<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 10:12
 */

namespace Weglot\Parser;

use SimpleHtmlDom\simple_html_dom;
use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\WordEntry;
use Weglot\Client\Client;
use Weglot\Client\Endpoint\Translate;
use Weglot\Parser\Util\Server;
use GuzzleHttp\Exception\GuzzleException;

class Parser
{
    const ATTRIBUTE_NO_TRANSLATE = 'data-wg-notranslate';

    /**
     * @var Client
     */
    protected $client;

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
     * @param array $excludeBlocks
     */
    public function __construct(Client $client, $language_from, $language_to, array $excludeBlocks = [])
    {
        $this
            ->setClient($client)
            ->setLanguageFrom($language_from)
            ->setLanguageTo($language_to)
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

        foreach ($this->domCheckMapping() as $key => $elem) {
            foreach ($dom->find($key) as $k => $row) {
                foreach ($elem as $element) {
                    $property = $element['property'];
                    $t = $element['t'];
                    $type = $element['type'];

                    $class = '\\Weglot\\Parser\\Check\\' .ucfirst($type);
                    $instance = new $class($row, $property);

                    if ($instance->handle()) {
                        $words[] = [
                            't' => $t,
                            'w' => $row->$property,
                        ];

                        $nodes[] = [
                            'node' => $row,
                            'type' => $type,
                            'property' => $property,
                        ];
                    }
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

        /**
         * @TODO switch to user options
         */
        $absolute_url = Server::fullUrl($_SERVER);
        $bot = Server::detectBot($_SERVER);

        $params = [
            'language_from' => $this->getLanguageFrom(),
            'language_to' => $this->getLanguageTo(),
            'title' => $this->getTitle($dom),
            'request_url' => $absolute_url,
            'bot' => $bot
        ];
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
            'text' => [
                [
                    'property' => 'outertext',
                    't' => 1,
                    'type' => 'text',
                ],
            ],
            "input[type='submit'],input[type='button']" => [
                [
                    'property' => 'value',
                    't' => 2,
                    'type' => 'button',
                ],
                [
                    'property' => 'data-value',
                    't' => 1,
                    'type' => 'input_dv',
                ],
                [
                    'property' => 'data-order_button_text',
                    't' => 1,
                    'type' => 'input_dobt',
                ],
            ],

            "input[type='radio']" => [
                [
                    'property' => 'data-order_button_text',
                    't' => 2,
                    'type' => 'rad_obt',
                ],
            ],


            "td" => [
                [
                    'property' => 'data-title',
                    't' => 2,
                    'type' => 'td_dt',
                ],
            ],

            "input[type=\'text\'],input[type=\'password\'],input[type=\'search\'],input[type=\'email\'],input:not([type]),textarea"
            => [
                [
                    'property' => 'placeholder',
                    't' => 3,
                    'type' => 'placeholder',
                ],
            ],

            'meta[name="description"],meta[property="og:title"],meta[property="og:description"],meta[property="og:site_name"],meta[name="twitter:title"],meta[name="twitter:description"]'
            => [
                [
                    'property' => 'content',
                    't' => 4,
                    'type' => 'meta_desc',
                ],
            ],

            'iframe' => [
                [
                    'property' => 'src',
                    't' => 5,
                    'type' => 'iframe_src',
                ],
            ],

            'img' => [
                [
                    'property' => 'src',
                    't' => 6,
                    'type' => 'img_src',
                ],
                [
                    'property' => 'alt',
                    't' => 7,
                    'type' => 'img_alt',
                ],
            ],

            'a' => [
                [
                    'property' => 'href',
                    't' => 8,
                    'type' => 'a_pdf',
                ],
                [
                    'property' => 'title',
                    't' => 1,
                    'type' => 'a_title',
                ],
                [
                    'property' => 'data-value',
                    't' => 1,
                    'type' => 'a_dv',
                ],
                [
                    'property' => 'data-title',
                    't' => 1,
                    'type' => 'a_dt',
                ],
                [
                    'property' => 'data-tooltip',
                    't' => 1,
                    'type' => 'a_dto',
                ],
                [
                    'property' => 'data-hover',
                    't' => 1,
                    'type' => 'a_dho',
                ],
                [
                    'property' => 'data-content',
                    't' => 1,
                    'type' => 'a_dco',
                ],
                [
                    'property' => 'data-text',
                    't' => 1,
                    'type' => 'a_dte',
                ],
            ],

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
