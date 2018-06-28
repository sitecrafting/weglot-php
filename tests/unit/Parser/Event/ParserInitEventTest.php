<?php

use Weglot\Parser\Event\ParserInitEvent;
use Weglot\Parser\Parser;

class ParserInitEventTest extends AbstractEventTest
{
    public function testBehavior()
    {
        new Parser($this->client, $this->config, [], [
            'parser.init' => function (ParserInitEvent $event) {
                $this->assertTrue($event->getParser() instanceof Parser);
            }
        ]);
    }
}
