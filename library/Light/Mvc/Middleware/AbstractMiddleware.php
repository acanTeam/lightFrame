<?php
namespace Light\Mvc\Middleware;

abstract class AbstractMiddleware
{
    /**
     * @var \Light\Mvc\Application Reference to the primary application instance
     */
    protected $application;

    /**
     * @var mixed Reference to the next downstream middleware
     */
    protected $next;

    /**
     * Set application
     *
     * @param  \Light\Light $application
     */
    final public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * Get application
     *
     * This method retrieves the application previously injected
     * into this middleware.
     *
     * @return \Light\Light
     */
    final public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set next middleware
     *
     * This method injects the next downstream middleware into
     * this middleware so that it may optionally be called
     * when appropriate.
     *
     * @param \Light|\Light\Middleware
     */
    final public function setNextMiddleware($nextMiddleware)
    {
        $this->next = $nextMiddleware;
    }

    /**
     * Get next middleware
     *
     * This method retrieves the next downstream middleware
     * previously injected into this middleware.
     *
     * @return \Light\Light|\Light\Middleware
     */
    final public function getNextMiddleware()
    {
        return $this->next;
    }

    /**
     * Call
     *
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     */
    abstract public function call();
}
