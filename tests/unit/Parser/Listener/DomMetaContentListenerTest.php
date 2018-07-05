<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomMetaContentListenerTest extends AbstractParserCrawlerAfterEventTest
{
    /**
     * @var array
     */
    protected $words;

    protected function _before()
    {
        parent::_before();

        $this->words = [
            'en' => [
                'The car is blue',
                'Smashed',
                'Hello',
                'Goodbye',
                'Future',
                'Tomorrow'
            ],
            'fr' => [
                'La voiture est bleue',
                'Brisé',
                'Salut',
                'Au revoir',
                'Avenir',
                'Demain'
            ]
        ];

        $this->sample['en'] = <<<HTML
<html>
    <head>
        <meta name="description" content="{$this->words['en'][0]}" />
        
        <meta name="twitter:title" content="{$this->words['en'][1]}" />
        <meta name="twitter:description" content="{$this->words['en'][2]}" />
        
        <meta property="og:title" content="{$this->words['en'][3]}" />
        <meta property="og:description" content="{$this->words['en'][4]}" />
        <meta property="og:site_name" content="{$this->words['en'][5]}" />
    </head>
    <body></body>
</html>
HTML;
        $this->sample['fr'] = <<<HTML
<html>
    <head>
        <meta name="description" content="{$this->words['fr'][0]}" />
        
        <meta name="twitter:title" content="{$this->words['fr'][1]}" />
        <meta name="twitter:description" content="{$this->words['fr'][2]}" />
        
        <meta property="og:title" content="{$this->words['fr'][3]}" />
        <meta property="og:description" content="{$this->words['fr'][4]}" />
        <meta property="og:site_name" content="{$this->words['fr'][5]}" />
    </head>
    <body></body>
</html>
HTML;
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $count = 0;

        $translateEntry = $event->getContext()->getTranslateEntry();
        foreach ($translateEntry->getInputWords() as $inputWord)
        {
            if ($inputWord->getType() === WordType::META_CONTENT && in_array($inputWord->getWord(), $this->words['en'])) {
                ++$count;
            }
        }

        $this->assertTrue($count === 6);
    }

    public function checks($translated)
    {
        foreach ($this->words['fr'] as $word) {
            $this->assertContains($word, $translated);
        }
    }
}