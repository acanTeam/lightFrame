<?php

use Light\Mvc\Router;
use Light\Mvc\Route\Route;

class RouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Constructor should initialize routes as empty array
     */
    public function testConstruct()
    {
        $router = new Router();

        $this->assertAttributeEquals(array(), 'routes', $router);
    }

    /**
     * Map should set and return instance of Route
     */
    public function testMap()
    {
        $router = new Router();
        $route = new Route('/foo', '\Light\Mvc\Application::getInstance');
        $router->map($route);

        $this->assertAttributeContains($route, 'routes', $router);
    }

    /**
     * Named route should be added and indexed by name
     */
    public function testAddNamedRoute()
    {
        $router = new Router();
        $route = new Route('/foo', '\Light\Mvc\Application::getInstance');
        $router->addNamedRoute('foo', $route);

        $property = new \ReflectionProperty($router, 'namedRoutes');
        $property->setAccessible(true);

		$namedRoutes = $property->getValue($router);
        $this->assertSame($route, $namedRoutes['foo']);
    }

    /**
     * Named route should have unique name
     */
    public function testAddNamedRouteWithDuplicateKey()
    {
        $this->setExpectedException('RuntimeException');

        $router = new Router();
        $route = new Route('/foo', '\Light\Mvc\Application::getInstance');
        $router->addNamedRoute('foo', $route);
        $router->addNamedRoute('foo', $route);
    }

    /**
     * Router should return named route by name, or null if not found
     */
    public function testGetNamedRoute()
    {
        $router = new Router();
        $route = new Route('/foo', '\Light\Mvc\Application::getInstance');

        $property = new \ReflectionProperty($router, 'namedRoutes');
        $property->setAccessible(true);
        $property->setValue($router, array('foo' => $route));

        $this->assertSame($route, $router->getNamedRoute('foo'));
        $this->assertNull($router->getNamedRoute('bar'));
    }

    /**
     * Router should determine named routes and cache results
     */
    public function testGetNamedRoutes()
    {
        $router = new Router();
        $route1 = new Route('/foo', '\Light\Mvc\Application::getInstance');
        $route2 = new Route('/bar', '\Light\Mvc\Application::getInstance');

        // Init router routes to array
        $propertyRouterRoutes = new \ReflectionProperty($router, 'routes');
        $propertyRouterRoutes->setAccessible(true);
        $propertyRouterRoutes->setValue($router, array($route1, $route2));

        // Init router named routes to null
        $propertyRouterNamedRoutes = new \ReflectionProperty($router, 'namedRoutes');
        $propertyRouterNamedRoutes->setAccessible(true);
        $propertyRouterNamedRoutes->setValue($router, null);

        // Init route name
        $propertyRouteName = new \ReflectionProperty($route2, 'name');
        $propertyRouteName->setAccessible(true);
        $propertyRouteName->setValue($route2, 'bar');

        $namedRoutes = $router->getNamedRoutes();
        $this->assertCount(1, $namedRoutes);
        $this->assertSame($route2, $namedRoutes['bar']);
    }

    /**
     * Router should detect presence of a named route by name
     */
    public function testHasNamedRoute()
    {
        $router = new Router();
        $route = new Route('/foo', '\Light\Mvc\Application::getInstance');

        $property = new \ReflectionProperty($router, 'namedRoutes');
        $property->setAccessible(true);
        $property->setValue($router, array('foo' => $route));

        $this->assertTrue($router->hasNamedRoute('foo'));
        $this->assertFalse($router->hasNamedRoute('bar'));
    }

    /**
     * Router should return current route if set during iteration
     */
    public function testGetCurrentRoute()
    {
        $router = new Router();
        $route = new Route('/foo', '\Light\Mvc\Application::getInstance');

        $property = new \ReflectionProperty($router, 'currentRoute');
        $property->setAccessible(true);
        $property->setValue($router, $route);

        $this->assertSame($route, $router->getCurrentRoute());
    }

    /**
     * Router should return first matching route if current route not set yet by iteration
     */
    public function testGetCurrentRouteIfMatchedRoutes()
    {
        $router = new Router();
        $route = new Route('/foo', '\Light\Mvc\Application::getInstance');

        $propertyMatchedRoutes = new \ReflectionProperty($router, 'matchedRoutes');
        $propertyMatchedRoutes->setAccessible(true);
        $propertyMatchedRoutes->setValue($router, array($route));

        $propertyCurrentRoute = new \ReflectionProperty($router, 'currentRoute');
        $propertyCurrentRoute->setAccessible(true);
        $propertyCurrentRoute->setValue($router, null);

        $this->assertSame($route, $router->getCurrentRoute());
    }

    /**
     * Router should return `null` if current route not set yet and there are no matching routes
     */
    public function testGetCurrentRouteIfNoMatchedRoutes()
    {
        $router = new Router();

        $propertyMatchedRoutes = new \ReflectionProperty($router, 'matchedRoutes');
        $propertyMatchedRoutes->setAccessible(true);
        $propertyMatchedRoutes->setValue($router, array());

        $propertyCurrentRoute = new \ReflectionProperty($router, 'currentRoute');
        $propertyCurrentRoute->setAccessible(true);
        $propertyCurrentRoute->setValue($router, null);

        $this->assertNull($router->getCurrentRoute());
    }

    public function testGetMatchedRoutes()
    {
        $router = new Router();

        $route1 = new Route('/foo', '\Light\Mvc\Application::getInstance');
		$route1 = $route1->via('GET');

        $route2 = new Route('/foo', '\Light\Mvc\Application::getInstance');
		$route2 = $route2->via('POST');

        $route3 = new Route('/bar', '\Light\Mvc\Application::getInstance');
		$route3 = $route3->via('PUT');

        $routes = new \ReflectionProperty($router, 'routes');
        $routes->setAccessible(true);
        $routes->setValue($router, array($route1, $route2, $route3));

        $matchedRoutes = $router->getMatchedRoutes('GET', '/foo');
        $this->assertSame($route1, $matchedRoutes[0]);
    }

    // Test url for named route

    public function testUrlFor()
    {
        $router = new Router();

        $route1 = new Route('/hello/:first/:last', '\Light\Mvc\Application::getInstance');
        $route1 = $route1->via('GET')->setName('hello');

        $route2 = new Route('/path/(:foo\.:bar)', '\Light\Mvc\Application::getInstance');
        $route2 = $route2->via('GET')->setName('regexRoute');

        $routes = new \ReflectionProperty($router, 'namedRoutes');
        $routes->setAccessible(true);
        $namedRoutes = array(
            'hello' => $route1,
            'regexRoute' => $route2
        );
        $routes->setValue($router, $namedRoutes);

        $this->assertEquals('/hello/Josh/Lockhart', $router->urlFor('hello', array('first' => 'Josh', 'last' => 'Lockhart')));
        $this->assertEquals('/path/Hello.Josh', $router->urlFor('regexRoute', array('foo' => 'Hello', 'bar' => 'Josh')));
    }

    public function testUrlForIfNoSuchRoute()
    {
        $this->setExpectedException('RuntimeException');

        $router = new Router();
        $router->urlFor('foo', array('abc' => '123'));
    }
}
