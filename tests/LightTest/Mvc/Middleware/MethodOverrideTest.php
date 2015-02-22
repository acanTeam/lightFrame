<?php

use Light\Mvc\Environment;
use Light\Mvc\Middleware\MethodOverride;

class CustomAppMethod
{
    protected $environment;

    public function __construct()
    {
        $this->environment = Environment::getInstance();
    }

    public function &environment() {
        return $this->environment;
    }

    public function call()
    {
        //Do nothing
    }
}

class MethodOverrideTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test overrides method as POST
     */
    public function testOverrideMethodAsPost()
    {
        Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 11,
            'light.input' => '_METHOD=PUT'
        ));
        $app = new CustomAppMethod();
        $mw = new MethodOverride();
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();
        $env =& $app->environment();
        $this->assertEquals('PUT', $env['REQUEST_METHOD']);
        $this->assertTrue(isset($env['light.method_override.original_method']));
        $this->assertEquals('POST', $env['light.method_override.original_method']);
    }

    /**
     * Test does not override method if not POST
     */
    public function testDoesNotOverrideMethodIfNotPost()
    {
        Environment::mock(array(
            'REQUEST_METHOD' => 'GET',
            'light.input' => ''
        ));
        $app = new CustomAppMethod();
        $mw = new MethodOverride();
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();
        $env =& $app->environment();
        $this->assertEquals('GET', $env['REQUEST_METHOD']);
        $this->assertFalse(isset($env['light.method_override.original_method']));
    }

    /**
     * Test does not override method if no method override parameter
     */
    public function testDoesNotOverrideMethodAsPostWithoutParameter()
    {
        Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'REMOTE_ADDR' => '127.0.0.1',
            'SCRIPT_NAME' => '/foo/index.php', //<-- Physical
            'PATH_INFO' => '/bar', //<-- Virtual
            'QUERY_STRING' => 'foo=bar',
            'SERVER_NAME' => 'light',
            'SERVER_PORT' => 80,
            'light.url_scheme' => 'http',
            'light.input' => '',
            'light.errors' => fopen('php://stderr', 'w')
        ));
        $app = new CustomAppMethod();
        $mw = new MethodOverride();
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();
        $env =& $app->environment();
        $this->assertEquals('POST', $env['REQUEST_METHOD']);
        $this->assertFalse(isset($env['light.method_override.original_method']));
    }

    /**
     * Test overrides method with X-Http-Method-Override header
     */
    public function testOverrideMethodAsHeader()
    {
        Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/json',
            'CONTENT_LENGTH' => 0,
            'light.input' => '',
            'HTTP_X_HTTP_METHOD_OVERRIDE' => 'DELETE'
        ));
        $app = new CustomAppMethod();
        $mw = new MethodOverride();
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();
        $env =& $app->environment();
        $this->assertEquals('DELETE', $env['REQUEST_METHOD']);
        $this->assertTrue(isset($env['light.method_override.original_method']));
        $this->assertEquals('POST', $env['light.method_override.original_method']);
    }
}
