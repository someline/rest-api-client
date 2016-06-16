<?php
namespace Libern\Rest\Test;

class RestClientTest extends TestCase
{

    public function testRestClient()
    {
        $restClient = new \Libern\Rest\RestClient('someline-starter');
        
        $restClient->setOAuthUserCredentials([
            'username' => 'libern@someline.com',
            'password' => 'Abc12345',
        ]);
        $restClient->withOAuthTokenTypeUser();

        $response = $restClient->get("users")->getResponse();
        if (!$restClient->isResponseStatusCode(200)) {
            $restClient->printResponseOriginContent();
            $responseMessage = $restClient->getResponseMessage();
            print_r($responseMessage);
        } else {
            $responseData = $restClient->getResponseData();
            print_r($responseData);
        }

        $this->assertEquals($response->getStatusCode(), 200);
    }

}