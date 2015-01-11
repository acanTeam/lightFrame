<?php

namespace Structure\Interface;

use \Iterator;

/**
 * Interface implemented by all iterators.
 */
interface IteratorInterface extends Iterator
{
    /**
     * Returns the next object to be enumerated by this iterator.
     * Returns NULL when there are not more objects.
     *
     * @return mixed The next object to be enumerated.
     */
    public abstract function succ();
}
