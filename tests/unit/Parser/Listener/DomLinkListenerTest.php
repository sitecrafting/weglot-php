<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomLinkListenerTest extends AbstractParserCrawlerAfterEventTest
{
    /**
     * @var array
     */
    protected $attributes = [
        'title',
        'href',
        'data-value',
        'data-tooltip',
        'data-title',
        'data-text',
        'data-hover',
        'data-content'
    ];

    /**
     * @var array
     */
    protected $words;

    /**
     * @var array
     */
    protected $attributesWords;

    protected function _before()
    {
        parent::_before();

        $this->words = [
            'en' => 'The car is blue',
            'fr' => 'La voiture est bleue'
        ];

        $this->attributesWords = [
            'en' => [
                'title' => 'Smashed',
                'href' => 'https://www.google.com/',    // won't be translated ! we only translate: .pdf / .rar / .docx
                'data-value' => 'Hello',
                'data-tooltip' => 'Goodbye',
                'data-title' => 'Future',
                'data-text' => 'Tomorrow',
                'data-hover' => 'Yesterday',
                'data-content' => 'Star',
            ],
            'fr' => [
                'title' => 'Brisé',
                'href' => 'https://www.google.com/',
                'data-value' => 'Salut',
                'data-tooltip' => 'Au revoir',
                'data-title' => 'Avenir',
                'data-text' => 'Demain',
                'data-hover' => 'Hier',
                'data-content' => 'Étoiles',
            ]
        ];

        $attributes = ['en' => [], 'fr' => []];
        foreach ($this->attributesWords as $lang => $list) {
            foreach ($list as $attribute => $word) {
                $attributes[$lang][] = $attribute . '="' . $word . '"';
            }
        }

        $this->sample['en'] = '<div><a ' .implode(' ', $attributes['en']). '>' .$this->words['en']. '</a></div>';
        $this->sample['fr'] = '<div><a ' .implode(' ', $attributes['fr']). '>' .$this->words['fr']. '</a></div>';
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $count = 0;

        $translateEntry = $event->getContext()->getTranslateEntry();

        foreach ($translateEntry->getInputWords() as $inputWord)
        {
            $pass = false;

            foreach ($this->attributesWords['en'] as $attribute => $word) {
                if (($attribute === 'href' && $inputWord->getWord() === $word && $inputWord->getType() === WordType::PDF_HREF) ||
                    $inputWord->getWord() === $word && $inputWord->getType() === WordType::TEXT) {
                    ++$count;
                    $pass = true;
                }
            }

            if ($inputWord->getWord() === $this->words['en'] && $inputWord->getType() === WordType::TEXT) {
                ++$count;
                $pass = true;
            }

            if(!$pass) {
                $this->assertTrue($pass);
            }
        }
        $this->assertTrue($count === 8);
    }

    public function checks($translated)
    {
        foreach ($this->attributesWords['fr'] as $word) {
            $this->assertContains($word, $translated);
        }
        $this->assertContains($this->words['fr'], $translated);
    }
}