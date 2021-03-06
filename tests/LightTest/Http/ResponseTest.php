<?php

use Light\Http\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }
    
    public function testConstructWithoutArgs()
    {
        $res = new Response();

        $this->assertAttributeEquals(200, 'status', $res);
        $this->assertAttributeEquals('', 'body', $res);
    }

    public function testConstructWithArgs()
    {
        $res = new Response('Foo', 201);

        $this->assertAttributeEquals(201, 'status', $res);
        $this->assertAttributeEquals('Foo', 'body', $res);
    }

    public function testGetStatus()
    {
        $res = new Response();

        $this->assertEquals(200, $res->getStatus());
    }

    public function testSetStatus()
    {
        $res = new Response();
        $res->setStatus(301);

        $this->assertAttributeEquals(301, 'status', $res);
    }

    public function testGetBody()
    {
        $res = new Response();
        $property = new \ReflectionProperty($res, 'body');
        $property->setAccessible(true);
        $property->setValue($res, 'foo');

        $this->assertEquals('foo', $res->getBody());
    }

    public function testSetBody()
    {
        $res = new Response('bar');
        $res->setBody('foo'); // <-- Should replace body

        $this->assertAttributeEquals('foo', 'body', $res);
    }

    public function testWrite()
    {
        $res = new Response();
        $property = new \ReflectionProperty($res, 'body');
        $property->setAccessible(true);
        $property->setValue($res, 'foo');
        $res->write('bar'); // <-- Should append to body

        $this->assertAttributeEquals('foobar', 'body', $res);
    }

    public function testLength()
    {
        $res = new Response('foo'); // <-- Sets body and length

        $this->assertEquals(3, $res->getLength());
    }

    public function testFinalize()
    {
        $res = new Response();
        $resFinal = $res->finalize();

        $this->assertTrue(is_array($resFinal));
        $this->assertEquals(3, count($resFinal));
        $this->assertEquals(200, $resFinal[0]);
        $this->assertInstanceOf('\Light\Http\Headers', $resFinal[1]);
        $this->assertEquals('', $resFinal[2]);
    }

    public function testFinalizeWithEmptyBody()
    {
        $res = new Response('Foo', 304);
        $resFinal = $res->finalize();

        $this->assertEquals('', $resFinal[2]);
    }

    public function testRedirect()
    {
        $res = new Response();
        $res->redirect('/foo');

        $pStatus = new \ReflectionProperty($res, 'status');
        $pStatus->setAccessible(true);

        $this->assertEquals(302, $pStatus->getValue($res));
        $this->assertEquals('/foo', $res->headers['Location']);
    }

    public function testIsEmpty()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r1->setStatus(404);
        $r2->setStatus(201);
        $this->assertFalse($r1->isEmpty());
        $this->assertTrue($r2->isEmpty());
    }

    public function testIsClientError()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r1->setStatus(404);
        $r2->setStatus(500);
        $this->assertTrue($r1->isClientError());
        $this->assertFalse($r2->isClientError());
    }

    public function testIsForbidden()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r1->setStatus(403);
        $r2->setStatus(500);
        $this->assertTrue($r1->isForbidden());
        $this->assertFalse($r2->isForbidden());
    }

    public function testIsInformational()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r1->setStatus(100);
        $r2->setStatus(200);
        $this->assertTrue($r1->isInformational());
        $this->assertFalse($r2->isInformational());
    }

    public function testIsNotFound()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r1->setStatus(404);
        $r2->setStatus(200);
        $this->assertTrue($r1->isNotFound());
        $this->assertFalse($r2->isNotFound());
    }

    public function testIsOk()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r1->setStatus(200);
        $r2->setStatus(201);
        $this->assertTrue($r1->isOk());
        $this->assertFalse($r2->isOk());
    }

    public function testIsSuccessful()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r3 = new Response();
        $r1->setStatus(200);
        $r2->setStatus(201);
        $r3->setStatus(302);
        $this->assertTrue($r1->isSuccessful());
        $this->assertTrue($r2->isSuccessful());
        $this->assertFalse($r3->isSuccessful());
    }

    public function testIsRedirect()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r1->setStatus(307);
        $r2->setStatus(304);
        $this->assertTrue($r1->isRedirect());
        $this->assertFalse($r2->isRedirect());
    }

    public function testIsRedirection()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r3 = new Response();
        $r1->setStatus(307);
        $r2->setStatus(304);
        $r3->setStatus(200);
        $this->assertTrue($r1->isRedirection());
        $this->assertTrue($r2->isRedirection());
        $this->assertFalse($r3->isRedirection());
    }

    public function testIsServerError()
    {
        $r1 = new Response();
        $r2 = new Response();
        $r1->setStatus(500);
        $r2->setStatus(400);
        $this->assertTrue($r1->isServerError());
        $this->assertFalse($r2->isServerError());
    }

    public function testMessageForCode()
    {
        $this->assertEquals('200 OK', Response::getMessageForCode(200));
    }

    public function testMessageForCodeWithInvalidCode()
    {
        $this->assertNull(Response::getMessageForCode(600));
    }
}
