<?php

namespace Structure\Interface;

/**
 * Interface implemented by all priority queues.
 */
interface PriorityQueueInterface extends ContainerInterface
{
    /**
     * Enqueues the given object at the tail of this queue.
     *
     * @param object ComparableInterface $obj The object to enqueue.
     */
    public abstract function enqueue(ComparableInterface $obj);

    /**
     * Dequeues and returns the smallest object in this queue.
     *
     * @return object ComparableInterface The smallest object in this queue.
     */
    public abstract function dequeueMin();

    /**
     * Finds and returns the smallest object in this queue.
     *
     * @return object ComparableInterface The smallest object in this queue.
     */
    public abstract function findMin();
}
