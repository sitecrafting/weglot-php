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
        'a','span',
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
    public function __construct($source = null)
    {
        $this->setSource($source);
    }

    /**
     * @param array $ignoredNodes
     * @return $this
     */
    public function setIgnoredNodes($ignoredNodes){
        $this->ignoredNodes = $ignoredNodes;
        return $this;
    }

    /**
     * @return array
     */
    public function getIgnoredNodes(){
        return $this->ignoredNodes;
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
     * @param array $matches
     */
    protected function replaceContent($matches)
    {

        $this->setSource(
            str_replace(
                $matches[0],
                '&lt;' .$matches['tag'].str_replace('>', '&gt;', str_replace('<', '&lt;', $matches['more'])). '&gt;' . $matches['content']. '&lt;/' . $matches['tag'] . '&gt;',
                $this->getSource()
            )
        );
    }



    /**
     * Convert < & > for some dom tags to let them able
     * to go through API calls.
     */
    public function handle()
    {
        $new = false;

        if(!$new) {
            $this->ignoredNodes = [
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


            $pattern = '#<(?<tag>' .implode('|', $this->ignoredNodes). ')(?<more>\s.*?)?\>(?<content>[^>]*?)\<\/(?<tagclosed>' .implode('|', $this->ignoredNodes). ')>#i';
            $matches = [];

            // Using while instead of preg_match_all is the key to handle nested ignored nodes. We will escape ignored nodes
            while (preg_match($pattern, $this->getSource(), $matches)) {
                $this->replaceContent($matches);
            }
        }
        else {
            // time for the BIG regex ...
            $pattern = '#<(?<tag>' .implode('|', $this->ignoredNodes). ')(?<more>\s[^>]*?)?\>(?<content>((?!<([a-z]+)).)*?)\<\/(?<tagclosed>' .implode('|', $this->ignoredNodes). ')>#i';
            $matches = [];

            // Using while instead of preg_match_all is the key to handle nested ignored nodes. We will escape ignored nodes
            while (preg_match($pattern, $this->getSource(), $matches)) {
                $this->replaceContent($matches);
            }



            //Now we will remove wrapping tag and empty starting tags and ending tags and unescape them back
            $patternWrapp = '#>(?<prespace>\s*?)&lt;(?<tag>' .implode('|', $this->ignoredNodes). ')(?<more>\s((?!&lt;).)*?)?&gt;(?<content>((?!&lt;\/(\2)&gt;.*&lt;(\2)).)*?)&lt;\/(\2)&gt;(?<endspace>\s*?)<#i';
            $matchesWrap = [];


            $patternStart = '#>(?<prespace>\s*?)&lt;(?<tag>' .implode('|', $this->ignoredNodes). ')(?<more>\s((?!&lt;).)*?)?&gt;(?<content>\s*?)&lt;\/(\2)&gt;#';
            $matchesStart = [];

            $patternEnd = '#&lt;(?<tag>' .implode('|', $this->ignoredNodes). ')(?<more>\s((?!&lt;).)*?)?&gt;(?<content>\s*?)&lt;\/(\1)&gt;(?<endspace>\s*?)<#';
            $matchesEnd = [];

            $is_mw = preg_match($patternWrapp, $this->getSource(), $matchesWrap);
            $is_ms = preg_match($patternStart, $this->getSource(), $matchesStart);
            $is_me = preg_match($patternEnd, $this->getSource(), $matchesEnd);

            // Using while instead of preg_match_all is the key to handle nested ignored nodes. We will escape ignored nodes
            while ($is_mw || $is_ms || $is_me) {


                if($is_mw) {
                    $this->setSource(
                        str_replace(
                            $matchesWrap[0],
                            '>' . $matchesWrap['prespace'] . '<' .$matchesWrap['tag']. $matchesWrap['more']. '>' . $matchesWrap['content']. '</' . $matchesWrap['tag'] . '>' . $matchesWrap['endspace'] . '<',
                            $this->getSource()
                        )
                    );
                }
                elseif ($is_ms) {

                    $this->setSource(
                        str_replace(
                            $matchesStart[0],
                            '>' . $matchesStart['prespace'] . '<' .$matchesStart['tag']. $matchesStart['more']. '>' . $matchesStart['content']. '</' . $matchesStart['tag'] . '>',
                            $this->getSource()
                        )
                    );
                }
                elseif ($is_me) {
                    $this->setSource(
                        str_replace(
                            $matchesEnd[0],
                            '<' .$matchesEnd['tag']. $matchesEnd['more']. '>' . $matchesEnd['content']. '</' . $matchesEnd['tag'] . '>' . $matchesEnd['endspace'] . '<',
                            $this->getSource()
                        )
                    );
                }


                $is_mw = preg_match($patternWrapp, $this->getSource(), $matchesWrap);
                $is_ms = preg_match($patternStart, $this->getSource(), $matchesStart);
                $is_me = preg_match($patternEnd, $this->getSource(), $matchesEnd);
            }

        }



    }
}
