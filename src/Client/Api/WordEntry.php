<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 16:15
 */

namespace Weglot\Client\Api;

use JsonSerializable;

/**
 * Enum WordEntryType
 * Used to define where was the text we are parsing
 *
 * @package Weglot\Client\Api
 */
abstract class WordEntryType
{
    const GENERIC = 0;
    const TEXT = 1;
    const VALUE = 2;
    const PLACEHOLDER = 3;
    const META_CONTENT = 4;
    const IFRAME_SRC = 5;
    const IMG_SRC = 6;
    const IMG_ALT = 7;
    const PDF_HREF = 8;
}

/**
 * Class WordEntry
 * @package Weglot\Client\Api
 */
class WordEntry implements JsonSerializable
{
    /**
     * @var string
     */
    protected $word;

    /**
     * @var int
     */
    protected $type = WordEntryType::GENERIC;

    public function __construct($word, $type = WordEntryType::GENERIC)
    {
        $this->setWord($word);
        $this->setType($type);
    }

    /**
     * @param string $word
     */
    public function setWord($word)
    {
        $this->word = $word;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * Set type of word you gonna translate.
     * Returns false if type is incorrect.
     *
     * @param int $type
     * @return bool
     */
    public function setType($type)
    {
        if ($type >= 0 && $type <= 8) {
            $this->type = $type;
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            't' => $this->getType(),
            'w' => $this->getWord()
        ];
    }
}
