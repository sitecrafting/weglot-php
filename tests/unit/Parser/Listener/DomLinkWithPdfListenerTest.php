<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomLinkWithPdfListenerTest extends AbstractParserCrawlerAfterEventTest
{

    /**
     * @var array
     */
    protected $words;

    /**
     * @var string
     */
    protected $link;

    protected function _before()
    {
        parent::_before();

        $this->words = [
            'en' => 'The car is blue',
            'fr' => 'La voiture est bleue'
        ];

        $this->link = 'https://medias.audiofanzine.com/files/mpc-renaissance-manuel-fr-471216.pdf';

        $this->sample['en'] = '<div><a href="' .$this->link. '">' .$this->words['en']. '</a></div>';
        $this->sample['fr'] = '<div><a href="' .$this->link. '">' .$this->words['fr']. '</a></div>';
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $count = 0;

        $translateEntry = $event->getContext()->getTranslateEntry();

        foreach ($translateEntry->getInputWords() as $inputWord) {
            if (($inputWord->getWord() === $this->link && $inputWord->getType() === WordType::PDF_HREF) ||
                ($inputWord->getWord() === $this->words['en'] && $inputWord->getType() === WordType::TEXT)) {
                ++$count;
            }
        }

        $this->assertTrue($count === 2);
    }

    public function checks($translated)
    {
        $this->assertContains($this->link, $translated);
        $this->assertContains($this->words['fr'], $translated);
    }
}
