<?php

namespace Structure\Interface;

/**
 * Interface implemented by all sorted lists.
 */
interface SortedListInterface extends SearchableContainerInterface, ArrayAccess
{
    /**
     * Returns the position in this list of the given object.
     *
     * @param object ComparableInterface $obj The object to find.
     * @return object ICursor A cursor.
     */
    public abstract function findPosition(ComparableInterface $obj);
}
