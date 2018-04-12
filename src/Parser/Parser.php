<?php

namespace Weglot\Parser;

use SimpleHtmlDom\simple_html_dom;
use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\WordEntry;
use Weglot\Client\Client;
use Weglot\Client\Endpoint\Translate;
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
            ->setExcludeBlocks($excludeBlocks);
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

        $words = [];
        $nodes = [];

        $discoverCaching = [];

        foreach ($this->domCheckMapping() as $element) {
            if (!isset($discoverCaching[$element['dom']])) {
                $discoverCaching[$element['dom']] = $dom->find($element['dom']);
            }

            foreach ($discoverCaching[$element['dom']] as $k => $node) {
                $t = $element['t'];
                $property = $element['property'];
                $type = $element['type'];

                $class = '\\Weglot\\Parser\\Check\\' .ucfirst($type);
                $instance = new $class($node, $property);

                if ($instance->handle()) {
                    $words[] = [
                        't' => $t,
                        'w' => $node->$property,
                    ];

                    $nodes[] = [
                        'node' => $node,
                        'type' => $type,
                        'property' => $property,
                    ];
                }
            }
        }

        $jsons = [];
        $nbJsonStrings = 0;

        foreach ($dom->find('script[type="application/ld+json"]') as $k => $row) {
            $mustAddjson = false;
            $json = json_decode($row->innertext, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($this->getMicroData() as $key) {
                    $path = explode(">", $key);
                    $value = $this->getValue($json, $path);

                    if (isset($value)) {
                        $mustAddjson = true;
                        $this->addValues($value, $words, $nbJsonStrings);
                    }
                }

                if ($mustAddjson) {
                    array_push($jsons, ['node' => $row, 'json' => $json]);
                }
            }
        }

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
            $input = $translate->getInputWords();

            foreach ($words as $word) {
                $input->addOne(new WordEntry($word['w'], $word['t']));
            }
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

        $this->applyToDom($translated, $nodes, $words, $jsons);
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
     * @return array
     */
    protected function domCheckMapping()
    {
        return [
            [
                'dom' => 'text',
                'type' => 'text',
                'property' => 'outertext',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'input[type="submit"],input[type="button"]',
                'type' => 'button',
                'property' => 'value',
                't' => WordType::VALUE,
            ],
            [
                'dom' => 'input[type="submit"],input[type="button"]',
                'type' => 'input_dv',
                'property' => 'data-value',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'input[type="submit"],input[type="button"]',
                'type' => 'input_dobt',
                'property' => 'data-order_button_text',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'input[type="radio"]',
                'type' => 'rad_obt',
                'property' => 'data-order_button_text',
                't' => WordType::VALUE,
            ],
            [
                'dom' => "td",
                'type' => 'td_dt',
                'property' => 'data-title',
                't' => WordType::VALUE,
            ],
            [
                'dom' => 'input[type="text"],input[type="password"],input[type="search"],input[type="email"],input:not([type]),textarea',
                'type' => 'placeholder',
                'property' => 'placeholder',
                't' => WordType::PLACEHOLDER,
            ],
            [
                'dom' => 'meta[name="description"],meta[property="og:title"],meta[property="og:description"],meta[property="og:site_name"],meta[name="twitter:title"],meta[name="twitter:description"]',
                'type' => 'meta_desc',
                'property' => 'content',
                't' => WordType::META_CONTENT,
            ],
            [
                'dom' => 'iframe',
                'type' => 'iframe_src',
                'property' => 'src',
                't' => WordType::IFRAME_SRC
            ],
            [
                'dom' => 'img',
                'property' => 'src',
                't' => WordType::IMG_SRC,
                'type' => 'img_src',
            ],
            [
                'dom' => 'img',
                'type' => 'img_alt',
                'property' => 'alt',
                't' => WordType::IMG_ALT,
            ],
            [
                'dom' => 'a',
                'type' => 'a_pdf',
                'property' => 'href',
                't' => WordType::PDF_HREF,
            ],
            [
                'dom' => 'a',
                'type' => 'a_title',
                'property' => 'title',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'type' => 'a_dv',
                'property' => 'data-value',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'type' => 'a_dt',
                'property' => 'data-title',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'type' => 'a_dto',
                'property' => 'data-tooltip',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'type' => 'a_dho',
                'property' => 'data-hover',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'type' => 'a_dco',
                'property' => 'data-content',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'type' => 'a_dte',
                'property' => 'data-text',
                't' => WordType::TEXT,
            ]
        ];
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
     * @return array
     */
    protected function getMicroData()
    {
        return ["description"];
    }

    /**
     * @param TranslateEntry $translateEntry
     * @param array $nodes
     * @param array $words
     * @param array $jsons
     */
    protected function applyToDom(TranslateEntry $translateEntry, array $nodes, array $words, array $jsons)
    {
        $translated_words = $translateEntry->getOutputWords();

        for ($i = 0; $i < count($nodes); ++$i) {
            $property = $nodes[$i]['property'];
            $type = $nodes[$i]['type'];

            if ($translated_words[$i] !== null) {
                $current_translated = $translated_words[$i]->getWord();

                if ($type == "meta_desc") {
                    $nodes[$i]['node']->$property = htmlspecialchars($current_translated);
                } else {
                    $nodes[$i]['node']->$property = $current_translated;
                }


                if ($nodes[$i]['type'] == 'img_src') {
                    $nodes[$i]['node']->src = $current_translated;
                    if ($nodes[$i]['node']->hasAttribute('srcset') &&
                        $nodes[$i]['node']->srcset != '' &&
                        $current_translated != $words[$i]['w']) {
                        $nodes[$i]['node']->srcset = '';
                    }
                }
            }
        }

        $index = count($nodes);
        for ($j = 0; $j < count($jsons); $j++) {
            $jsonArray = $jsons[$j]['json'];
            $node = $jsons[$j]['node'];
            foreach ($this->getMicroData() as $key) {
                $path = explode(">", $key);
                $hasV = $this->getValue($jsonArray, $path);

                if (isset($hasV)) {
                    $this->setValues($jsonArray, $path, $translated_words, $index);
                }
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
     * @param array $translatedwords
     * @param int $index
     * @return null|void
     */
    public function setValues(&$data, $path, $translatedwords, &$index)
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
                $this->setValues($val, null, $translatedwords, $index);
            }
        } else {
            $temp = $translatedwords[$index]->getWord();
            $index++;
        }

        return;
    }
}
