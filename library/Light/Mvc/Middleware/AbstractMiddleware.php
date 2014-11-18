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
     * @param  \Light\Mvc\Application $application
     */
    final public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * Get application
     *
     * @return \Light\Mvc\Application
     */
    final public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set next middleware
     *
     * @return \Light\Mvc\Application | \Light\Mvc\Middleware\AbstractMiddleware
     */
    final public function setNextMiddleware($nextMiddleware)
    {
        $this->next = $nextMiddleware;
    }

    /**
     * Get next middleware
     *
     * @return \Light\Mvc\Application | \Light\Mvc\Middleware\AbstractMiddleware
     */
    final public function getNextMiddleware()
    {
        return $this->next;
    }

    /**
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     */
    abstract public function call();
}
