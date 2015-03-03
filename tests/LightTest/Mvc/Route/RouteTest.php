<?php

use Light\Mvc\Route\Route;

class LazyInitializeTestClass
{
    public static $initialized = false;

    public function __construct()
    {
        self::$initialized = true;
    }

    public function foo()
    {
    }
}

class FooTestClass
{
    public static $foo_invoked = false;
    public static $foo_invoked_args = array();

    public function foo()
    {
        self::$foo_invoked = true;
        self::$foo_invoked_args = func_get_args();
    }
}

class RouteTest extends PHPUnit_Framework_TestCase
{
    public function testGetPattern()
    {
        $route = new Route('/foo', function () {});

        $this->assertEquals('/foo', $route->getPattern());
    }

    public function testGetName()
    {
        $route = new Route('/foo', function () {});

        $property = new \ReflectionProperty($route, 'name');
        $property->setAccessible(true);
        $property->setValue($route, 'foo');

        $this->assertEquals('foo', $route->getName());
    }

    public function testSetName()
    {
        $route = new Route('/foo', function () {});
        $route->setName('foo'); 

        $this->assertAttributeEquals('foo', 'name', $route);
    }

    public function testGetCallable()
    {
        $callable = function () {
            echo 'Foo';
        };
        $route = new Route('/foo', $callable);

        $this->assertSame($callable, $route->getCallable());
    }

    public function testGetCallableAsClass()
    {
        FooTestClass::$foo_invoked = false;
        FooTestClass::$foo_invoked_args = array();
        $route = new Route('/foo', '\FooTestClass:foo');
        $route->setParams(array('bar' => '1234'));

        $this->assertFalse(FooTestClass::$foo_invoked);
        $this->assertTrue($route->dispatch());
        $this->assertTrue(FooTestClass::$foo_invoked);
        $this->assertEquals(array('1234'), FooTestClass::$foo_invoked_args);
    }

    public function testGetCallableAsClassLazyInitialize()
    {
        LazyInitializeTestClass::$initialized = false;

        $route = new Route('/foo', '\LazyInitializeTestClass:foo');
        $this->assertFalse(LazyInitializeTestClass::$initialized);

        $route->dispatch();
        $this->assertTrue(LazyInitializeTestClass::$initialized);
    }

    public function testGetCallableAsStaticMethod()
    {
        $route = new Route('/bar', '\Light\Mvc\Application::getInstance');

        $callable = $route->getCallable();
        $this->assertEquals('\Light\Mvc\Application::getInstance', $callable);
    }

    public function example_càllâble_wïth_wéird_chars()
    {
        return 'test';
    }

    public function testGetCallableWithOddCharsAsClass()
    {
        $route = new Route('/foo', '\RouteTest:example_càllâble_wïth_wéird_chars');
        $callable = $route->getCallable();

        $this->assertEquals('test', $callable());
    }

    public function testSetCallable()
    {
        $callable = function () {
            echo 'Foo';
        };
        $route = new Route('/foo', $callable); 

        $this->assertAttributeSame($callable, 'callable', $route);
    }

