<?php

namespace Structure\Interface;

/**
 * Interface implemented by all deques.
 *
 * @package Opus11
 */
interface DequeInterface extends ContainerInterface, QueueInterface
{
    /**
     * Enqueues the given object at the head of this deque.
     *
     * @param object ObjectInterface $obj The object to enqueue.
     */
    public abstract function enqueueHead(ObjectInterface $obj);

    /**
     * Enqueues the given object at the tail of this deque.
     *
     * @param object ObjectInterface $obj The object to enqueue.
     */
    public abstract function enqueueTail(ObjectInterface $obj);

    /**
     * Dequeues and returns the object at the head of this deque.
     *
     * @return object ObjectInterface The object at the head of this deque.
     */
    public abstract function dequeueHead();

    /**
     * Dequeues and returns the object at the tail of this deque.
     *
     * @return object ObjectInterface The object at the tail of this deque.
     */
    public abstract function dequeueTail();

    /**
     * Tail getter.
     *
     * @return object ObjectInterface The object at the tail of this deque.
     */
    public abstract function getTail();
}
