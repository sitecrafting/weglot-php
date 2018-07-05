<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomInputDataListenerTest extends AbstractParserCrawlerAfterEventTest
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
    <input type="submit" data-value="' .$this->words['en']. '" data-order_button_text="' .$this->words['en']. '">
    <input type="button" data-value="' .$this->words['en']. '" data-order_button_text="' .$this->words['en']. '">
</div>';
        $this->sample['fr'] = '
<div>
    <input type="submit" data-value="' .$this->words['fr']. '" data-order_button_text="' .$this->words['fr']. '">
    <input type="button" data-value="' .$this->words['fr']. '" data-order_button_text="' .$this->words['fr']. '">
</div>';
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $count = 0;

        $translateEntry = $event->getContext()->getTranslateEntry();
        foreach ($translateEntry->getInputWords() as $inputWord) {
            if ($inputWord->getWord() === $this->words['en'] &&
                $inputWord->getType() === WordType::TEXT) {
                ++$count;
            }
        }

        $this->assertTrue($count === 4);
    }

    public function checks($translated)
    {
        $this->assertContains($this->words['fr'], $translated);
        $this->assertContains('<input type="submit" data-value="' .$this->words['fr']. '" data-order_button_text="' .$this->words['fr']. '">', $translated);
    }
}
