<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 16:15
 */

namespace Weglot\Client\Api;

use JsonSerializable;
use Weglot\Client\Api\Enum\WordType;

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
    protected $type = WordType::GENERIC;

    public function __construct($word, $type = WordType::GENERIC)
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
        /**
         * Thoses WordType::__MIN and WordType::__MAX values are
         * only used to check if given type is okay according to
         * what we have in WordType.
         *
         * @see src/Client/Api/Enum/WordType.php
         */
        if (!($type >= WordType::__MIN && $type <= WordType::__MAX)) {
            return true;
        }
        $this->type = $type;
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
