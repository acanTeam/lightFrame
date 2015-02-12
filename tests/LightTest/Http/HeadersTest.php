<?php
class HeadersTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }
    
    public function testNormalizesKey()
    {
        $header = new \Light\Http\Headers();
        $header->set('Http_Content_Type', 'text/html');

        $prop = new \ReflectionProperty($header, 'data');
        $prop->setAccessible(true);
        $this->assertArrayHasKey('Content-Type', $prop->getValue($header));
    }

    public function testExtractHeaders()
    {
        $test = array(
            'HTTP_HOST' => 'foo.com',
            'SERVER_NAME' => 'foo',
            'CONTENT_TYPE' => 'text/html',
            'X_FORWARDED_FOR' => '127.0.0.1'
        );
        $results = \Light\Http\Headers::extract($test);
        $this->assertEquals(array(
            'HTTP_HOST' => 'foo.com',
            'CONTENT_TYPE' => 'text/html',
            'X_FORWARDED_FOR' => '127.0.0.1'
        ), $results);
    }
}
