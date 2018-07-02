<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomCombinedListenerTest extends AbstractParserCrawlerAfterEventTest
{
    /**
     * @var array
     */
    protected $words;

    protected function _before()
    {
        parent::_before();

        $this->words = [
            'en' => file_get_contents(__DIR__ . '/../Resources/en-combined-sample.html'),
            'fr' => file_get_contents(__DIR__ . '/../Resources/fr-combined-sample.html'),
        ];

        $this->sample['en'] = '<p>' .$this->words['en']. '</p>';
        $this->sample['fr'] = '<p>' .$this->words['fr']. '</p>';
    }


    public function listenerCallback(AbstractEvent $event)
    {
        
    }

    public function checks($translated)
    {
        $this->assertEquals($this->sample['fr'], $translated);
    }
}