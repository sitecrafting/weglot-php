<?php

namespace Weglot\Client\Api\Shared;

use Countable;
use Iterator;
use ArrayAccess;
use JsonSerializable;

/**
 * Class AbstractCollection
 * @package Weglot\Client\Api\Shared
 */
abstract class AbstractCollection implements Countable, Iterator, ArrayAccess, JsonSerializable, AbstractCollectionInterface
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
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (isset($this->collection[$offset]) && $value instanceof AbstractCollectionEntry) {
            $this->collection[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->collection);
    }
}
