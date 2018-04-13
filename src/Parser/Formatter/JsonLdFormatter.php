<?php

namespace Weglot\Parser\Formatter;

use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\WordCollection;
use Weglot\Parser\Parser;

/**
 * Class JsonLdFormatter
 * @package Weglot\Parser\Formatter
 */
class JsonLdFormatter extends AbstractFormatter
{
    /**
     * @var int
     */
    protected $nodesCount;

    /**
     * JsonLdFormatter constructor.
     * @param Parser $parser
     * @param TranslateEntry $translated
     * @param int $nodesCount
     */
    public function __construct(Parser $parser, TranslateEntry $translated, $nodesCount)
    {
        $this->setNodesCount($nodesCount);
        parent::__construct($parser, $translated);
    }

    /**
     * @param int $nodesCount
     * @return $this
     */
    public function setNodesCount($nodesCount)
    {
        $this->nodesCount = $nodesCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getNodesCount()
    {
        return $this->nodesCount;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $jsons)
    {
        $translated_words = $this->getTranslated()->getOutputWords();

        for ($j = 0; $j < count($jsons); $j++) {
            $jsonArray = $jsons[$j]['json'];
            $node = $jsons[$j]['node'];

            $hasV = $this->getValue($jsonArray, ['description']);

            if (isset($hasV)) {
                $this->setValues($jsonArray, ['description'], $translated_words);
            }

            $node->innertext = json_encode($jsonArray, JSON_PRETTY_PRINT);
        }
    }

    /**
     * @param array $data
     * @param $path
     * @return null
     */
    private function getValue($data, $path)
    {
        $temp = $data;
        foreach ($path as $key) {
            if (array_key_exists($key, $temp)) {
                $temp = $temp[$key];
            } else {
                return null;
            }
        }
        return $temp;
    }

    /**
     * @param $data
     * @param $path
     * @param WordCollection $words
     * @return null|void
     */
    private function setValues(&$data, $path, WordCollection $words)
    {
        $temp = &$data;
        foreach ($path as $key) {
            if (array_key_exists($key, $temp)) {
                $temp = &$temp[$key];
            } else {
                return null;
            }
        }

        if (is_array($temp)) {
            foreach ($temp as $key => &$val) {
                $this->setValues($val, null, $words);
            }
        } else {
            $temp = $words[$this->getNodesCount()]->getWord();
            $this->setNodesCount($this->getNodesCount() + 1);
        }

        return;
    }
}
