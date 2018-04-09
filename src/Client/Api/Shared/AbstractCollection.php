<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 09/04/2018
 * Time: 16:29
 */

namespace Weglot\Client\Api\Shared;

use Countable;
use Iterator;
use JsonSerializable;

abstract class AbstractCollection implements Countable, Iterator, JsonSerializable, AbstractCollectionInterface
{
    /**
     * @var AbstractCollectionEntry[]
     */
    protected $collection = [];

    /**
     * @param AbstractCollectionEntry $entry
     * @return $this
     */
    public function addOne(AbstractCollectionEntry $entry)
    {
        $this->collection[] = $entry;

        return $this;
    }

    /**
     * @param array $entries
     * @return $this
     */
    public function addMany(array $entries)
    {
        foreach ($entries as $entry) {
            $this->addOne($entry);
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
