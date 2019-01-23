<?php

namespace Weglot\Parser\Check;

use WGSimpleHtmlDom\simple_html_dom;
use WGSimpleHtmlDom\simple_html_dom_node;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\WordEntry;
use Weglot\Parser\Check\Dom\AbstractDomChecker;
use Weglot\Parser\Parser;
use Weglot\Util\Text;

class DomCheckerProvider
{

    /**
     * @var array
     */
    protected $inlineNodes = [
        'a' , 'span',
        'strong', 'b',
        'em', 'i',
        'small', 'big',
        'sub', 'sup',
        'abbr',
        'acronym',
        'bdo',
        'cite',
        'kbd',
        'q', 'u'
    ];

    const DEFAULT_CHECKERS_NAMESPACE = '\\Weglot\\Parser\\Check\\Dom\\';

    /**
     * @var Parser
     */
    protected $parser = null;

    /**
     * @var array
     */
    protected $checkers = [];

    /**
     * @var array
     */
    protected $discoverCaching = [];

    /**
     * @var int
     */
    protected $translationEngine;

    /**
     * DomChecker constructor.
     * @param Parser $parser
     * @param int $translationEngine
     */
    public function __construct(Parser $parser, $translationEngine)
    {
        $this->setParser($parser);
        $this->setTranslationEngine($translationEngine);
        $this->loadDefaultCheckers();
    }

    /**
     * @param Parser $parser
     * @return $this
     */
    public function setParser(Parser $parser)
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * @return Parser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param array $inlineNodes
     * @return $this
     */
    public function setInlineNodes($inlineNodes)
    {
        $this->inlineNodes = $inlineNodes;

        return $this;
    }

    /**
     * @return array
     */
    public function getInlineNodes()
    {
        return $this->inlineNodes;
    }


    /**
     * @param int $translationEngine
     * @return $this
     */
    public function setTranslationEngine($translationEngine)
    {
        $this->translationEngine = $translationEngine;

        return $this;
    }

    /**
     * @return int
     */
    public function getTranslationEngine()
    {
        return $this->translationEngine;
    }

    /**
     * @param $checker
     * @return $this
     */
    public function addChecker($checker)
    {
        $this->checkers[] = $checker;

        return $this;
    }

    /**
     * @param array $checkers
     * @return $this
     */
    public function addCheckers(array $checkers)
    {
        $this->checkers = array_merge($this->checkers, $checkers);

        return $this;
    }

    /**
     * @return array
     */
    public function getCheckers()
    {
        $this->resetDiscoverCaching();

        return $this->checkers;
    }

    /**
     * @return $this
     */
    public function resetDiscoverCaching()
    {
        $this->discoverCaching = [];

        return $this;
    }

    /**
     * @param $domToSearch
     * @param simple_html_dom $dom
     * @return simple_html_dom_node
     */
    public function discoverCachingGet($domToSearch, simple_html_dom $dom)
    {
        if (!isset($discoverCaching[$domToSearch])) {
            $this->discoverCaching[$domToSearch] = $dom->find($domToSearch);
        }

        return $this->discoverCaching[$domToSearch];
    }

    /**
     * Load default checkers
     */
    protected function loadDefaultCheckers()
    {
        $files = array_diff(scandir(__DIR__ . '/Dom'), ['AbstractDomChecker.php', '..', '.']);
        $checkers = array_map(function ($filename) {
            return self::DEFAULT_CHECKERS_NAMESPACE . Text::removeFileExtension($filename);
        }, $files);

        $this->addCheckers($checkers);
    }

    /**
     * @param string $checker   Class of the Checker to add
     * @return bool
     */
    public function register($checker)
    {
        if ($checker instanceof AbstractDomChecker) {
            $this->addChecker($checker);
            return true;
        }
        return false;
    }

    /**
     * @param string $class
     * @return array
     */
    protected function getClassDetails($class)
    {
        $class = self::CHECKERS_NAMESPACE. $class;
        return [
            $class,
            $class::DOM,
            $class::PROPERTY,
            $class::WORD_TYPE
        ];
    }

