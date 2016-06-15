<?php

namespace Libern\Rest\Test;

class RestTest extends TestCase
{

    public function testEchoPhrase()
    {
        $restClient = new \Libern\Rest\RestClient();
        $result = $restClient->echoPhrase('Hello, libern!');
        $this->assertEquals($result, 'Hello, libern!');
    }

}
