<?php

namespace Weglot\Parser;

use SimpleHtmlDom\simple_html_dom;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\WordCollection;
use Weglot\Client\Api\WordEntry;
use Weglot\Client\Client;
use Weglot\Client\Endpoint\Translate;
use Weglot\Parser\Check\ImageSource;
use Weglot\Parser\Check\MetaContent;
use Weglot\Parser\ConfigProvider\ConfigProviderInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Parser
 * @package Weglot\Parser
 */
class Parser
{
    /**
     * Attribute to match in DOM when we don't want to translate innertext & childs.
     */
    const ATTRIBUTE_NO_TRANSLATE = 'data-wg-notranslate';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ConfigProviderInterface
     */
    protected $configProvider;

    /**
     * @var array
     */
    protected $excludeBlocks;

    /**
     * @var string
     */
    protected $languageFrom;

    /**
     * @var string
     */
    protected $languageTo;

    /**
     * @var WordCollection
     */
    protected $words;

    /**
     * Parser constructor.
     * @param Client $client
     * @param string $language_from
     * @param string $language_to
     * @param ConfigProviderInterface $config
     * @param array $excludeBlocks
     */
    public function __construct(Client $client, $language_from, $language_to, ConfigProviderInterface $config, array $excludeBlocks = [])
    {
        $this
            ->setClient($client)
            ->setLanguageFrom($language_from)
            ->setLanguageTo($language_to)
            ->setConfigProvider($config)
            ->setExcludeBlocks($excludeBlocks)
            ->setWords(new WordCollection());
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param array $excludeBlocks
     * @return $this
     */
    public function setExcludeBlocks(array $excludeBlocks)
    {
        $this->excludeBlocks = $excludeBlocks;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludeBlocks()
    {
        return $this->excludeBlocks;
    }

    /**
     * @param ConfigProviderInterface $config
     * @return $this
     */
    public function setConfigProvider(ConfigProviderInterface $config)
    {
        $this->configProvider = $config;

        return $this;
    }

    /**
     * @return ConfigProviderInterface
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }

    /**
     * @param string $languageFrom
     * @return $this
     */
    public function setLanguageFrom($languageFrom)
    {
        $this->languageFrom = $languageFrom;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageFrom()
    {
        return $this->languageFrom;
    }

    /**
     * @param string $languageTo
     * @return $this
     */
    public function setLanguageTo($languageTo)
    {
        $this->languageTo = $languageTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageTo()
    {
        return $this->languageTo;
    }

    /**
     * @param WordCollection $wordCollection
     * @return $this
     */
    public function setWords(WordCollection $wordCollection)
    {
        $this->words = $wordCollection;

        return $this;
    }

    /**
     * @return WordCollection
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * @param string $source
     * @return string
     */
    public function translate($source)
    {
        if ($this->client->apiKeyCheck()) {
            $source = $this->ignoreNodes($source);
        }

        $dom = \SimpleHtmlDom\str_get_html(
            $source,
            true,
            true,
            DEFAULT_TARGET_CHARSET,
            false,
            DEFAULT_BR_TEXT,
            DEFAULT_SPAN_TEXT
        );

        $this->filterExcludeBlocks($dom);

        $checker = new DomChecker($this, $dom);
        $nodes = $checker->handle();

        $checker = new JsonLdChecker($this, $dom);
        $jsons = $checker->handle();

        // Translate endpoint parameters
        $params = [
            'language_from' => $this->getLanguageFrom(),
            'language_to' => $this->getLanguageTo()
        ];

        if ($this->getConfigProvider()->getAutoDiscoverTitle()) {
            $params['title'] = $this->getTitle($dom);
        }
        $params = array_merge($params, $this->getConfigProvider()->asArray());

        try {
            $translate = new TranslateEntry($params);
            $translate->setInputWords($this->getWords());
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $translate = new Translate($translate, $this->client);

        try {
            $translated = $translate->handle();
        } catch (\Exception $e) {
            die($e->getMessage());
        } catch (GuzzleException $e) {
            die($e->getMessage());
        }

        $this->applyToDom($translated, $nodes, $jsons);
        return $dom->save();
    }

    /**
     * Convert < & > for some dom tags to let them able
     * to go through API calls.
     *
     * @param string $source
     * @return string
     */
    protected function ignoreNodes($source)
    {
        $nodes_to_ignore = [
            ['<strong>', '</strong>'],
            ['<em>', '</em>'],
            ['<abbr>', '</abbr>'],
            ['<acronym>', '</acronym>'],
            ['<b>', '</b>'],
            ['<bdo>', '</bdo>'],
            ['<big>', '</big>'],
            ['<cite>', '</cite>'],
            ['<kbd>', '</kbd>'],
            ['<q>', '</q>'],
            ['<small>', '</small>'],
            ['<sub>', '</sub>'],
            ['<sup>', '</sup>'],
        ];

        foreach ($nodes_to_ignore as $ignore) {
            $pattern = '#' . $ignore[0] . '([^>]*)?' . $ignore[1] . '#';
            $replace = htmlentities($ignore[0]) . '$1' . htmlentities($ignore[1]);
            $source = preg_replace($pattern, $replace, $source);
        }

        return $source;
    }

    /**
     * Add ATTRIBUTE_NO_TRANSLATE to dom elements that don't
     * wanna be translated.
     *
     * @param simple_html_dom $dom
     */
    protected function filterExcludeBlocks(simple_html_dom &$dom)
    {
        foreach ($this->excludeBlocks as $exception) {
            foreach ($dom->find($exception) as $k => $row) {
                $attribute = self::ATTRIBUTE_NO_TRANSLATE;
                $row->$attribute = '';
            }
        }
    }

    /**
     * @param simple_html_dom $dom
     * @return string
     */
    protected function getTitle(simple_html_dom $dom)
    {
        $title = 'Empty title';
        foreach ($dom->find('title') as $k => $node) {
            if ($node->innertext != '') {
                $title = $node->innertext;
            }
        }
        return $title;
    }

    /**
     * @param TranslateEntry $translateEntry
     * @param array $nodes
     * @param array $jsons
     */
    protected function applyToDom(TranslateEntry $translateEntry, array $nodes, array $jsons)
    {
        $words = $this->getWords();
        $translated_words = $translateEntry->getOutputWords();

        for ($i = 0; $i < count($nodes); ++$i) {
            $currentNode = $nodes[$i];
            $property = $currentNode['property'];

            if ($translated_words[$i] !== null) {
                $current_translated = $translated_words[$i]->getWord();

                if ($currentNode['class'] instanceof MetaContent) {
                    $currentNode['node']->$property = htmlspecialchars($current_translated);
                } else {
                    $currentNode['node']->$property = $current_translated;
                }

                if ($currentNode['class'] instanceof ImageSource) {
                    $currentNode['node']->src = $current_translated;
                    if ($currentNode['node']->hasAttribute('srcset') &&
                        $currentNode['node']->srcset != '' &&
                        $current_translated != $words[$i]->getWord()) {
                        $currentNode['node']->srcset = '';
                    }
                }
            }
        }

        $index = count($nodes);
        for ($j = 0; $j < count($jsons); $j++) {
            $jsonArray = $jsons[$j]['json'];
            $node = $jsons[$j]['node'];

            $hasV = $this->getValue($jsonArray, ['description']);

            if (isset($hasV)) {
                $this->setValues($jsonArray, ['description'], $translated_words, $index);
            }

            $node->innertext = json_encode($jsonArray, JSON_PRETTY_PRINT);
        }
    }

    /**
     * ----------------------------------------------------------------------------------------------------------------
     *
     * Not refactored
     *
     * ----------------------------------------------------------------------------------------------------------------
     */

    /**
     * @param array $data
     * @param $path
     * @return null
     */
    public function getValue($data, $path)
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
     * @param $value
     * @param array $words
     * @param int $nbJsonStrings
     */
    public function addValues($value, &$words, &$nbJsonStrings)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $this->addValues($val, $words, $nbJsonStrings);
            }
        } else {
            array_push(
                $words,
                [
                    't' => 1,
                    'w' => $value,
                ]
            );
            $nbJsonStrings++;
        }
    }

    /**
     * @param $data
     * @param $path
     * @param WordCollection $words
     * @param int $index
     * @return null|void
     */
    public function setValues(&$data, $path, WordCollection $words, &$index)
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
                $this->setValues($val, null, $words, $index);
            }
        } else {
            $temp = $words[$index]->getWord();
            $index++;
        }

        return;
    }
}
