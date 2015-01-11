<?php

namesapce Structure\Interface;

/**
 * Interface implemented by all searchable containers.
 */
interface SearchableContainerInterface extends ContainerInterface
{
    /**
     * Returns true if this container contains the given object instance.
     *
     * @param object ComparableInterface $obj The object to find.
     * @return True if this container contains the given object; false otherwise.
     */
    public abstract function contains(ComparableInterface $obj);

    /**
     * Inserts the specified object into this container.
     *
     * @param object ComparableInterface $obj The object to insert.
     */
    public abstract function insert(ComparableInterface $obj);

    /**
     * Withdraws the given object instance from this container.
     *
     * @param object ComparableInterface $obj The object to withdraw.
     */
    public abstract function withdraw(ComparableInterface $obj);

    /**
     * Finds the object in this container that equals the given object.
     *
     * @param object ComparableInterface $obj The object to match.
     * @return mixed The object that equasl the given object.
     */
    public abstract function find(ComparableInterface $obj);
}
