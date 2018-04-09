<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 16:15
 */

namespace Weglot\Client\Api;

interface WordCollectionInterface
{
    /**
     * Add one word at a time
     *
     * @param WordEntry $entry
     */
    public function addWord(WordEntry $entry);

    /**
     * Add several words at once
     *
     * @param WordEntry[] $entries
     */
    public function addWords(array $entries);
}
