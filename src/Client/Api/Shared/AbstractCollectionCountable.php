<?php

namespace Weglot\Client\Api\Shared;

use Countable;
use Iterator;
use ArrayAccess;
use JsonSerializable;

/**
 * Trait AbstractCollectionCountable
 * @package Weglot\Client\Api\Shared
 */
trait AbstractCollectionCountable
{
    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->collection);
    }
}
