<?php
/**
 * @author Remy Berda
 * User: remy
 * Date: 12/06/2019
 * Time: 16:52
 */

namespace Weglot\Parser\Check\Regex;

use Weglot\Parser\Parser;


/**
 * Class RegexChecker
 * @package Weglot\Parser\Check
 */
abstract class RegexChecker
{
    /**
     * DOM node to match
     *
     * @var string
     */
    const REGEX = '';

    /**
     * DOM node to match
     *
     * @var string
     */
    const TYPE = '';

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * DomChecker constructor.
     * @param Parser $parser
     * @param string $jsonString
     */
    public function __construct(Parser $parser, $jsonString)
    {
        $this
            ->setParser($parser);
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
     * @return array
     */
    public static function toArray()
    {
        $class = \get_called_class();

        return [
            $class::REGEX,
            $class::TYPE,
            $class::$KEYS,
        ];
    }
}