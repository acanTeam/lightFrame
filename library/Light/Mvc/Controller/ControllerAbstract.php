<?php
namespace Light\Mvc\Controller;

use \Light\Mvc\Application as Application;

class ControllerAbstract 
{
    /**
     * @var array
     */
    protected $application;

    /**
     * Constructor (private access)
     *
     * @param array | null $configs If present, these are used instead of global server variables
     */
    public function __construct()
    {
        $this->application = Application::getInstance();
    }
}
