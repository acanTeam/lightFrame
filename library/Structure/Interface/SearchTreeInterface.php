<?php

namespace Structure\Interface;

/**
 * Interface implemented by all trees.
 *
 * @package Opus11
 */
interface SearchTreeInterface extends TreeInterface, SearchableContainerInterface
{
    /**
     * Returns the "smallest" object in this tree. The smallest object in 
     * this tree is the one which is less than all the rest.
     *
     * @return object ComparableInterface The smallest object in this tree.
     */
    public abstract function findMin();

    /**
     * Returns the "largest" object in this tree. The largest object in this 
     * tree is the one which is greater than all the rest.
     *
     * @return object ComparableInterface The largest object in this tree.
     */
    public abstract function findMax();
}
