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

        if (preg_match('#\<script(\s.*?)?\>(.*?)\<\/script\>#is', $source, $matches)) {
            if ($this->cleaning) {
                $replacement = '<script' .$matches[1]. '>' .str_replace('<', '\x3C', $matches[2]). '</script>';
            } else {
                $replacement = '<script' .$matches[1]. '>' .str_replace('\x3C', '<', $matches[2]). '</script>';
            }

            $source = preg_replace('#\<script(\s.*?)?\>(.*?)\<\/script\>#is', $replacement, $source);
        }

        $event->getContext()->setSource($source);
    }
}
