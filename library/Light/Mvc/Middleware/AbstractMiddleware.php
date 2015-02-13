<?php
namespace Light\Mvc\Middleware;

use Light\Mvc\Application;

abstract class AbstractMiddleware
{
    /**
     * @var Application Reference to the primary application instance
     */
    protected $application;

    /**
     * @var mixed Reference to the next downstream middleware
     */
    protected $next;

    /**
     * Set application
     *
     * @param  Application $application
     */
    final public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * Get application
     *
     * @return Application
     */
    final public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set next middleware
     *
     * @return Application | AbstractMiddleware
     */
    final public function setNextMiddleware($nextMiddleware)
    {
        $this->next = $nextMiddleware;
    }

    /**
     * Get next middleware
     *
     * @return Application | AbstractMiddleware
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
