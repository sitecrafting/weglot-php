<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 06/04/2018
 * Time: 16:15
 */

namespace Weglot\Client\Api\Shared;

interface AbstractCollectionInterface
{
    /**
     * Add one word at a time
     *
     * @param AbstractCollectionEntry $entry
     */
    public function addOne(AbstractCollectionEntry $entry);

    /**
     * Add several words at once
     *
     * @param AbstractCollectionEntry[] $entries
     */
    public function addMany(array $entries);
}
