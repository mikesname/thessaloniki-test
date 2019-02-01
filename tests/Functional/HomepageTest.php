<?php

namespace Tests\Functional;

class HomepageTest extends BaseTestCase
{
    public function testGetHomepage()
    {
        $response = $this->runApp('GET', '/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Text Search', (string)$response->getBody());
        $this->assertNotContains('Hello', (string)$response->getBody());
    }
}