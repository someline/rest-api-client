<?php

namespace Libern\Rest;

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class RestClient
{

    /**
     * @var array
     */
    protected $service_config;

    /**
     * @var array
     */
    protected $shared_service_config;

    /**
     * @var Psr\Http\Message\ResponseInterface
     */
    protected $guzzle_response;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $oauth_tokens = [];

    const TOKEN_TYPE_USER = 'user';
    const TOKEN_TYPE_CLIENT = 'client';

    protected $use_oauth_token = null;

    /**
     * @var bool
     */
    protected $debug_mode = false;

    /**
     * @var string
     */
    protected $environment;

    /**
     * Create a new RestClient Instance
     * @param $service_name
     */
    public function __construct($service_name = null)
    {
        $this->environment = $this->getConfig('environment', 'production');
        $this->shared_service_config = $this->getConfig('shared_service_config');
        $this->debug_mode = $this->getConfig('debug_mode');
        $services = $this->getConfig('services');

        // use default service name
        if (empty($service_name)) {
            $service_name = $this->getConfig('default_service_name');
        }

        // choose service environment
        if (!isset($services[$this->environment])) {
            throw new RuntimeException("Rest Client Error: Service for environment [{$this->environment}] is not found in config.");
        }
        $services = $services[$this->environment];

        // check service configs
        if (!isset($services[$service_name])) {
            throw new RuntimeException("Rest Client Error: Service [$service_name] is not found in environment [{$this->environment}] config.");
        }

        $this->setServiceConfig($services[$service_name]);

        $this->setUp();
    }

    public function getConfig($key, $default = null)
    {
        return config("rest-client.$key");
    }

    private function setServiceConfig($service_config)
    {
        $shared_service_config = $this->shared_service_config;

        $combined_service_config = $service_config;
        foreach ($shared_service_config as $key => $config) {
            if (is_array($config) && isset($combined_service_config[$key])) {
                $combined_service_config[$key] = array_merge($config, $combined_service_config[$key]);
            } else if (!isset($combined_service_config[$key])) {
                $combined_service_config[$key] = $config;
            }
        }

        $this->service_config = $combined_service_config;
    }

    public function getServiceConfig($key)
    {
        return $this->service_config[$key];
    }

    public function setUp()
    {
        $this->client = new Client([
            'base_uri' => $this->getServiceConfig('base_uri'),
            'exceptions' => false,
        ]);
    }

    /**
     * @param boolean $debug_mode
     */
    public function setDebugMode($debug_mode)
    {
        $this->debug_mode = $debug_mode;
    }

    protected function getClientData()
    {
        return $this->getServiceConfig('oauth2_credentials');
    }

    protected function getOAuthUserCredentialsData()
    {
        return [
            'username' => 'libern@someline.com',
            'password' => 'Abc12345',
        ];
    }

    protected function postRequestAccessToken($grant_type, $data)
    {
        $url = $this->getServiceConfig('oauth2_access_token_url');
        return $this->post($url, array_merge($data, [
            'grant_type' => $grant_type,
        ]));
    }

    private function configureOptions($options)
    {
        $headers = $this->getServiceConfig('headers');

        if ($this->use_oauth_token) {
            $headers['Authorization'] = 'Bearer ' . $this->getOAuthToken($this->use_oauth_token);
        }

        if (isset($options['headers'])) {
            $headers = array_merge($headers, $options['headers']);
            unset($options['headers']);
        }

        return array_merge([
            'headers' => $headers,
        ], $options);
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getResponseAsJson($assoc = true)
    {
        return json_decode($this->getResponse()->getContent(), $assoc);
    }

    public function getResponseData()
    {
        return $this->getResponseAsJson();
    }

    private function getOAuthToken($type)
    {
        $grant_types = $this->getServiceConfig('oauth2_grant_types');
        if (!isset($this->oauth_tokens[$type])) {
            if ($type == self::TOKEN_TYPE_CLIENT) {
                $this->postRequestAccessToken($grant_types['client_credentials'],
                    array_merge($this->getClientData(), []));
            } else if ($type == self::TOKEN_TYPE_USER) {
                $this->postRequestAccessToken($grant_types['password'],
                    array_merge($this->getClientData(), $this->getOAuthUserCredentialsData()));
            }
            $data = $this->getResponseData();
            $this->setOAuthToken($type, $data['access_token']);
        }
        return $this->oauth_tokens[$type];
    }

    public function setOAuthToken($type, $access_token)
    {
        if ($this->debug_mode) {
            echo "SET OAuthToken[$type]: $access_token\n\n";
        }
        $this->oauth_tokens[$type] = $access_token;
    }

    public function withOAuthToken($type)
    {
        $this->getOAuthToken($type);
        $this->use_oauth_token = $type;
        return $this;
    }

    public function withOAuthTokenTypeUser()
    {
        return $this->withOAuthToken(self::TOKEN_TYPE_USER);
    }

    public function withOAuthTokenTypeClient()
    {
        return $this->withOAuthToken(self::TOKEN_TYPE_CLIENT);
    }

    public function withoutOAuthToken()
    {
        $this->use_oauth_token = null;
        return $this;
    }

    /**
     * @param string $uri
     * @param array $query
     * @param array $options
     * @return $this
     */
    public function get($uri, array $query = [], array $options = [])
    {
        $options = $this->configureOptions($options);
        $this->printArray($options);
        $response = $this->client->get($uri, array_merge($options, [
            'query' => $query,
        ]));
        $this->setGuzzleResponse($response);
        return $this;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $options
     * @return $this
     */
    public function post($uri, array $data = [], array $options = [])
    {
        $options = $this->configureOptions($options);
        $response = $this->client->post($uri, array_merge($options, [
            'form_params' => $data,
        ]));
        $this->setGuzzleResponse($response);
        return $this;
    }

    /**
     * @url http://docs.guzzlephp.org/en/latest/quickstart.html#sending-form-files
     * @param $uri
     * @param array $multipart
     * @param array $options
     * @return $this
     */
    public function postMultipart($uri, array $multipart = [], array $options = [])
    {
        $options = $this->configureOptions($options);
        $response = $this->client->post($uri, array_merge($options, [
            'multipart' => $multipart,
        ]));
        $this->setGuzzleResponse($response);
        return $this;
    }

    /**
     * @param $uri
     * @param array $data
     * @param array $options
     * @return $this
     */
    public function postMultipartSimple($uri, array $data = [], array $options = [])
    {
        $options = $this->configureOptions($options);
        $multipart = [];
        foreach ($data as $key => $value) {
            $multipart[] = [
                'name' => $key,
                'contents' => $value,
            ];
        }
        $response = $this->client->post($uri, array_merge($options, [
            'multipart' => $multipart,
        ]));
        $this->setGuzzleResponse($response);
        return $this;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $options
     * @return $this
     */
    public function head($uri, array $data = [], array $options = [])
    {
        $response = $this->client->head($uri, array_merge($options, [
            'body' => $data,
        ]));
        $this->setGuzzleResponse($response);
        return $this;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $options
     * @return $this
     */
    public function put($uri, array $data = [], array $options = [])
    {
        $options = $this->configureOptions($options);
        $response = $this->client->put($uri, array_merge($options, [
            'form_params' => $data,
        ]));
        $this->setGuzzleResponse($response);
        return $this;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $options
     * @return $this
     */
    public function patch($uri, array $data = [], array $options = [])
    {
        $options = $this->configureOptions($options);
        $response = $this->client->patch($uri, array_merge($options, [
            'form_params' => $data,
        ]));
        $this->setGuzzleResponse($response);
        return $this;
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $options
     * @return $this
     */
    public function delete($uri, array $data = [], array $options = [])
    {
        $options = $this->configureOptions($options);
        $response = $this->client->delete($uri, array_merge($options, [
            'form_params' => $data,
        ]));
        $this->setGuzzleResponse($response);
        return $this;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setGuzzleResponse(ResponseInterface $response)
    {
        $this->guzzle_response = $response;
        $this->setResponse(new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders()));
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        $statusCode = $this->response->getStatusCode();
        if ($statusCode >= 300 && $this->debug_mode) {
            echo "\nResponse STATUS CODE is $statusCode:\n";
            $responseData = $this->getResponseData();
            if ($responseData) {
                $this->printArray($responseData);
            } else {
                echo $this->getResponse();
            }
        }
    }

    public function printResponseData()
    {
        print_r($this->getResponseData());
        return $this;
    }

    public function printResponseOriginContent()
    {
        print_r((string)$this->response->getOriginalContent());
        return $this;
    }

    protected function printArray($array)
    {
        if ($this->debug_mode) {
            print_r($array);
        }
    }

}
