<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomImgListenerTest extends AbstractParserCrawlerAfterEventTest
{
    /**
     * @var array
     */
    protected $words;

    /**
     * @var string
     */
    protected $image;

    protected function _before()
    {
        parent::_before();

        $this->words = [
            'en' => 'The car is blue',
            'fr' => 'La voiture est bleue'
        ];

        $this->image = 'https://placekitten.com/200/300';

        $this->sample['en'] = '<img src="' .$this->image. '" alt="' .$this->words['en']. '"/>';
        $this->sample['fr'] = '<img src="' .$this->image. '" alt="' .$this->words['fr']. '"/>';
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $count = 0;

        $translateEntry = $event->getContext()->getTranslateEntry();
        foreach ($translateEntry->getInputWords() as $inputWord) {
            if (($inputWord->getWord() === $this->words['en'] && $inputWord->getType() === WordType::IMG_ALT) ||
                ($inputWord->getWord() === $this->image && $inputWord->getType() === WordType::IMG_SRC)) {
                ++$count;
            }
        }

        $this->assertTrue($count === 2);
    }

    public function checks($translated)
    {
        $this->assertContains($this->image, $translated);
        $this->assertContains($this->words['fr'], $translated);
    }
}
