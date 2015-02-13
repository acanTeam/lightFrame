<?php

use Light\Mvc\Middleware\AbstractMiddleware;

class MyMiddleware extends AbstractMiddleware
{
    public function call() {}
}

class MiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function testSetApplication()
    {
        $application = new stdClass();
        $myMiddleware = new MyMiddleware();
        $myMiddleware->setApplication($application);

        $this->assertAttributeSame($application, 'application', $myMiddleware);
    }

    public function testGetApplication()
    {
        $application = new stdClass();
        $myMiddleware = new MyMiddleware();
        $property = new \ReflectionProperty($myMiddleware, 'application');
        $property->setAccessible(true);
        $property->setValue($myMiddleware, $application);

        $this->assertSame($application, $myMiddleware->getApplication());
    }

    public function testSetNextMiddleware()
    {
        $myMiddleware1 = new MyMiddleware();
        $myMiddleware2 = new MyMiddleware();
        $myMiddleware1->setNextMiddleware($myMiddleware2);

        $this->assertAttributeSame($myMiddleware2, 'next', $myMiddleware1);
    }

    public function testGetNextMiddleware()
    {
        $myMiddleware1 = new MyMiddleware();
        $myMiddleware2 = new MyMiddleware();
        $property = new \ReflectionProperty($myMiddleware1, 'next');
        $property->setAccessible(true);
        $property->setValue($myMiddleware1, $myMiddleware2);

        $this->assertSame($myMiddleware2, $myMiddleware1->getNextMiddleware());
    }
}
