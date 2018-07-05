<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomInputRadioListenerTest extends AbstractParserCrawlerAfterEventTest
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

        $this->sample['en'] = '
<div>
    <input type="radio" data-order_button_text="' .$this->words['en']. '">
</div>';
        $this->sample['fr'] = '
<div>
    <input type="radio" data-order_button_text="' .$this->words['fr']. '">
</div>';
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $count = 0;

        $translateEntry = $event->getContext()->getTranslateEntry();
        foreach ($translateEntry->getInputWords() as $inputWord)
        {
            if ($inputWord->getWord() === $this->words['en'] &&
                $inputWord->getType() === WordType::VALUE)
            {
                ++$count;
            }
        }

        $this->assertTrue($count === 1);
    }

    public function checks($translated)
    {
        $this->assertContains($this->words['fr'], $translated);
        $this->assertContains('<input type="radio" data-order_button_text="' .$this->words['fr']. '">', $translated);
    }
}