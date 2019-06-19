<?php

namespace Weglot\Parser\Formatter;

use Weglot\Client\Api\TranslateEntry;
use Weglot\Parser\Parser;
use Weglot\Util\JsonUtil;

/**
 * Class JsonFormatter
 * @package Weglot\Parser\Formatter
 */
class JsonFormatter extends AbstractFormatter
{

    /**
     * @var string
     */
    protected $source;

    /**
     * JsonLdFormatter constructor.
     * @param Parser $parser
     * @param string $source
     * @param TranslateEntry $translated
     * @param int $nodesCount
     */
    public function __construct(Parser $parser, $source, TranslateEntry $translated)
    {
        $this->setSource($source);
        parent::__construct($parser, $translated);
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
     * {@inheritdoc}
     */
    public function handle(array $tree, &$index)
    {
        $translated_words = $this->getTranslated()->getOutputWords();

            $jsonString = $tree['jsonString'];
            $jsonArray = $tree['jsonArray'];
            $paths = $tree['paths'];

            foreach ($paths as $path) {
               $key = $path['key'];
               $count = $path['count'];
               $dom = $path['dom'];
               $nodes = $path['nodes'];
               if(empty($nodes)) {
                   $jsonArray = JsonUtil::set($translated_words, $jsonArray, $key, $index);
               }
               else {
                   if($nodes) {
                       $formatter = new DomFormatter($this->getParser(),  $this->getTranslated());
                       $formatter->handle($nodes, $index);
                       $jsonArray= JsonUtil::setHTML($dom->save(), $jsonArray, $key);
                   }
               }
            }
            $this->setSource(str_replace($jsonString, json_encode($jsonArray ), $this->getSource()));


        return $this->getSource();
    }
}
