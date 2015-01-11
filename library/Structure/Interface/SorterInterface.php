<?php

namespace Structure\Interface;

use Structure\BasicArray;

/**
 * Interface implemented by all sorters.
 * A sorter is an abstract machine that sorts an array of comparable objects.
 */
interface SorterInterface extends ObjectInterface
{
    /**
     * Sorts the specified array of comparable
     * objects from "smallest" to "largest".
     *
     * @param object BasicArray $array The array of objects to be sorted.
     */
    public abstract function sort(BasicArray $array);
}
