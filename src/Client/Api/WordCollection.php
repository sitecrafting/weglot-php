<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 16:15
 */

namespace Weglot\Client\Api;

use Countable;
use Iterator;
use JsonSerializable;

class WordCollection implements Countable, Iterator, JsonSerializable, WordCollectionInterface
{
    /**
     * @var WordEntry[]
     */
    protected $collection = [];

    /**
     * @param WordEntry $entry
     * @return $this
     */
    public function addWord(WordEntry $entry)
    {
        $this->collection[] = $entry;

        return $this;
    }

    /**
     * @param array $entries
     * @return $this
     */
    public function addWords(array $entries)
    {
        foreach ($entries as $entry) {
            $this->addWord($entry);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->collection) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        return reset($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $words = [];
        foreach ($this->collection as $entry) {
            $words[] = $entry->jsonSerialize();
        }

        return $words;
    }
    
    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->collection);
    }
}
