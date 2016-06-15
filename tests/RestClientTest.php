<?php
namespace Libern\Rest\Test;

class RestClientTest extends TestCase
{

    public function testRestClient()
    {
        $restClient = new \Libern\Rest\RestClient();
        $restClient->withOAuthTokenTypeUser();
        $response = $restClient->get("users");
        $restClient->printResponseData();
        $restClient->getResponseData();
        $this->assertEquals($response->getStatusCode(), 200);
    }

}