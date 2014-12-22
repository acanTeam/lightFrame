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

        $this->modulePath = $this->application->container['configs']['modulePath'][$this->currentModel];

    }

    /**
     * Get the navbar content for pointing navbarInfos
     *
     * @param array $navbarInfos The navbar infos 
     * @param string $navbarType The type of navbar template
     * @return string The html content of navbar
     */
    protected function _getNavbarContent($navbarInfos, $navbarType = 'common/navbar')
    {
        $navbarContent = $this->application->view->fetch($navbarType, array('navbarInfos' => $navbarInfos));

        return $navbarContent;
    }

    /**
     * Connect the database
     *
     * @return resource | null
     */
    protected function _connectDb()
    {
        $dbHost = $this->application->configCommon['dbHost'];
        $dbUsername = $this->application->configCommon['dbUsername'];
        $dbPassword = $this->application->configCommon['dbPassword'];
        $link = mysql_connect($dbHost, $dbUsername, $dbPassword); 
        mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary", $link);

        return $link;
    }
}
