<?php

namespace Weglot\Parser;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Weglot\Client\Client;
use Weglot\Client\Endpoint\Translate;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;
use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Weglot\Parser\Event\ParserCrawlerBeforeEvent;
use Weglot\Parser\Event\ParserTranslatedEvent;
use Weglot\Parser\Event\ParserInitEvent;
use Weglot\Parser\Event\ParserRenderEvent;
use Weglot\Parser\Listener\CleanHtmlEntitiesListener;
use Weglot\Parser\Listener\DomButtonListener;
use Weglot\Parser\Listener\DomIframeSrcListener;
use Weglot\Parser\Listener\DomImgListener;
use Weglot\Parser\Listener\DomInputDataListener;
use Weglot\Parser\Listener\DomInputRadioListener;
use Weglot\Parser\Listener\DomLinkListener;
use Weglot\Parser\Listener\DomMetaContentListener;
use Weglot\Parser\Listener\DomPlaceholderListener;
use Weglot\Parser\Listener\DomReplaceListener;
use Weglot\Parser\Listener\DomSpanListener;
use Weglot\Parser\Listener\DomTableDataListener;
use Weglot\Parser\Listener\DomTextListener;
use Weglot\Parser\Listener\IgnoredNodesListener;

/**
 * Class Parser
 * @package Weglot\Parser
 */
class Parser implements ParserInterface
{
    /**
     * Attribute to match in DOM when we don't want to translate innertext & childs.
     *
     * @var string
     */
    const ATTRIBUTE_NO_TRANSLATE = 'data-wg-notranslate';

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
        $this->defaultListeners();

        // dispatch - parser.init
        $event = new ParserInitEvent($this);
        $this->eventDispatcher->dispatch(ParserInitEvent::NAME, $event);
    }

    /**
     * Add default listeners
     */
    protected function defaultListeners()
    {
        $this->eventDispatcher->addListener('parser.crawler.before', new IgnoredNodesListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomTextListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomButtonListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomIframeSrcListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomImgListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomInputDataListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomInputRadioListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomLinkListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomMetaContentListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomPlaceholderListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomSpanListener());
        $this->eventDispatcher->addListener('parser.crawler.after', new DomTableDataListener());
        $this->eventDispatcher->addListener('parser.translated', new DomReplaceListener());
        $this->eventDispatcher->addListener('parser.render', new CleanHtmlEntitiesListener());
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
        $context = new ParserContext($this, $languageFrom, $languageTo, $source);

        // dispatch - parser.crawler.before
        $event = new ParserCrawlerBeforeEvent($context);
        $this->eventDispatcher->dispatch(ParserCrawlerBeforeEvent::NAME, $event);

        // crawling source
        $crawler = new Crawler($context->getSource());
        $context
            ->setCrawler($crawler)
            ->generateTranslateEntry();

        // dispatch - parser.crawler.after
        $event = new ParserCrawlerAfterEvent($context);
        $this->eventDispatcher->dispatch(ParserCrawlerAfterEvent::NAME, $event);

        // translating through Weglot API
        $translate = new Translate($context->getTranslateEntry(), $this->getClient());
        $context->setTranslateEntry($translate->handle());

        // dispatch - parser.translated
        $event = new ParserTranslatedEvent($context);
        $this->eventDispatcher->dispatch(ParserTranslatedEvent::NAME, $event);

        // rendering crawled source
        $source = $context->getCrawler()->html();
        $context
            ->setCrawler(null)
            ->setSource($source);

        // dispatch - parser.render
        $event = new ParserRenderEvent($context);
        $this->eventDispatcher->dispatch(ParserRenderEvent::NAME, $event);

        return $context->getSource();
    }
}
