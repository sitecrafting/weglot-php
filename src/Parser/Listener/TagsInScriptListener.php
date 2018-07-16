<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Event\AbstractEvent;
use Weglot\Parser\Exception\ParserContextException;

final class TagsInScriptListener
{
    /**
     * @var bool
     */
    protected $cleaning;

    public function __construct($cleaning = true)
    {
        $this->cleaning = $cleaning;
    }

    /**
     * @param AbstractEvent $event
     *
     * @throws ParserContextException
     */
    public function __invoke(AbstractEvent $event)
    {
        $source = $event->getContext()->getSource();
        $matches = [];

        if (preg_match_all('#\<script(\s.*?)?\>(.*?)\<\/script\>#is', $source, $matches)) {
            if (isset($matches[0])) {
                $count = count($matches[0]);

                for ($i = 0; $i < $count; ++$i) {
                    if ($this->cleaning) {
                        $replacement = '<script' .$matches[1][$i]. '>' .str_replace('<', '\x3C', $matches[2][$i]). '</script>';
                    } else {
                        $replacement = '<script' .$matches[1][$i]. '>' .str_replace('\x3C', '<', $matches[2][$i]). '</script>';
                    }

                    $source = str_replace($matches[0][$i], $replacement, $source);
                }
            }
        }

        $event->getContext()->setSource($source);
    }
}
