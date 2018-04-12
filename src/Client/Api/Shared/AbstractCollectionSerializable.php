<?php

namespace Weglot\Client\Api\Shared;

use Countable;
use Iterator;
use ArrayAccess;
use JsonSerializable;

/**
 * Trait AbstractCollectionSerializable
 * @package Weglot\Client\Api\Shared
 */
trait AbstractCollectionSerializable
{
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
}
