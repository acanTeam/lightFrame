<?php

namespace Structure\Interface;

/**
 * Interface implemented by all cursors.
 */
interface CursorInterface extends IteratorInterface
{
    /**
     * Inserts the given object after the position of this cursor.
     *
     * @param object ObjectInterface obj The object to insert.
     */
    public abstract function insertAfter(ObjectInterface $obj);

    /**
     * Inserts the given object before the position of this cursor.
     *
     * @param object ObjectInterface $obj The object to insert.
     */
    public abstract function insertBefore(ObjectInterface $obj);

    /**
     * Withdraws the object at the position of this cursor.
     */
    public abstract function withdraw();
}