    public function testSetCallableWithInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $route = new Route('/foo', 'doesNotExist');
    }

    public function testGetParams()
    {
        $route = new Route('/hello/:first/:last/', function () {});
        $route->matches('/hello/mr/anderson');
        //var_dump($route->getParams());$route->setCallable('\Light\Mvc\Application::getInstance');var_dump($route);exit();
        $array = array('first' => 'mr', 'last' => 'anderson');
        $this->assertEquals($array, $route->getParams());
    }

    public function testSetParams()
    {
        $route = new Route('/foo', function () {});

        $array = array(
            'first' => 'agent',
            'last' => 'smith',
        );
        $route->setParams($array);
        $this->assertAttributeEquals($array, 'params', $route);
    }

    public function testGetParam()
    {
        $route = new Route('/foo', function () {});

        $property = new \ReflectionProperty($route, 'params');
        $property->setAccessible(true);

        $array = array(
            'first' => 'foo',
            'last' => 'bar',
        );
        $property->setValue($route, $array);
        $this->assertEquals('foo', $route->getParam('first'));
    }

    public function testGetParamThatDoesNotExist()
    {
        $this->setExpectedException('InvalidArgumentException');

        $route = new Route('/foo', function () {});

        $property = new \ReflectionProperty($route, 'params');
        $property->setAccessible(true);

        $array = array(
            'first' => 'foo',
            'last' => 'bar',
        );
        $property->setValue($route, $array);
        $route->getParam('middle');
    }

    public function testSetParam()
    {
        $route = new Route('/hello/:first/:last', function () {});
        $route->matches('/hello/mr/anderson');
        $route->setParam('last', 'smith');

        $array = array('first' => 'mr', 'last' => 'smith');
        $this->assertAttributeEquals($array, 'params', $route);
    }

    public function testSetParamThatDoesNotExist()
    {
        $this->setExpectedException('InvalidArgumentException');

        $route = new Route('/hello/:first/:last', function () {});
        $route->matches('/hello/mr/anderson');
        $route->setParam('middle', 'smith');
    }

    public function testMatches()
    {
        $route = new Route('/hello/:name', function () {});

        $this->assertTrue($route->matches('/hello/josh'));
    }

    public function testMatchesIsFalse()
    {
        $route = new Route('/foo', function () {});

        $this->assertFalse($route->matches('/bar'));
    }

    public function testMatchesPatternWithTrailingSlash()
    {
        $route = new Route('/foo/', function () {});

        $this->assertTrue($route->matches('/foo/'));
        $this->assertTrue($route->matches('/foo'));
    }

    public function testMatchesPatternWithoutTrailingSlash()
    {
        $route = new Route('/foo', function () {});

        $this->assertFalse($route->matches('/foo/'));
        $this->assertTrue($route->matches('/foo'));
    }

    public function testMatchesWithConditions()
    {
        $route = new Route('/hello/:first/and/:second', function () {});
        $route->conditions(array(
            'first' => '[a-zA-Z]{3,}'
        ));

        $this->assertTrue($route->matches('/hello/Josh/and/John'));
        $this->assertFalse($route->matches('/hello/Jo/and/John'));
    }

    public function testMatchesWithConditionsIsFalse()
    {
        $route = new Route('/hello/:first/and/:second', function () {});
        $route->conditions(array(
            'first' => '[a-z]{3,}'
        ));

        $this->assertFalse($route->matches('/hello/Josh/and/John'));
    }

    /*
     * Route should match URI with valid path component according to rfc2396
     *
     * "Uniform Resource Identifiers (URI): Generic Syntax" http://www.ietf.org/rfc/rfc2396.txt
     *
     * Excludes "+" which is valid but decodes into a space character
     */
    public function testMatchesWithValidRfc2396PathComponent()
    {
        $symbols = ':@+&=$,';
        $route = new Route('/rfc2386/:symbols', function () {});

        $this->assertTrue($route->matches('/rfc2386/' . $symbols));
    }

    /*
     * Route should match URI including unreserved punctuation marks from rfc2396
     *
     * "Uniform Resource Identifiers (URI): Generic Syntax" http://www.ietf.org/rfc/rfc2396.txt
     */
    public function testMatchesWithUnreservedMarks()
    {
        $marks = "-_.!~*'()";
        $route = new Route('/marks/:marks', function () {});

        $this->assertTrue($route->matches('/marks/' . $marks));
    }

    public function testMatchesOptionalParameters()
    {
        $pattern = '/archive/:year(/:month(/:day))';

        $route1 = new Route($pattern, function () {});
        $this->assertTrue($route1->matches('/archive/2010'));
        $this->assertEquals(array('year' => '2010'), $route1->getParams());

        $route2 = new Route($pattern, function () {});
        $this->assertTrue($route2->matches('/archive/2010/05'));
        $this->assertEquals(array('year' => '2010', 'month' => '05'), $route2->getParams());

        $route3 = new Route($pattern, function () {});
        $this->assertTrue($route3->matches('/archive/2010/05/13'));
        $this->assertEquals(array('year' => '2010', 'month' => '05', 'day' => '13'), $route3->getParams());
    }

    public function testMatchesIsCaseSensitiveByDefault()
    {
        $route = new Route('/case/sensitive', function () {});
        $this->assertTrue($route->matches('/case/sensitive'));
        $this->assertFalse($route->matches('/CaSe/SensItiVe'));
    }

    public function testMatchesCanBeCaseInsensitive()
    {
        $route = new Route('/Case/Insensitive', function () {}, false);
        $this->assertTrue($route->matches('/Case/Insensitive'));
        $this->assertTrue($route->matches('/CaSe/iNSensItiVe'));
    }

    public function testGetConditions()
    {
        $route = new Route('/foo', function () {});

        $property = new \ReflectionProperty($route, 'conditions');
        $property->setAccessible(true);
        $property->setValue($route, array('foo' => '\d{3}'));

        $this->assertEquals(array('foo' => '\d{3}'), $route->getConditions());
    }

    public function testSetDefaultConditions()
    {
        $defaultConditions = array(
            'id' => '\d+'
        );
        Route::setDefaultConditions($defaultConditions);

        $property = new \ReflectionProperty('\Light\Mvc\Route\Route', 'defaultConditions');
        $property->setAccessible(true);

        $this->assertEquals($defaultConditions, $property->getValue());
    }

    public function testGetDefaultConditions()
    {
        $conditions = array(
            'id' => '\d+'
        );
        $property = new \ReflectionProperty('\Light\Mvc\Route\Route', 'defaultConditions');
        $property->setAccessible(true);
        $property->setValue($conditions);

        $this->assertEquals($conditions, Route::getDefaultConditions());
    }

    public function testDefaultConditionsAssignedToInstance()
    {
        $conditions = array(
            'id' => '\d+'
        );
        $staticProperty = new \ReflectionProperty('\Light\Mvc\Route\Route', 'defaultConditions');
        $staticProperty->setAccessible(true);
        $staticProperty->setValue($conditions);
        $route = new Route('/foo', function () {});

        $this->assertAttributeEquals($conditions, 'conditions', $route);
    }

    public function testMatchesWildcard()
    {
        $route = new Route('/hello/:path+/world', function () {});

        $this->assertTrue($route->matches('/hello/foo/bar/world'));
        $this->assertAttributeEquals(array(
            'path' => array('foo', 'bar')
        ), 'params', $route);
    }

    public function testMatchesMultipleWildcards()
    {
        $route = new Route('/hello/:path+/world/:date+', function () {});

        $this->assertTrue($route->matches('/hello/foo/bar/world/2012/03/10'));
        $this->assertAttributeEquals(array(
            'path' => array('foo', 'bar'),
            'date' => array('2012', '03', '10')
        ), 'params', $route);
    }

    public function testMatchesParamsAndWildcards()
    {
        $route = new Route('/hello/:path+/world/:year/:month/:day/:path2+', function () {});

        $this->assertTrue($route->matches('/hello/foo/bar/world/2012/03/10/first/second'));
        $this->assertAttributeEquals(array(
            'path' => array('foo', 'bar'),
            'year' => '2012',
            'month' => '03',
            'day' => '10',
            'path2' => array('first', 'second')
        ), 'params', $route);
    }

    public function testMatchesParamsWithOptionalWildcard()
    {
        $route = new Route('/hello(/:foo(/:bar+))', function () {});

        $this->assertTrue($route->matches('/hello'));
        $this->assertTrue($route->matches('/hello/world'));
        $this->assertTrue($route->matches('/hello/world/foo'));
        $this->assertTrue($route->matches('/hello/world/foo/bar'));
    }

    public function testSetMiddleware()
    {
        $route = new Route('/foo', function () {});
        $mw = function () {
            echo 'Foo';
        };
        $route->setMiddleware($mw);

        $this->assertAttributeContains($mw, 'middleware', $route);
    }

    public function testSetMiddlewareMultipleTimes()
    {
        $route = new Route('/foo', function () {});
        $mw1 = function () {
            echo 'Foo';
        };
        $mw2 = function () {
            echo 'Bar';
        };
        $route->setMiddleware($mw1);
        $route->setMiddleware($mw2);

        $this->assertAttributeContains($mw1, 'middleware', $route);
        $this->assertAttributeContains($mw2, 'middleware', $route);
    }

    public function testSetMiddlewareWithArray()
    {
        $route = new Route('/foo', function () {});
        $mw1 = function () {
            echo 'Foo';
        };
        $mw2 = function () {
            echo 'Bar';
        };
        $route->setMiddleware(array($mw1, $mw2));

        $this->assertAttributeContains($mw1, 'middleware', $route);
        $this->assertAttributeContains($mw2, 'middleware', $route);
    }

    public function testSetMiddlewareWithInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException');

        $route = new Route('/foo', function () {});
        $route->setMiddleware('doesNotExist');
    }

    public function testSetMiddlewareWithArrayWithInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException');

        $route = new Route('/foo', function () {});
        $route->setMiddleware(array('doesNotExist'));
    }

    public function testGetMiddleware()
    {
        $route = new Route('/foo', function () {});

        $property = new \ReflectionProperty($route, 'middleware');
        $property->setAccessible(true);
        $property->setValue($route, array('foo' => 'bar'));

        $this->assertEquals(array('foo' => 'bar'), $route->getMiddleware());
    }

    public function testSetHttpMethods()
    {
        $route = new Route('/foo', function () {});
        $route->setHttpMethods('GET', 'POST');

        $this->assertAttributeEquals(array('GET', 'POST'), 'methods', $route);
    }

    public function testGetHttpMethods()
    {
        $route = new Route('/foo', function () {});

        $property = new \ReflectionProperty($route, 'methods');
        $property->setAccessible(true);
        $property->setValue($route, array('GET', 'POST'));

        $this->assertEquals(array('GET', 'POST'), $route->getHttpMethods());
    }

    public function testAppendHttpMethods()
    {
        $route = new Route('/foo', function () {});

        $property = new \ReflectionProperty($route, 'methods');
        $property->setAccessible(true);
        $property->setValue($route, array('GET', 'POST'));

        $route->appendHttpMethods('PUT');

        $this->assertAttributeEquals(array('GET', 'POST', 'PUT'), 'methods', $route);
    }

    public function testAppendHttpMethodsWithVia()
    {
        $route = new Route('/foo', function () {});
        $route->via('PUT');

        $this->assertAttributeContains('PUT', 'methods', $route);
    }

    public function testSupportsHttpMethod()
    {
        $route = new Route('/foo', function () {});

        $property = new \ReflectionProperty($route, 'methods');
        $property->setAccessible(true);
        $property->setValue($route, array('POST'));

        $this->assertTrue($route->supportsHttpMethod('POST'));
        $this->assertFalse($route->supportsHttpMethod('PUT'));
    }

    /**
     * Test dispatch with params
     */
    public function testDispatch()
    {
        $this->expectOutputString('Hello josh');
        $route = new Route('/hello/:name', function ($name) { echo "Hello $name"; });
        $route->matches('/hello/josh');
        $route->dispatch();
    }

    /**
     * Test dispatch with middleware
     */
    public function testDispatchWithMiddleware()
    {
        $this->expectOutputString('First! Second! Hello josh');
        $route = new Route('/hello/:name', function ($name) { echo "Hello $name"; });
        $route->setMiddleware(function () {
            echo "First! ";
        });
        $route->setMiddleware(function () {
            echo "Second! ";
        });
        $route->matches('/hello/josh'); //<-- Extracts params from resource URI
        $route->dispatch();
    }

    /**
     * Test middleware with arguments
     */
    public function testRouteMiddlwareArguments()
    {
        $this->expectOutputString('foobar');
        $route = new Route('/foo', function () { echo "bar"; });
        $route->setName('foo');
        $route->setMiddleware(function ($route) {
            echo $route->getName();
        });
        $route->matches('/foo');
        $route->dispatch();
    }
}
