<?php

namespace Weglot\Parser;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Weglot\Client\Client;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Event\ParserCrawlerBeforeEvent;
use Weglot\Parser\Event\ParserInitEvent;
use Weglot\Parser\Event\ParserRenderEvent;

/**
 * Class Parser
 * @package Weglot\Parser
 */
class Parser implements ParserInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ConfigProviderInterface
     */
    protected $configProvider;

    /**
     * @var array
     */
    protected $excludeBlocks = [];

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * {@inheritdoc}
     */
    public function __construct(Client $client, ConfigProviderInterface $configProvider, array $excludeBlocks = [])
    {
        // config-related stuff
        $this
            ->setClient($client)
            ->setConfigProvider($configProvider)
            ->setExcludeBlocks($excludeBlocks);

        // init
        $this->eventDispatcher = new EventDispatcher();

        // dispatch - parser.init
        $event = new ParserInitEvent($this);
        $this->eventDispatcher->dispatch(ParserInitEvent::NAME, $event);
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param ConfigProviderInterface $configProvider
     * @return $this
     */
    public function setConfigProvider(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        return $this;
    }

    /**
     * @return ConfigProviderInterface
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }

    /**
     * @param array $excludeBlocks
     * @return $this
     */
    public function setExcludeBlocks(array $excludeBlocks = [])
    {
        $this->excludeBlocks = $excludeBlocks;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludeBlocks()
    {
        return $this->excludeBlocks;
    }

    /**
     * {@inheritdoc}
     */
    public function translate($source, $languageFrom, $languageTo)
    {
        // dispatch - parser.crawler.before
        $event = new ParserCrawlerBeforeEvent($this, $source);
        $this->eventDispatcher->dispatch(ParserCrawlerBeforeEvent::NAME, $event);

        $crawler = new Crawler($source);

        // dispatch - parser.crawler.after
        $event = new ParserCrawlerAfterEvent($this, $crawler);
        $this->eventDispatcher->dispatch(ParserCrawlerAfterEvent::NAME, $event);

        $source = $crawler->html();

        // dispatch - parser.render
        $event = new ParserRenderEvent($this, $source);
        $this->eventDispatcher->dispatch(ParserRenderEvent::NAME, $event);

        return $source;
    }
}
