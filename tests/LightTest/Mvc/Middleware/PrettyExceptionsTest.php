<?php

use Light\Mvc\Environment;
use Light\Mvc\Application;
use Light\Mvc\Middleware\PrettyExceptions;

class PrettyExceptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test middleware returns successful response unchanged
     */
    public function testReturnsUnchangedSuccessResponse()
    {
        Environment::mock(array(
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/foo'
        ));
        $app = new Application();
        $app->get('/foo', function () {
            echo "Success";
        });
        $mw = new PrettyExceptions();
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();
        $this->assertEquals(200, $app->response()->getStatus());
        $this->assertEquals('Success', $app->response()->body());
    }

    /**
     * Test middleware returns diagnostic screen for error response
     */
    public function testReturnsDiagnosticsForErrorResponse()
    {
        Environment::mock(array(
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/foo'
        ));
        $app = new Application(array(
            'logger.enabled' => false
        ));
        $app->get('/foo', function () {
            throw new \Exception('Test Message', 100);
        });
        $mw = new PrettyExceptions();
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();
        $this->assertEquals(1, preg_match('@Light Application Error@', $app->response()->body()));
        $this->assertEquals(500, $app->response()->getStatus());
    }

    /**
     * Test middleware overrides response content type to html
     */
    public function testResponseContentTypeIsOverriddenToHtml()
    {
        Environment::mock(array(
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/foo'
        ));
        $app = new Application(array(
            'logger.enabled' => false
        ));
        $app->get('/foo', function () use ($app) {
            $app->contentType('application/json;charset=utf-8'); //<-- set content type to something else
            throw new \Exception('Test Message', 100);
        });
        $mw = new PrettyExceptions();
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();
        $response = $app->response();
        $this->assertEquals('text/html', $response['Content-Type']);
    }

    /**
     * Test exception type is in response body
     */
    public function testExceptionTypeIsInResponseBody()
    {
        Environment::mock(array(
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/foo'
        ));
        $app = new Application(array(
            'logger.enabled' => false
        ));
        $app->get('/foo', function () use ($app) {
            throw new \LogicException('Test Message', 100);
        });
        $mw = new PrettyExceptions();
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();

        $this->assertContains('LogicException', $app->response()->body());
    }

    /**
     * Test with custom log
     */
    public function testWithCustomLogWriter()
    {
        $this->setExpectedException('\LogicException');

        Environment::mock(array(
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/foo'
        ));
        $app = new Application(array(
            'logger.enabled' => false
        ));
        $app->container->singleton('logger', function () use ($app) {
            return new \Light\Logger\Logger(new \Light\Logger\Writer('php://temp'));
        });
        $app->get('/foo', function () use ($app) {
            throw new \LogicException('Test Message', 100);
        });
        $mw = new PrettyExceptions();
        $mw->setApplication($app);
        $mw->setNextMiddleware($app);
        $mw->call();

        $this->assertContains('LogicException', $app->response()->body());
    }
}
