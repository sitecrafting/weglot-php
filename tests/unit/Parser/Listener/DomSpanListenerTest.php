<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomSpanListenerTest extends AbstractParserCrawlerAfterEventTest
{
    /**
     * @var array
     */
    protected $words;

    /**
     * @var array
     */
    protected $title;

    protected function _before()
    {
        parent::_before();

        $this->words = [
            'en' => 'The car is blue',
            'fr' => 'La voiture est bleue'
        ];

        $this->title = [
            'en' => 'But not black !',
            'fr' => 'Mais pas noir!'
        ];

        $this->sample['en'] = '<span title="' .$this->title['en']. '">' .$this->words['en']. '</span>';
        $this->sample['fr'] = '<span title="' .$this->title['fr']. '">' .$this->words['fr']. '</span>';
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $count = 0;

        $translateEntry = $event->getContext()->getTranslateEntry();
        foreach ($translateEntry->getInputWords() as $inputWord)
        {
            if (($inputWord->getWord() === $this->words['en'] && $inputWord->getType() === WordType::TEXT) ||
                ($inputWord->getWord() === $this->title['en'] && $inputWord->getType() === WordType::VALUE)) {
                ++$count;
            }
        }

        $this->assertTrue($count === 2);
    }

    public function checks($translated)
    {
        $this->assertContains($this->words['fr'], $translated);
        $this->assertContains($this->title['fr'], $translated);
    }
}