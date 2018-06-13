<?php

namespace Weglot\Parser\Formatter;

/**
 * Class IgnoredNodes
 * @package Weglot\Parser\Formatter
 */
class IgnoredNodes
{
    /**
     * @var string
     */
    protected $source;

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
        'cite', 'code',
        'kbd',
        'q',
    ];

    protected $usualTags = [
        'span',
        'blockquote',
        'aside',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'section', 'article', 'nav',
        'div',
        'dd', 'dl', 'dt',
        'li', 'ul', 'ol',
        'p', 'pre',
    ];

    /**
     * IgnoredNodes constructor.
     * @param string $source
     */
    public function __construct($source)
    {
        $this
            ->setSource($source)
            ->handle();
    }

    /**
     * @param string $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return array
     */
    public function getIgnoredNodes()
    {
        return $this->ignoredNodes;
    }

    /**
     * Convert < & > for some dom tags to let them able
     * to go through API calls.
     */
    public function handle()
    {
        foreach ($this->getIgnoredNodes() as $ignore) {
            $pattern = '#\<(?<tag>' .$ignore. ')(?<more>.*?)\>(?<content>.*?)\<\/' .$ignore. '\>#i';
            $matches = [];

            if (preg_match($pattern, $this->getSource(), $matches)) {
                $count = 0;
                $patterns = ['#\<' .implode('|', $this->usualTags). '(?<after>.*?)\>#', '#\</' .implode('|', $this->usualTags). '\>#'];
                foreach ($patterns as $current) {
                    $count += preg_match($current, $matches['content']);
                }

                if ($count === 0) {
                    $this->setSource(str_replace(
                        $matches[0],
                        '&lt;' .$matches['tag'].$matches['more']. '&gt;' .$matches['content']. '&lt;/' .$matches['tag']. '&gt;',
                        $this->getSource()
                    ));
                }
            }
        }
    }
}
