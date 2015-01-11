<?php

namespace Structure\Interface;

/**
 * Interface implemented by all hash tables.
 */
interface HashTableInterface extends SearchableContainerInterface
{
    /**
     * Load factor getter.
     *
     * @return float The current load factor.
     */
    public abstract function getLoadFactor();
}
