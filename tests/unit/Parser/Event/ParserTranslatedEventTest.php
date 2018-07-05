<?php

use Weglot\Parser\Event\ParserTranslatedEvent;
use Symfony\Component\DomCrawler\Crawler;
use Weglot\Client\Api\TranslateEntry;

class ParserTranslatedEventTest extends AbstractEventTest
{
    public function testBehavior()
    {
        list($parser, $sample) = $this->_beforeDispatchEvent();

        $parser->addListener('parser.translated', function (ParserTranslatedEvent $event) {
            $context = $event->getContext();
            $this->_defaultChecks($context);

            // event-specific tests
            $this->assertNotNull($context->getCrawler());
            $this->assertTrue($context->getCrawler() instanceof Crawler);
            $this->assertNotNull($context->getTranslateEntry());
            $this->assertTrue($context->getTranslateEntry() instanceof TranslateEntry);
            $this->assertEquals($context->getTranslateEntry()->getInputWords()->count(), $context->getTranslateEntry()->getOutputWords()->count());
        });

        // trigger event
        $parser->translate($sample, 'en', 'fr');
    }
}
