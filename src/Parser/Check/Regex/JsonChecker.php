<?php

namespace Weglot\Parser\Check\Regex;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\WordEntry;
use Weglot\Parser\Parser;
use Weglot\Util\JsonUtil;
use Weglot\Util\Text;

/**
 * Class JsonLdChecker
 * @package Weglot\Parser\Check
 */
class JsonChecker
{
    const DEFAULT_KEYS = array(  'description' , 'name' ); //TODO : pouvoir ajouter des clés facilement

    protected $jsonString;
    protected $parser;
    protected $extraKeys;

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

    public function __construct(Parser $parser, string $jsonString, $extraKeys)
    {
        $this
            ->setParser($parser)
            ->setJSonString($jsonString)
            ->setExtraKeys($extraKeys);
    }

    /**
     * @param string $jsonString
     * @return $this
     */
    public function setJsonString(string $jsonString)
    {
        $this->jsonString = $jsonString;

        return $this;
    }

    /**
     * @return string
     */
    public function getJsonString()
    {
        return $this->jsonString;
    }

    /**
     * @param array $extraKeys
     * @return $this
     */
    public function setExtraKeys($extraKeys)
    {
        $this->extraKeys = $extraKeys;

        return $this;
    }

    /**
     * @return array
     */
    public function getExtraKeys()
    {
        return $this->extraKeys;
    }

    /**
     * @return array
     * @throws InvalidWordTypeException
     */
    public function handle()
    {
        $json = json_decode($this->jsonString, true);

        $paths = [];
        $this->findWords($json, "", $paths);

        return array(
            "type" => "JSON",
            "jsonString" => $this->jsonString,
            "jsonArray" => $json,
            "paths" => $paths);

    }

    public function findWords($json, $currentKey, &$paths) {

        foreach ($json as $key => $value) {
            if(is_array($value)) {
                $this->findWords($value, ltrim($currentKey.JsonUtil::SEPARATOR.$key, JsonUtil::SEPARATOR), $paths);
            }
            else {
                if(Text::isHTML($value)) {
                    $parsed = $this->getParser()->parseHTML($value);
                    array_push($paths, array( "key" => ltrim($currentKey.JsonUtil::SEPARATOR.$key, JsonUtil::SEPARATOR) ,
                        "count" => count($parsed['nodes']) , "dom" => $parsed['dom'], "nodes" => $parsed['nodes']));

                }
                if(in_array($key, array_unique(array_merge(self::DEFAULT_KEYS , $this->getExtraKeys())) , true)) {
                    array_push($paths, array( "key" => ltrim($currentKey.JsonUtil::SEPARATOR.$key, JsonUtil::SEPARATOR) ,
                        "count" => 1 , "dom" => null, "nodes" => null));
                    $this->getParser()->getWords()->addOne(new WordEntry($value, WordType::TEXT));
                }
            }
        }
    }
}
