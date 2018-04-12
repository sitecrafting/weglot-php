<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 12/04/2018
 * Time: 15:45
 */

namespace Weglot\Parser;

use SimpleHtmlDom\simple_html_dom;
use SimpleHtmlDom\simple_html_dom_node;
use Weglot\Parser\Util\Text;

class JsonChecker
{

    /**
     * @var simple_html_dom
     */
    protected $dom;

    /**
     * @var array
     */
    protected $words;

    /**
     * JsonChecker constructor.
     * @param simple_html_dom $dom
     * @param array $words
     */
    public function __construct(simple_html_dom $dom, array $words)
    {
        $this
            ->setDom($dom)
            ->setWords($words);
    }

    /**
     * @param simple_html_dom $dom
     * @return $this
     */
    public function setDom(simple_html_dom $dom)
    {
        $this->dom = $dom;

        return $this;
    }

    /**
     * @return simple_html_dom
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * @param $words
     * @return $this
     */
    public function setWords($words)
    {
        $this->words = $words;

        return $this;
    }

    /**
     * @return array
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * @return array
     */
    public function handle()
    {
        $jsons = [];
        $countJsonStrings = 0;

        foreach ($this->dom->find('script[type="application/ld+json"]') as $k => $row) {
            $mustAddjson = false;
            $json = json_decode($row->innertext, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $path = explode(">", 'description');
                $value = $this->getValue($json, $path);

                if (isset($value)) {
                    $mustAddjson = true;
                    $this->addValues($value, $countJsonStrings);
                }

                if ($mustAddjson) {
                    $jsons[] = [
                        'node' => $row,
                        'json' => $json
                    ];
                }
            }
        }

        return [
            $this->words,
            $jsons
        ];
    }

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
    public function addValues($value, &$nbJsonStrings)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $this->addValues($val, $nbJsonStrings);
            }
        } else {
            $this->words[] = [
                't' => 1,
                'w' => $value,
            ];
            $nbJsonStrings++;
        }
    }
}
