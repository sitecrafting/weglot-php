<?php

use Weglot\Parser\Event\AbstractEvent;

class IgnoredNodesListenerTest extends AbstractParserCrawlerBeforeEventTest
{
    /**
     * @var int
     */
    const EVENT_PRIORITY = -1;

    public function listenerCallback(AbstractEvent $event)
    {
        $source = $event->getContext()->getSource();

        $this->assertContains('&lt;em&gt;', $source);
        $this->assertContains('&lt;/em&gt;', $source);
    }

    public function checks($translated)
    {
        $this->assertContains('<em>', $translated);
        $this->assertContains('</em>', $translated);
        $this->assertNotContains('&lt;em&gt;', $translated);
        $this->assertNotContains('&lt;/em&gt;', $translated);
    }
}
