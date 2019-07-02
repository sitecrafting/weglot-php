<?php

namespace Weglot\Parser\Check;

use Weglot\Parser\Check\Regex\RegexChecker;
use Weglot\Util\SourceType;
use WGSimpleHtmlDom\simple_html_dom;
use WGSimpleHtmlDom\simple_html_dom_node;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\WordEntry;
use Weglot\Parser\Check\Dom\AbstractDomChecker;
use Weglot\Parser\Parser;
use Weglot\Util\Text;

class RegexCheckerProvider
{

    const DEFAULT_CHECKERS_NAMESPACE = '\\Weglot\\Parser\\Check\\Regex\\';

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
     * DomChecker constructor.
     * @param Parser $parser
     * @param int $translationEngine
     */
    public function __construct(Parser $parser)
    {
        $this->setParser($parser);
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
        $files = array_diff(scandir(__DIR__ . '/Regex'), ['RegexChecker.php', 'JsonChecker.php', '..', '.']);
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
        if ($checker instanceof RegexChecker) {
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
            $class::REGEX,
            $class::TYPE,
            $class::VAR_NUMBER,
            $class::$KEYS,
        ];
    }

    /**
     * @param string $domString
     * @return array
     * @throws InvalidWordTypeException
     */
    public function handle($domString)
    {
        $checkers = $this->getCheckers();
        $regexes = [];
        foreach ($checkers as $class) {
            list($regex, $type, $varNumber, $extraKeys) = $class::toArray();
            preg_match_all($regex, $domString, $matches);
            if(isset($matches[$varNumber])) {
                $matches0 = $matches[0];
                $matches1 = $matches[$varNumber];
                foreach ($matches1 as $k => $match) {

                    if($type === SourceType::SOURCE_JSON) {
                        $regex = $this->getParser()->parseJSON($match, $extraKeys);
                    }
                    if($type === SourceType::SOURCE_TEXT) {
                        $regex = $this->getParser()->parseText($match, $matches0[$k]);
                    }
                    if($type === SourceType::SOURCE_HTML) {
                        $regex = $this->getParser()->parseHTML($match);
                    }

                    array_push($regexes, $regex);
                }
            }
        }
        return $regexes;
    }


}
