<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 12/04/2018
 * Time: 15:45
 */

namespace Weglot\Parser;

use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\WordEntry;

class JsonLdChecker extends AbstractChecker
{
    /**
     * @return array
     * @throws InvalidWordTypeException
     */
    public function handle()
    {
        $jsons = [];
        $countJsonStrings = 0;

        foreach ($this->dom->find('script[type="application/ld+json"]') as $k => $row) {
            $mustAddjson = false;
            $json = json_decode($row->innertext, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $this->getValue($json, ['description']);

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

        return $jsons;
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
     * @param $nbJsonStrings
     * @throws InvalidWordTypeException
     */
    public function addValues($value, &$nbJsonStrings)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $this->addValues($val, $nbJsonStrings);
            }
        } else {
            $this->getParser()->getWords()->addOne(new WordEntry($value, WordType::TEXT));
            $nbJsonStrings++;
        }
    }
}
