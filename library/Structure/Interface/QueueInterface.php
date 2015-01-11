<?php

namespace Structure\Interface;

/**
 * Interface implemented by all queues.
 *
 * @package Opus11
 */
interface QueueInterface extends ContainerInterface
{
    /**
     * Enqueues the given object at the tail of this queue.
     *
     * @param object ObjectInterface $obj The object to enqueue.
     */
    public abstract function enqueue(ObjectInterface $obj);

    /**
     * Dequeues and returns the object at the head of this queue.
     *
     * @return object ObjectInterface The object at the head of this queue.
     */
    public abstract function dequeue();

    /**
     * Head getter.
     *
     * @return object ObjectInterface The object at the head of this queue.
     */
    public abstract function getHead();
}
