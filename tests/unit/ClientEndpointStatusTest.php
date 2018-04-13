<?php

use Weglot\Client\Client;
use Weglot\Client\Endpoint\Status;

class ClientEndpointStatusTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Weglot\Client\Client
     */
    protected $client;

    /**
     * Init client
     */
    protected function _before()
    {
        echo '<pre>';
        echo print_r($_ENV);
        exit;
        $this->client = new Client($_ENV['WG_API_KEY']);
    }

    // tests
    public function testSomeFeature()
    {
        $status = new Status($this->client);
        $this->assertTrue($status->handle(), 'API not reachable');
    }
}
