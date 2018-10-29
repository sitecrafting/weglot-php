<?php

namespace Weglot\Parser;

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
use Weglot\Parser\Listener\ExcludeBlocksListener;
use Weglot\Parser\Listener\TagsInScriptListener;

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
    public function __construct(Client $client, ConfigProviderInterface $configProvider, array $excludeBlocks = [], array $listeners = [])
    {
        // config-related stuff
        $this
            ->setClient($client)
            ->setConfigProvider($configProvider)
            ->setExcludeBlocks($excludeBlocks);

        // init EventDispatcher
        $this->eventDispatcher = new EventDispatcher();
        $this->defaultListeners();
        foreach ($listeners as $eventName => $callback) {
            $this->addListener($eventName, $callback);
        }

        // dispatch - parser.init
        $event = new ParserInitEvent($this);
        $this->eventDispatcher->dispatch(ParserInitEvent::NAME, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Add default listeners
     */
    protected function defaultListeners()
    {
        $this->addListener('parser.crawler.before', new TagsInScriptListener());

        $this->addListener('parser.crawler.after', new ExcludeBlocksListener(), 1);
        $this->addListener('parser.crawler.after', new DomTextListener());
        $this->addListener('parser.crawler.after', new DomButtonListener());
        $this->addListener('parser.crawler.after', new DomIframeSrcListener());
        $this->addListener('parser.crawler.after', new DomImgListener());
        $this->addListener('parser.crawler.after', new DomInputDataListener());
        $this->addListener('parser.crawler.after', new DomInputRadioListener());
        $this->addListener('parser.crawler.after', new DomLinkListener());
        $this->addListener('parser.crawler.after', new DomMetaContentListener());
        $this->addListener('parser.crawler.after', new DomPlaceholderListener());
        $this->addListener('parser.crawler.after', new DomSpanListener());
        $this->addListener('parser.crawler.after', new DomTableDataListener());

        $this->addListener('parser.translated', new DomReplaceListener());
        
        $this->addListener('parser.render', new CleanHtmlEntitiesListener());
        $this->addListener('parser.render', new TagsInScriptListener(false));
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
     * @param $source
     * @param $languageFrom
     * @param $languageTo
     * @return ParserContext
     * @throws Exception\ParserContextException
     * @throws \Weglot\Client\Api\Exception\MissingRequiredParamException
     */
    public function parse($source, $languageFrom, $languageTo)
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

        return $context;
    }

    /**
     * {@inheritdoc}
     * @param $source
     * @param $languageFrom
     * @param $languageTo
     * @return string
     * @throws Exception\ParserContextException
     * @throws \Weglot\Client\Api\Exception\ApiError
     * @throws \Weglot\Client\Api\Exception\InputAndOutputCountMatchException
     * @throws \Weglot\Client\Api\Exception\InvalidWordTypeException
     * @throws \Weglot\Client\Api\Exception\MissingRequiredParamException
     * @throws \Weglot\Client\Api\Exception\MissingWordsOutputException
     */
    public function translate($source, $languageFrom, $languageTo)
    {
        $context = $this->parse($source, $languageFrom, $languageTo);
        $hasBodyTag = (strpos($context->getSource(), '<body') !== false);

        // translating through Weglot API
        $translate = new Translate($context->getTranslateEntry(), $this->getClient());
        $context->setTranslateEntry($translate->handle());

        // dispatch - parser.translated
        $event = new ParserTranslatedEvent($context);
        $this->eventDispatcher->dispatch(ParserTranslatedEvent::NAME, $event);

        // rendering crawled source
        $source = $context->getCrawler()->html();
        if (!$hasBodyTag) {
            $source = str_replace('<body>', '', $source);
            $source = str_replace('</body>', '', $source);
        }

        $context
            ->setCrawler(null)
            ->setSource($source);

        // dispatch - parser.render
        $event = new ParserRenderEvent($context);
        $this->eventDispatcher->dispatch(ParserRenderEvent::NAME, $event);

        return $context->getSource();
    }
}
