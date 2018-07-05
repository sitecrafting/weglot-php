<?php

use Weglot\Parser\ConfigProvider\ConfigProviderInterface;
use Weglot\Client\Api\Enum\BotType;

abstract class AbstractConfigProviderTest extends \Codeception\Test\Unit
{
    /**
     * @var ConfigProviderInterface
     */
    protected $config;

    public function testSimple()
    {
        $url = 'https://www.google.com/';
        $bot = BotType::HUMAN;
        $title = 'Google';
        $defaultArray = [
            'request_url' => $url,
            'bot' => $bot
        ];

        $this->assertEquals($url, $this->config->getUrl());
        $this->assertEquals($bot, $this->config->getBot());
        $this->assertNull($this->config->getTitle());
        $this->assertTrue($this->config->getAutoDiscoverTitle());

        $this->config->setTitle($title);
        $this->assertEquals($title, $this->config->getTitle());
        $this->assertFalse($this->config->getAutoDiscoverTitle());
        $this->assertEquals(array_merge($defaultArray, ['title' => $title]), $this->config->asArray());

        $this->config->setTitle(null);
        $this->assertNull($this->config->getTitle());
        $this->assertTrue($this->config->getAutoDiscoverTitle());
        $this->assertEquals($defaultArray, $this->config->asArray());
    }
}
