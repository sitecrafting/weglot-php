<?php

use Weglot\Parser\Event\ParserRenderEvent;
use Weglot\Client\Api\TranslateEntry;

class ParserRenderEventTest extends AbstractEventTest
{
    public function testBehavior()
    {
        list($parser, $sample) = $this->_beforeDispatchEvent();

        $parser->addListener('parser.render', function (ParserRenderEvent $event) {
            $context = $event->getContext();
            $this->_defaultChecks($context);

            // event-specific tests
            $this->assertNull($context->getCrawler());
            $this->assertNotNull($context->getTranslateEntry());
            $this->assertTrue($context->getTranslateEntry() instanceof TranslateEntry);
            $this->assertEquals($context->getTranslateEntry()->getInputWords()->count(), $context->getTranslateEntry()->getOutputWords()->count());
        });

        // trigger event
        $parser->translate($sample, 'en', 'fr');
    }
}
