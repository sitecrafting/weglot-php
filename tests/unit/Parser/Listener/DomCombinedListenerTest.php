<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomCombinedListenerTest extends AbstractParserCrawlerAfterEventTest
{
    protected function _before()
    {
        parent::_before();

        $this->sample = [
            'en' => file_get_contents(__DIR__ . '/../Resources/en-combined-sample.html'),
            'fr' => file_get_contents(__DIR__ . '/../Resources/fr-combined-sample.html'),
        ];
    }


    public function listenerCallback(AbstractEvent $event)
    {
        //
    }

    public function checks($translated)
    {
        $this->assertEquals($this->sample['fr'], $translated);
    }
}
