<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomPlaceholderListenerTest extends AbstractParserCrawlerAfterEventTest
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
                'Goodbye'
            ],
            'fr' => [
                'La voiture est bleue',
                'Brisé',
                'Salut',
                'Au revoir'
            ]
        ];

        $this->sample['en'] = <<<HTML
<html>
    <body>
           <input type="text" placeholder="{$this->words['en'][0]}" />
           <input type="password" placeholder="{$this->words['en'][1]}" />
           <input type="search" placeholder="{$this->words['en'][2]}" />
           <input type="email" placeholder="{$this->words['en'][3]}" />
    </body>
</html>
HTML;
        $this->sample['fr'] = <<<HTML
<html>
    <body>
           <input type="text" placeholder="{$this->words['fr'][0]}" />
           <input type="password" placeholder="{$this->words['fr'][1]}" />
           <input type="search" placeholder="{$this->words['fr'][2]}" />
           <input type="email" placeholder="{$this->words['fr'][3]}" />
    </body>
</html>
HTML;
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $count = 0;

        $translateEntry = $event->getContext()->getTranslateEntry();
        foreach ($translateEntry->getInputWords() as $inputWord) {
            if ($inputWord->getType() === WordType::VALUE && in_array($inputWord->getWord(), $this->words['en'])) {
                ++$count;
            }
        }

        $this->assertTrue($count === 4);
    }

    public function checks($translated)
    {
        foreach ($this->words['fr'] as $word) {
            $this->assertContains($word, $translated);
        }
    }
}
