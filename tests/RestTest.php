<?php

namespace Libern\Rest\Test;

use Libern\Rest\RestClient;

class RestTest extends TestCase
{

    public function testGetUsers()
    {
        $restClient = new RestClient();
        $restClient->withOAuthTokenTypeUser();
        $restClient->get('users');
        $restClient->printResponseData();
        $this->assertEquals('200', '200');
    }

}