    /**
     * @param simple_html_dom $dom
     * @return array
     * @throws InvalidWordTypeException
     */
    public function handle(simple_html_dom $dom)
    {
        $nodes = [];
        $checkers = $this->getCheckers();

        foreach ($checkers as $class) {
            list($selector, $property, $wordType) = $class::toArray();

            $discoveringNodes = $this->discoverCachingGet($selector, $dom);

            if($this->getTranslationEngine() <= 2) { // Old model
                foreach ($discoveringNodes as $k => $node) {
                    $instance = new $class($node, $property);

                    if ($instance->handle()) {
                        $this->getParser()->getWords()->addOne(new WordEntry($node->$property, $wordType));

                        $nodes[] = [
                            'node' => $node,
                            'class' => $class,
                            'property' => $property,
                        ];
                    } else {
                        if (strpos($node->$property, '&gt;') !== false || strpos($node->$property, '&lt;') !== false) {
                            $node->$property = str_replace(['&lt;', '&gt;'], ['<', '>'], $node->$property);
                        }
                    }
                }
            }
            if($this->getTranslationEngine() == 3)  { //New model

                for ($i = 0; $i < count($discoveringNodes); $i++) {
                    $node = $discoveringNodes[$i];
                    $instance = new $class($node, $property);

                    if ($instance->handle()) {

                        $attributes = [];

                        if($selector === 'text') {
                            $jump = 0;

                            while($this->shouldMergeWithSiblings($node, $jump))
                                $node = $node->parentNode();


                            $node = $this->getMinimalNode($node);

                            //We remove attributes from all child nodes and replace by wg-1, wg-2, etc... Real attributes are saved into $attributes.
                            $node = $this->removeAttributesFromChild($node, $attributes);

                            $i = $i + $jump;
                        }

                        $this->getParser()->getWords()->addOne(new WordEntry($node->$property, $wordType));

                        $nodes[] = [
                            'node' => $node,
                            'class' => $class,
                            'property' => $property,
                            'attributes' => $attributes,
                        ];

                    }
                }
            }

        }

        return $nodes;
    }

    public function shouldMergeWithSiblings($node, &$c) {
        //echo "Start shouldMergeWithSiblings node : ". $node->tag . " & content : " . $node->innertext(). ". c is : ". $c . "\n";
        if($this->isBlock($node)) {
          return false;
        }

        //echo "Checking node : " . $node->tag."\n";
        $siblings = $node->parentNode()->nodes;
        $siblings = $this->unsetValue($siblings, $node);
        //echo "Count :" . count($parent->nodes);
        $c_copy = $c;
        foreach($siblings as $sibling) {
           if($this->containsChildBlock($sibling, $c)) {
               $c = $c_copy;
               return false;
           }


        }
        return true;
    }

    public function containsChildBlock($node, &$c) {
        //echo "Start containsChildBlock node : ". $node->tag . " & content : " . $node->innertext(). ". c is : ". $c . "\n";
        if($this->isText($node)) {
            $c++;
            return false;
        }
        elseif($this->isInline($node)) {
          foreach($node->nodes as $n) {
              if($this->containsChildBlock($n, $c))
                 return true;
          }
          return false;

        }
        else {
            return true;
        }

    }

    public function getMinimalNode($node) {
        if($this->isText($node)) {
            return $node;
        }

        //We remove unnecessary wrapping nodes
        while(count($node->nodes) == 1)
            $node = $node->nodes[0];

        $notEmptyChild = [];
        foreach ($node->nodes as $n) {
            if(!$this->hasOnlyEmptyChild($n)) {
                $notEmptyChild[] = $n;
            }
        }

        if(count($notEmptyChild) == 1) {
            return $this->getMinimalNode($notEmptyChild[0]);
        }


        return $node;
    }


    public function removeAttributesFromChild($node, &$attributes) {

        foreach ($node->children() as $n) {
            $k = count($attributes)+1;
            $attributes['wg-'.$k] = $n->getAllAttributes();
            $n->attr = [];
            $n->setAttribute('wg-'.$k, "");
            $this->removeAttributesFromChild($n, $attributes);
        }

        return $node;
    }

    public function hasOnlyEmptyChild($node) {
        if($this->isText($node)) {
            if(Text::fullTrim($node->innertext()) != '')
               return false;
            else
                return true;
        }
        else {
            foreach ($node->nodes as $n) {
                if(!$this->hasOnlyEmptyChild($n))
                    return false;
            }
            return true;
        }
    }

    public function isInline($node) {
        return in_array($node->tag, $this->getInlineNodes());
    }

    public function isText($node) {
        return $node->tag == 'text';
    }

    public function isBlock($node) {
        return (!$this->isInline($node) && !$this->isText($node));
    }

    public function isInlineOrText($node) {
        return $this->isInline($node) || $this->isText($node);
    }

    public function unsetValue(array $array, $value, $strict = TRUE)
    {
        if(($key = array_search($value, $array, $strict)) !== FALSE) {
            unset($array[$key]);
        }
        return $array;
    }
}
