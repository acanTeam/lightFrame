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
        $this->application->domain = 'http://www.acanstudio' . $this->application->configCommon['topDomain'] . '/';

        $this->modulePath = $this->application->container['configs']['modulePath'][$this->currentModule];
        $this->time = time();
        $this->ip = $this->_getIp();

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
        $navbarContent = $this->application->view->fetch($navbarType, array('navbarInfos' => $navbarInfos, 'application' => $this->application));

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
        $link = @mysql_connect($dbHost, $dbUsername, $dbPassword); 
        @mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary", $link);

        return $link;
    }

    /**
     * Create a random string
     *
     * @param  string $length the length of the string
     * @return string random string
     */
    protected function _getRandomStr($length = 6, $chars = '23456789abcdefghijklmnpqrstuvwxyABCDEFGHJKLMNPQRSTUVWXY')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }


    protected function _getIp()
    { 
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) $ip = getenv("HTTP_CLIENT_IP"); 
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR"); 
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) $ip = getenv("REMOTE_ADDR"); 
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) $ip = $_SERVER['REMOTE_ADDR']; 
        else $ip = "unknown"; 
        return ($ip); 
    } 
    
    protected function _messageInfo($message, $urlForward = '', $sleepTime = 3000)
    {

        $data = array(
            'message' => $message,
            'application' => $this->application,
            'urlForward' => $urlForward,
            'sleepTime' => $sleepTime,
        );
        $this->application->layout('common/message', 'common/layout', $data);
        exit();
    }

    protected function _getNavbar()
    {
        $navbarInfos = array(
            'blog' => array('name' => '博客', 'url' => 'http://blog.acanstudio.com/'),
            'docs' => array('name' => '文档系统', 'url' => $this->application->domain . 'document'),
            'bootstrap' => array('name' => 'Bootstrap', 'url' => $this->application->domain . 'bootstrap/demo'),
            //'php' => array('name' => 'PHP进阶', 'url' => $this->application->domain . 'codelib/phuml'),
            'phptool' => array('name' => 'PHP小工具', 'url' => $this->application->domain . 'codelib/tool'),
            'about' => array('name' => 'About', 'url' => 'http://blog.acanstudio.com/about'),
        );
        return $navbarInfos;
    }    
}
