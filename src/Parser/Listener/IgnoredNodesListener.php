<?php

namespace Weglot\Parser\Listener;

use Weglot\Parser\Event\ParserCrawlerBeforeEvent;
use Weglot\Parser\Exception\ParserContextException;

final class IgnoredNodesListener
{
    /**
     * Nodes to ignore in DOM
     * @var array
     */
    protected $ignoredNodes = [
        'strong', 'b',
        'em', 'i',
        'small', 'big',
        'sub', 'sup',
        'abbr',
        'acronym',
        'bdo',
        'cite',
        'kbd',
        'q',
    ];

    /**
     * @param ParserCrawlerBeforeEvent $event
     *
     * @throws ParserContextException
     */
    public function __invoke(ParserCrawlerBeforeEvent $event)
    {
        $source = $event->getContext()->getSource();

        // time for the BIG regex ...
        $pattern = '#<(?<tag>' .implode('|', $this->ignoredNodes). ')(?<more>\s.*?)?\>(?<content>[^>]*?)\<\/(?<tagclosed>' .implode('|', $this->ignoredNodes). ')>#i';
        $matches = [];

        // Using while instead of preg_match_all is the key to handle nested ignored nodes.
        while (preg_match($pattern, $source, $matches)) {
            if ($matches[0] !== '' && $matches['tag'] === $matches['tagclosed']) {
                $source = str_replace(
                    $matches[0],
                    '&lt;' .$matches['tag'].str_replace('>', '&gt;', str_replace('<', '&lt;', $matches['more'])). '&gt;' . $matches['content']. '&lt;/' . $matches['tag'] . '&gt;',
                    $source
                );
            }
        }

        $event->getContext()->setSource($source);
    }
}
