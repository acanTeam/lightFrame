<?php

namespace Structure\Interface;

/**
 * Interface implemented by all pre/post visitors.
 */
interface PrePostVisitorInterface
{
    /**
     * "Pre"-visits the given object.
     *
     * @param object ObjectInterface $obj The object to visit.
     */
    public abstract function preVisit(ObjectInterface $obj);

    /**
     * "In"-visits the given object.
     *
     * @param object ObjectInterface $obj The object to visit.
     */
    public abstract function inVisit(ObjectInterface $obj);

    /**
     * "Post"-visits the given object.
     *
     * @param object ObjectInterface $obj The object to visit.
     */
    public abstract function postVisit(ObjectInterface $obj);

    /**
     * Done predicate.
     *
     * @return boolean True if this visitor is done.
     */
    public abstract function isDone();
}
