<?php

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Client\Api\Enum\WordType;

class DomIframeSrcListenerTest extends AbstractParserCrawlerAfterEventTest
{
    /**
     * @var string
     */
    protected $url;

    protected function _before()
    {
        parent::_before();

        $this->url = 'https://www.google.com/';

        $this->sample['en'] = '<iframe src="' .$this->url. '"/>';
        $this->sample['fr'] = '<iframe src="' .$this->url. '"/>';
    }

    public function listenerCallback(AbstractEvent $event)
    {
        $count = 0;

        $translateEntry = $event->getContext()->getTranslateEntry();
        foreach ($translateEntry->getInputWords() as $inputWord) {
            if ($inputWord->getWord() === $this->url &&
                $inputWord->getType() === WordType::IFRAME_SRC) {
                ++$count;
            }
        }

        $this->assertTrue($count === 1);
    }

    public function checks($translated)
    {
        $this->assertContains($this->sample['fr'], $translated);
    }
}
