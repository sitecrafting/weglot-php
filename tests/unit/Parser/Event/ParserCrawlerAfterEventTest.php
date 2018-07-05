<?php

use Weglot\Parser\Event\ParserCrawlerAfterEvent;
use Symfony\Component\DomCrawler\Crawler;
use Weglot\Client\Api\TranslateEntry;

class ParserCrawlerAfterEventTest extends AbstractEventTest
{
    public function testBehavior()
    {
        list($parser, $sample) = $this->_beforeDispatchEvent();

        $parser->addListener('parser.crawler.after', function (ParserCrawlerAfterEvent $event) {
            $context = $event->getContext();
            $this->_defaultChecks($context);

            // event-specific tests
            $this->assertNotNull($context->getCrawler());
            $this->assertTrue($context->getCrawler() instanceof Crawler);
            $this->assertNotNull($context->getTranslateEntry());
            $this->assertTrue($context->getTranslateEntry() instanceof TranslateEntry);
        });

        // trigger event
        $parser->translate($sample, 'en', 'fr');
    }
}
