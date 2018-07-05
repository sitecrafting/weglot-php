<?php

use Weglot\Parser\Event\ParserCrawlerBeforeEvent;

class ParserCrawlerBeforeEventTest extends AbstractEventTest
{
    public function testBehavior()
    {
        list($parser, $sample) = $this->_beforeDispatchEvent();

        $parser->addListener('parser.crawler.before', function (ParserCrawlerBeforeEvent $event) {
            $context = $event->getContext();
            $this->_defaultChecks($context);

            // event-specific tests
            $this->assertNull($context->getCrawler());
            $this->assertNull($context->getTranslateEntry());
        });

        // trigger event
        $parser->translate($sample, 'en', 'fr');
    }
}
