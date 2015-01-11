<?php

namespace Structure\Interface;

/**
 * Interface implemented by all partitions.
 */
interface PartitionInterface extends SetInterface
{
    /**
     * Returns the element of this partition that contains the specified item.
     *
     * @param integer An item.
     * @return object SetInterface The element of this partition that contains the item.
     */
    public abstract function findItem($item);

    /**
     * Joins two specified elements of this partition.
     *
     * @param object SetInterface $set1 An element of this partition.
     * @param object SetInterface $set2 An element of this partition.
     */
    public abstract function join(SetInterface $set1, SetInterface $set2);
}
