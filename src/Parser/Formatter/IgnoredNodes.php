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
        'cite',
        'kbd',
        'q',
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
            $pattern = ['#\<' .$ignore. '(?<after>.*?)\>#', '#\</' .$ignore. '\>#'];
            $replace = [htmlentities('<' .$ignore. '$1>'), htmlentities('</' .$ignore. '>')];
            $this->setSource(preg_replace($pattern, $replace, $this->getSource()));
        }
    }
}
