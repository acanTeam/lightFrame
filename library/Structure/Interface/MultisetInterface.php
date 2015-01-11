<?php

namesapce Structure\Interface;

/**
 * Interface implemented by all sets.
 */
interface MultisetInterface extends SearchableContainerInterface
{
    /**
     * Insert the given item into this set.
     *
     * @param integer item The item to insert.
     */
    public abstract function insertItem($item);

    /**
     * Withdraws the given item from this set.
     *
     * @param integer item The item to withdraw.
     */
    public abstract function withdrawItem($item);

    /**
     * ContainsItem predicate.
     *
     * @return boolean True if this set contains the given item.
     */
    public abstract function containsItem($item);

    /**
     * Returns the union of this set and the specified set.
     *
     * @param object MultisetInterface $set The set to be joined with this set.
     * @return object MultisetInterface The union of this set and the specified set.
     */
    public abstract function union(MultisetInterface $set);

    /**
     * Returns the intersection of this set and the specified set.
     *
     * @param object MultisetInterface $set The set to be intersected with this set.
     * @return object MultisetInterface The intersection of this set and the specified set.
     */
    public abstract function intersection(MultisetInterface $set);

    /**
     * Returns the difference between this set and the specified set.
     *
     * @param object MultisetInterface $set The set to subtract from this set.
     * @return object MultisetInterface The difference between this set and the specified set.
     */
    public abstract function difference(MultisetInterface $set);

    /**
     * Tests whether this set is a subset of the specified set.
     *
     * @param object MultisetInterface $set The set to which this set is compared.
     * @return boolean True if this set is a subset of the specified set; false otherwise.
     */
    public abstract function isSubset(MultisetInterface $set);
}
