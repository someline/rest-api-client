<?php
namespace Libern\Rest\Test;

class RestClientTest extends TestCase
{

    public function testRestClient()
    {
        $restClient = new \Libern\Rest\RestClient();
        $restClient->setOAuthUserCredentials([
            'username' => 'libern@someline.com',
            'password' => 'Abc12345',
        ]);
        $restClient->withOAuthTokenTypeUser();
        $response = $restClient->get("users");
        if ($response->getStatusCode() == 200) {
            $responseData = $restClient->getResponseData();
        } else {
            $restClient->printResponseOriginContent();
        }
        $this->assertEquals($response->getStatusCode(), 200);
    }

}