<?php

use Weglot\Parser\Event\AbstractEvent;

class DomTextListenerTest extends AbstractParserCrawlerAfterEventTest
{
    /**
     * @var array
     */
    protected $words;

    protected function _before()
    {
        parent::_before();

        $this->words = [
            'en' => 'The car is blue',
            'fr' => 'La voiture est bleue'
        ];

        $this->sample['en'] = '<p>' .$this->words['en']. '</p>';
        $this->sample['fr'] = '<p>' .$this->words['fr']. '</p>';
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $contains = false;

        $translateEntry = $event->getContext()->getTranslateEntry();
        foreach ($translateEntry->getInputWords() as $inputWord)
        {
            if($inputWord->getWord() === $this->words['en'])
            {
                $contains = true;
            }
        }

        $this->assertTrue($contains);
    }

    public function checks($translated)
    {
        $this->assertContains($this->words['fr'], $translated);
    }
}