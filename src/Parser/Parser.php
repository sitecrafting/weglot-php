<?php

namespace Weglot\Parser;

use SimpleHtmlDom\simple_html_dom;
use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\WordEntry;
use Weglot\Client\Client;
use Weglot\Client\Endpoint\Translate;
use Weglot\Parser\Check\A_dco;
use Weglot\Parser\Check\A_dho;
use Weglot\Parser\Check\A_dt;
use Weglot\Parser\Check\A_dte;
use Weglot\Parser\Check\A_dto;
use Weglot\Parser\Check\A_dv;
use Weglot\Parser\Check\A_pdf;
use Weglot\Parser\Check\A_title;
use Weglot\Parser\Check\Button;
use Weglot\Parser\Check\Iframe_src;
use Weglot\Parser\Check\Img_alt;
use Weglot\Parser\Check\Img_src;
use Weglot\Parser\Check\Input_dobt;
use Weglot\Parser\Check\Input_dv;
use Weglot\Parser\Check\Meta_desc;
use Weglot\Parser\Check\Placeholder;
use Weglot\Parser\Check\Rad_obt;
use Weglot\Parser\Check\Td_dt;
use Weglot\Parser\Check\Text;
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
                $property = $element['property'];

                $instance = new $element['class']($node, $property);

                if ($instance->handle()) {
                    $words[] = [
                        't' => $element['t'],
                        'w' => $node->$property,
                    ];

                    $nodes[] = [
                        'node' => $node,
                        'class' => $element['class'],
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
                'class' => Text::class,
                'property' => 'outertext',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'input[type="submit"],input[type="button"]',
                'class' => Button::class,
                'property' => 'value',
                't' => WordType::VALUE,
            ],
            [
                'dom' => 'input[type="submit"],input[type="button"]',
                'class' => Input_dv::class,
                'property' => 'data-value',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'input[type="submit"],input[type="button"]',
                'class' => Input_dobt::class,
                'property' => 'data-order_button_text',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'input[type="radio"]',
                'class' => Rad_obt::class,
                'property' => 'data-order_button_text',
                't' => WordType::VALUE,
            ],
            [
                'dom' => "td",
                'class' => Td_dt::class,
                'property' => 'data-title',
                't' => WordType::VALUE,
            ],
            [
                'dom' => 'input[type="text"],input[type="password"],input[type="search"],input[type="email"],input:not([type]),textarea',
                'class' => Placeholder::class,
                'property' => 'placeholder',
                't' => WordType::PLACEHOLDER,
            ],
            [
                'dom' => 'meta[name="description"],meta[property="og:title"],meta[property="og:description"],meta[property="og:site_name"],meta[name="twitter:title"],meta[name="twitter:description"]',
                'class' => Meta_desc::class,
                'property' => 'content',
                't' => WordType::META_CONTENT,
            ],
            [
                'dom' => 'iframe',
                'class' => Iframe_src::class,
                'property' => 'src',
                't' => WordType::IFRAME_SRC
            ],
            [
                'dom' => 'img',
                'class' => Img_src::class,
                'property' => 'src',
                't' => WordType::IMG_SRC,
            ],
            [
                'dom' => 'img',
                'class' => Img_alt::class,
                'property' => 'alt',
                't' => WordType::IMG_ALT,
            ],
            [
                'dom' => 'a',
                'class' => A_pdf::class,
                'property' => 'href',
                't' => WordType::PDF_HREF,
            ],
            [
                'dom' => 'a',
                'class' => A_title::class,
                'property' => 'title',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'class' => A_dv::class,
                'property' => 'data-value',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'class' => A_dt::class,
                'property' => 'data-title',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'class' => A_dto::class,
                'property' => 'data-tooltip',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'class' => A_dho::class,
                'property' => 'data-hover',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'class' => A_dco::class,
                'property' => 'data-content',
                't' => WordType::TEXT,
            ],
            [
                'dom' => 'a',
                'class' => A_dte::class,
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
            $currentNode = $nodes[$i];
            $property = $currentNode['property'];

            if ($translated_words[$i] !== null) {
                $current_translated = $translated_words[$i]->getWord();

                if ($currentNode['class'] instanceof Meta_desc) {
                    $currentNode['node']->$property = htmlspecialchars($current_translated);
                } else {
                    $currentNode['node']->$property = $current_translated;
                }


                if ($currentNode['class'] instanceof Img_src) {
                    $currentNode['node']->src = $current_translated;
                    if ($currentNode['node']->hasAttribute('srcset') &&
                        $currentNode['node']->srcset != '' &&
                        $current_translated != $words[$i]['w']) {
                        $currentNode['node']->srcset = '';
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
