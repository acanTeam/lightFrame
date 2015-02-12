<?php

use Light\Mvc\Environment;
use Light\Http\Request;

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }
    
    /**
     * Test sets HTTP method
     */
    public function testGetMethod()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'GET'
        ));
        $req = new Request($env);
        $this->assertEquals('GET', $req->getMethod());
    }

    /**
     * Test HTTP GET method detection
     */
    public function testIsGet()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'GET'
        ));
        $req = new Request($env);
        $this->assertTrue($req->isGet());
    }

    /**
     * Test HTTP POST method detection
     */
    public function testIsPost()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
        ));
        $req = new Request($env);
        $this->assertTrue($req->isPost());
    }

    /**
     * Test HTTP PUT method detection
     */
    public function testIsPut()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'PUT',
        ));
        $req = new Request($env);
        $this->assertTrue($req->isPut());
    }

    /**
     * Test HTTP DELETE method detection
     */
    public function testIsDelete()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'DELETE',
        ));
        $req = new Request($env);
        $this->assertTrue($req->isDelete());
    }

    /**
     * Test HTTP OPTIONS method detection
     */
    public function testIsOptions()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'OPTIONS',
        ));
        $req = new Request($env);
        $this->assertTrue($req->isOptions());
    }

    /**
     * Test HTTP HEAD method detection
     */
    public function testIsHead()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'HEAD',
        ));
        $req = new Request($env);
        $this->assertTrue($req->isHead());
    }

    /**
     * Test HTTP PATCH method detection
     */
    public function testIsPatch()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'PATCH',
        ));
        $req = new Request($env);
        $this->assertTrue($req->isPatch());
    }

    /**
     * Test AJAX method detection w/ header
     */
    public function testIsAjaxWithHeader()
    {
        $env = Environment::mock(array(
            'X_REQUESTED_WITH' => 'XMLHttpRequest'
        ));
        $req = new Request($env);
        $this->assertTrue($req->isAjax());
        $this->assertTrue($req->isXhr());
    }

    /**
     * Test AJAX method detection w/ query parameter
     */
    public function testIsAjaxWithQueryParameter()
    {
        $env = Environment::mock(array(
            'QUERY_STRING' => 'isajax=1',
        ));
        $req = new Request($env);
        $this->assertTrue($req->isAjax());
        $this->assertTrue($req->isXhr());
    }

    /**
     * Test AJAX method detection without header or query parameter
     */
    public function testIsAjaxWithoutHeaderOrQueryParameter()
    {
        $env = Environment::mock();
        $req = new Request($env);
        $this->assertFalse($req->isAjax());
        $this->assertFalse($req->isXhr());
    }

    /**
     * Test AJAX method detection with misspelled header
     */
    public function testIsAjaxWithMisspelledHeader()
    {
        $env = Environment::mock(array(
            'X_REQUESTED_WITH' => 'foo'
        ));
        $req = new Request($env);
        $this->assertFalse($req->isAjax());
        $this->assertFalse($req->isXhr());
    }

    /**
     * Test params from query string
     */
    public function testParamsFromQueryString()
    {
        $env = Environment::mock(array(
            'QUERY_STRING' => 'one=1&two=2&three=3'
        ));
        $req = new Request($env);
        $this->assertEquals(3, count($req->params()));
        $this->assertEquals('1', $req->params('one'));
        $this->assertNull($req->params('foo'));
        $this->assertEquals(1, $req->params('foo', 1));
    }

    /**
     * Test params from request body
     */
    public function testParamsFromRequestBody()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING' => 'one=1&two=2&three=3',
            'light.input' => 'foo=bar&abc=123',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 15
        ));
        $req = new Request($env);
        $this->assertEquals(5, count($req->params())); //Union of GET and POST
        $this->assertEquals('bar', $req->params('foo'));
    }

    /**
     * Test fetch GET params
     */
    public function testGet()
    {
        $env = Environment::mock(array(
            'QUERY_STRING' => 'one=1&two=2&three=3'
        ));
        $req = new Request($env);
        $this->assertEquals(3, count($req->get()));
        $this->assertEquals('1', $req->get('one'));
        $this->assertNull($req->get('foo'));
        $this->assertFalse($req->get('foo', false));
    }

    /**
     * Test fetch GET params without multibyte
     */
    public function testGetWithoutMultibyte()
    {
        $env = Environment::mock(array(
            'QUERY_STRING' => 'one=1&two=2&three=3',
            'light.tests.ignore_multibyte' => true
        ));
        $req = new Request($env);
        $this->assertEquals(3, count($req->get()));
        $this->assertEquals('1', $req->get('one'));
        $this->assertNull($req->get('foo'));
        $this->assertFalse($req->get('foo', false));
    }

    /**
     * Test fetch POST params
     */
    public function testPost()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'light.input' => 'foo=bar&abc=123',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 15
        ));
        $req = new Request($env);
        $this->assertEquals(2, count($req->post()));
        $this->assertEquals('bar', $req->post('foo'));
        $this->assertNull($req->post('xyz'));
        $this->assertFalse($req->post('xyz', false));
    }

    /**
     * Test fetch POST params without multibyte
     */
    public function testPostWithoutMultibyte()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'light.input' => 'foo=bar&abc=123',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 15,
            'light.tests.ignore_multibyte' => true
        ));
        $req = new Request($env);
        $this->assertEquals(2, count($req->post()));
        $this->assertEquals('bar', $req->post('foo'));
        $this->assertNull($req->post('xyz'));
        $this->assertFalse($req->post('xyz', false));
    }

    /**
     * Test fetch POST without light.input
     */
    public function testPostWithoutInput()
    {
        $this->setExpectedException('RuntimeException');
        $env = Environment::mock();
        unset($env['light.input']);
        $req = new Request($env);
        $req->post('foo');
    }

    /**
     * Test fetch POST params even if multipart/form-data request
     */
    public function testPostWithMultipartRequest()
    {
        $_POST = array('foo' => 'bar'); //<-- Set by PHP
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'light.input' => '', //<-- "php://input" is empty for multipart/form-data requests
            'CONTENT_TYPE' => 'multipart/form-data',
            'CONTENT_LENGTH' => 0
        ));
        $req = new Request($env);
        $this->assertEquals(1, count($req->post()));
        $this->assertEquals('bar', $req->post('foo'));
        $this->assertNull($req->post('xyz'));
    }

    /**
     * Test fetch PUT params
     */
    public function testPut()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'PUT',
            'light.input' => 'foo=bar&abc=123',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 15
        ));
        $req = new Request($env);
        $this->assertEquals(2, count($req->put()));
        $this->assertEquals('bar', $req->put('foo'));
        $this->assertEquals('bar', $req->params('foo'));
        $this->assertNull($req->put('xyz'));
        $this->assertFalse($req->put('xyz', false));
    }

    /**
     * Test fetch PATCH params
     */
    public function testPatch()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'PATCH',
            'light.input' => 'foo=bar&abc=123',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 15
        ));
        $req = new Request($env);
        $this->assertEquals(2, count($req->patch()));
        $this->assertEquals('bar', $req->patch('foo'));
        $this->assertEquals('bar', $req->params('foo'));
        $this->assertNull($req->patch('xyz'));
        $this->assertFalse($req->patch('xyz', false));
    }

    /**
     * Test fetch DELETE params
     */
    public function testDelete()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'DELETE',
            'light.input' => 'foo=bar&abc=123',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 15
        ));
        $req = new Request($env);
        $this->assertEquals(2, count($req->delete()));
        $this->assertEquals('bar', $req->delete('foo'));
        $this->assertEquals('bar', $req->params('foo'));
        $this->assertNull($req->delete('xyz'));
        $this->assertFalse($req->delete('xyz', false));
    }

    /**
     * Test fetch COOKIE params
     */
    public function testCookies()
    {
        $env = Environment::mock(array(
            'HTTP_COOKIE' => 'foo=bar; abc=123'
        ));
        $req = new Request($env);
        $this->assertEquals(2, count($req->cookies()));
        $this->assertEquals('bar', $req->cookies('foo'));
        $this->assertNull($req->cookies('xyz'));
    }

    /**
     * Test is form data
     */
    public function testIsFormDataContentFormUrlencoded()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'PUT',
            'light.input' => '',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded'
        ));
        $req = new Request($env);
        $this->assertTrue($req->isFormData());
    }

    /**
     * Test is form data
     */
    public function testIsFormDataPostContentUnknown()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'POST',
            'light.input' => '',
        ));
        $req = new Request($env);
        $this->assertTrue($req->isFormData());
    }

    /**
     * Test is form data
     */
    public function testIsFormDataPostContentUnknownWithMethodOverride()
    {
        $env = Environment::mock(array(
            'REQUEST_METHOD' => 'PUT',
        ));
        $env['light.method_override.original_method'] = 'POST';
        $req = new Request($env);
        $this->assertTrue($req->isPut());
        $this->assertTrue($req->isFormData());
    }

    /**
     * Test is not form data
     */
    public function testIsNotFormData()
    {
        $env = Environment::mock(array(
            'light.input' => '',
            'CONTENT_TYPE' => 'application/json'
        ));
        $req = new Request($env);
        $this->assertFalse($req->isFormData());
    }

    /**
     * Test headers
     */
    public function testHeaders()
    {
        $env = Environment::mock(array(
            'HTTP_ACCEPT_ENCODING' => 'gzip'
        ));
        $req = new Request($env);
        $headers = $req->headers();
        $this->assertInstanceOf('\Light\Http\Headers', $headers);
        $this->assertEquals('gzip', $req->headers('HTTP_ACCEPT_ENCODING'));
        $this->assertEquals('gzip', $req->headers('HTTP-ACCEPT-ENCODING'));
        $this->assertEquals('gzip', $req->headers('http_accept_encoding'));
        $this->assertEquals('gzip', $req->headers('http-accept-encoding'));
        $this->assertEquals('gzip', $req->headers('ACCEPT_ENCODING'));
        $this->assertEquals('gzip', $req->headers('ACCEPT-ENCODING'));
        $this->assertEquals('gzip', $req->headers('accept_encoding'));
        $this->assertEquals('gzip', $req->headers('accept-encoding'));
        $this->assertNull($req->headers('foo'));
    }

    /**
     * Test accurately removes HTTP_ prefix from input header name
     */
    public function testHeaderRemovesHttpPrefix()
    {
        $env = Environment::mock(array(
            'X_HTTP_METHOD_OVERRIDE' => 'PUT',
            'CONTENT_TYPE' => 'application/json'
        ));
        //fwrite(fopen('php://stdout', 'w'), print_r($env, true));
        $req = new Request($env);
        $this->assertEquals('PUT', $req->headers('X_HTTP_METHOD_OVERRIDE'));
        $this->assertNull($req->headers('X_METHOD_OVERRIDE')); //<-- Ensures `HTTP_` is not removed if not prefix
        $this->assertEquals('application/json', $req->headers('HTTP_CONTENT_TYPE')); //<-- Ensures `HTTP_` is removed if prefix
    }

    /**
     * Test get body
     */
    public function testGetBodyWhenExists()
    {
        $env = Environment::mock(array(
            'light.input' => 'foo=bar&abc=123',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 15
        ));
        $req = new Request($env);
        $this->assertEquals('foo=bar&abc=123', $req->getBody());
    }

    /**
     * Test get body
     */
    public function testGetBodyWhenNotExists()
    {
        $env = Environment::mock();
        $req = new Request($env);
        $this->assertEquals('', $req->getBody());
    }

    /**
     * Test get content type
     */
    public function testGetContentTypeWhenExists()
    {
        $env = Environment::mock(array(
            'light.input' => '',
            'CONTENT_TYPE' => 'application/json; charset=ISO-8859-4'
        ));
        $req = new Request($env);
        $this->assertEquals('application/json; charset=ISO-8859-4', $req->getContentType());
    }

    /**
     * Test get content type for built-in PHP server
     */
    public function testGetContentTypeForBuiltInServer()
    {
        $env = Environment::mock(array(
            'light.input' => '',
            'HTTP_CONTENT_TYPE' => 'application/json; charset=ISO-8859-4'
        ));
        $req = new Request($env);
        $this->assertEquals('application/json; charset=ISO-8859-4', $req->getContentType());
    }

    /**
     * Test get content type
     */
    public function testGetContentTypeWhenNotExists()
    {
        $env = Environment::mock();
        $req = new Request($env);
        $this->assertNull($req->getContentType());
    }

    /**
     * Test get content type with built-in server
     */
    public function testGetContentTypeWithBuiltInServer()
    {
        $env = Environment::mock(array(
            'light.input' => '',
            'HTTP_CONTENT_TYPE' => 'application/json; charset=ISO-8859-4'
        ));
        $req = new Request($env);
        $this->assertEquals('application/json; charset=ISO-8859-4', $req->getContentType());
    }

    /**
     * Test get media type
     */
    public function testGetMediaTypeWhenExists()
    {
        $env = Environment::mock(array(
            'CONTENT_TYPE' => 'application/json;charset=utf-8'
        ));
        $req = new Request($env);
        $this->assertEquals('application/json', $req->getMediaType());
    }

    /**
     * Test get media type
     */
    public function testGetMediaTypeWhenNotExists()
    {
        $env = Environment::mock();
        $req = new Request($env);
        $this->assertNull($req->getMediaType());
    }

    /**
     * Test get media type
     */
    public function testGetMediaTypeWhenNoParamsExist()
    {
        $env = Environment::mock(array(
            'light.input' => '',
            'CONTENT_TYPE' => 'application/json'
        ));
        $req = new Request($env);
        $this->assertEquals('application/json', $req->getMediaType());
    }

    /**
     * Test get media type params
     */
    public function testGetMediaTypeParams()
    {
        $env = Environment::mock(array(
            'CONTENT_TYPE' => 'application/json; charset=ISO-8859-4'
        ));
        $req = new Request($env);
        $params = $req->getMediaTypeParams();
        $this->assertEquals(1, count($params));
        $this->assertArrayHasKey('charset', $params);
        $this->assertEquals('ISO-8859-4', $params['charset']);
    }

    /**
     * Test get media type params
     */
    public function testGetMediaTypeParamsWhenNotExists()
    {
        $env = Environment::mock();
        $req = new Request($env);
        $params = $req->getMediaTypeParams();
        $this->assertTrue(is_array($params));
        $this->assertEquals(0, count($params));
    }

    /**
     * Test get content charset
     */
    public function testGetContentCharset()
    {
        $env = Environment::mock(array(
            'light.input' => '',
            'CONTENT_TYPE' => 'application/json; charset=ISO-8859-4'
        ));
        $req = new Request($env);
        $this->assertEquals('ISO-8859-4', $req->getContentCharset());
    }

    /**
     * Test get content charset
     */
    public function testGetContentCharsetWhenNotExists()
    {
        $env = Environment::mock(array(
            'light.input' => '',
            'CONTENT_TYPE' => 'application/json'
        ));
        $req = new Request($env);
        $this->assertNull($req->getContentCharset());
    }

    /**
     * Test get content length
     */
    public function testGetContentLength()
    {
        $env = Environment::mock(array(
            'light.input' => 'foo=bar&abc=123',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'CONTENT_LENGTH' => 15
        ));
        $req = new Request($env);
        $this->assertEquals(15, $req->getContentLength());
    }

    /**
     * Test get content length
     */
    public function testGetContentLengthWhenNotExists()
    {
        $env = Environment::mock(array(
            'light.input' => '',
        ));
        $req = new Request($env);
        $this->assertEquals(0, $req->getContentLength());
    }

    /**
     * Test get host
     */
    public function testGetHost()
    {
        $env = Environment::mock(array(
            'SERVER_NAME' => 'light',
            'HTTP_HOST' => 'lightframework.com'
        ));
        $req = new Request($env);
        $this->assertEquals('lightframework.com', $req->getHost()); //Uses HTTP_HOST if available
    }

    /**
     * Test get host when it has a port number
     */
    public function testGetHostAndStripPort()
    {
        $env = Environment::mock(array(
            'SERVER_NAME' => 'light',
            'HTTP_HOST' => 'lightframework.com:80'
        ));
        $req = new Request($env);
        $this->assertEquals('lightframework.com', $req->getHost()); //Uses HTTP_HOST if available
    }

    /**
     * Test get host
     */
    public function testGetHostWhenNotExists()
    {
        $env = Environment::mock(array(
            'SERVER_NAME' => 'light',
            'HTTP_HOST' => 'lightframework.com'
        ));
        unset($env['HTTP_HOST']);
        $req = new Request($env);
        $this->assertEquals('light', $req->getHost()); //Uses SERVER_NAME as backup
    }

    /**
     * Test get host with port
     */
    public function testGetHostWithPort()
    {
        $env = Environment::mock(array(
            'HTTP_HOST' => 'lightframework.com',
            'SERVER_NAME' => 'light',
            'SERVER_PORT' => 80,
            'light.url_scheme' => 'http'
        ));
        $req = new Request($env);
        $this->assertEquals('lightframework.com:80', $req->getHostWithPort());
    }

    /**
     * Test get host with port doesn't duplicate port numbers
     */
    public function testGetHostDoesntDuplicatePort()
    {
        $env = Environment::mock(array(
            'HTTP_HOST' => 'lightframework.com:80',
            'SERVER_NAME' => 'light',
            'SERVER_PORT' => 80,
            'light.url_scheme' => 'http'
        ));
        $req = new Request($env);
        $this->assertEquals('lightframework.com:80', $req->getHostWithPort());
    }

    /**
     * Test get port
     */
    public function testGetPort()
    {
        $env = Environment::mock(array(
            'SERVER_PORT' => 80
        ));
        $req = new Request($env);
        $this->assertTrue(is_integer($req->getPort()));
        $this->assertEquals(80, $req->getPort());
    }

    /**
     * Test get scheme
     */
    public function testGetSchemeIfHttp()
    {
        $env = Environment::mock(array(
            'light.url_scheme' => 'http'
        ));
        $req = new Request($env);
        $this->assertEquals('http', $req->getScheme());
    }

    /**
     * Test get scheme
     */
    public function testGetSchemeIfHttps()
    {
        $env = Environment::mock(array(
            'light.url_scheme' => 'https',
        ));
        $req = new Request($env);
        $this->assertEquals('https', $req->getScheme());
    }

    /**
     * Test get [script name, root uri, path, path info, resource uri] in subdirectory without htaccess
     */
    public function testAppPathsInSubdirectoryWithoutHtaccess()
    {
        $env = Environment::mock(array(
            'SCRIPT_NAME' => '/foo/index.php', //<-- Physical
            'PATH_INFO' => '/bar/xyz', //<-- Virtual
        ));
        $req = new Request($env);
        $this->assertEquals('/foo/index.php', $req->getScriptName());
        $this->assertEquals('/foo/index.php', $req->getRootUri());
        $this->assertEquals('/foo/index.php/bar/xyz', $req->getPath());
        $this->assertEquals('/bar/xyz', $req->getPathInfo());
        $this->assertEquals('/bar/xyz', $req->getResourceUri());
    }

    /**
     * Test get [script name, root uri, path, path info, resource uri] in subdirectory with htaccess
     */
    public function testAppPathsInSubdirectoryWithHtaccess()
    {
        $env = Environment::mock(array(
            'SCRIPT_NAME' => '/foo', //<-- Physical
            'PATH_INFO' => '/bar/xyz', //<-- Virtual
        ));
        $req = new Request($env);
        $this->assertEquals('/foo', $req->getScriptName());
        $this->assertEquals('/foo', $req->getRootUri());
        $this->assertEquals('/foo/bar/xyz', $req->getPath());
        $this->assertEquals('/bar/xyz', $req->getPathInfo());
        $this->assertEquals('/bar/xyz', $req->getResourceUri());
    }

    /**
     * Test get [script name, root uri, path, path info, resource uri] in root directory without htaccess
     */
    public function testAppPathsInRootDirectoryWithoutHtaccess()
    {
        $env = Environment::mock(array(
            'SCRIPT_NAME' => '/index.php', //<-- Physical
            'PATH_INFO' => '/bar/xyz', //<-- Virtual
        ));
        $req = new Request($env);
        $this->assertEquals('/index.php', $req->getScriptName());
        $this->assertEquals('/index.php', $req->getRootUri());
        $this->assertEquals('/index.php/bar/xyz', $req->getPath());
        $this->assertEquals('/bar/xyz', $req->getPathInfo());
        $this->assertEquals('/bar/xyz', $req->getResourceUri());
    }

    /**
     * Test get [script name, root uri, path, path info, resource uri] in root directory with htaccess
     */
    public function testAppPathsInRootDirectoryWithHtaccess()
    {
        $env = Environment::mock(array(
            'SCRIPT_NAME' => '', //<-- Physical
            'PATH_INFO' => '/bar/xyz', //<-- Virtual
        ));
        $req = new Request($env);
        $this->assertEquals('', $req->getScriptName());
        $this->assertEquals('', $req->getRootUri());
        $this->assertEquals('/bar/xyz', $req->getPath());
        $this->assertEquals('/bar/xyz', $req->getPathInfo());
        $this->assertEquals('/bar/xyz', $req->getResourceUri());
    }

    /**
     * Test get URL
     */
    public function testGetUrl()
    {
        $env = Environment::mock(array(
            'HTTP_HOST' => 'lightframework.com',
            'SERVER_NAME' => 'light',
            'SERVER_PORT' => 80,
            'light.url_scheme' => 'http'
        ));
        $req = new Request($env);
        $this->assertEquals('http://lightframework.com', $req->getUrl());
    }

    /**
     * Test get URL
     */
    public function testGetUrlWithCustomPort()
    {
        $env = Environment::mock(array(
            'HTTP_HOST' => 'lightframework.com',
            'SERVER_NAME' => 'light',
            'SERVER_PORT' => 8080,
            'light.url_scheme' => 'http'
        ));
        $req = new Request($env);
        $this->assertEquals('http://lightframework.com:8080', $req->getUrl());
    }

    /**
     * Test get URL
     */
    public function testGetUrlWithHttps()
    {
        $env = Environment::mock(array(
            'HTTP_HOST' => 'lightframework.com',
            'SERVER_NAME' => 'light',
            'SERVER_PORT' => 443,
            'light.url_scheme' => 'https'
        ));
        $req = new Request($env);
        $this->assertEquals('https://lightframework.com', $req->getUrl());
    }

    /**
     * Test get IP
     *  @dataProvider dataTestIp
     */
    public function testGetIp(array $server, $expected)
    {
        $env = Environment::mock($server);
        $req = new Request($env);
        $this->assertEquals($expected, $req->getIp());
    }

    public function dataTestIp()
    {
        return array(
                array(array('REMOTE_ADDR' => '127.0.0.1'), '127.0.0.1'),
                array(array('REMOTE_ADDR' => '127.0.0.1', 'CLIENT_IP' => '127.0.0.2'), '127.0.0.2'),
                array(array('REMOTE_ADDR' => '127.0.0.1', 'CLIENT_IP' => '127.0.0.2', 'X_FORWARDED_FOR' => '127.0.0.3'), '127.0.0.3'),
                array(array('REMOTE_ADDR' => '127.0.0.1', 'CLIENT_IP' => '127.0.0.2', 'HTTP_X_FORWARDED_FOR' => '127.0.0.4'), '127.0.0.4'),
                array(array('REMOTE_ADDR' => '127.0.0.1', 'CLIENT_IP' => '127.0.0.2', 'X_FORWARDED_FOR' => '127.0.0.3', 'HTTP_X_FORWARDED_FOR' => '127.0.0.4'), '127.0.0.3'),
        );
    }

    /**
     * Test get referer
     */
    public function testGetReferrer()
    {
        $env = Environment::mock(array(
            'HTTP_REFERER' => 'http://foo.com'
        ));
        $req = new Request($env);
        $this->assertEquals('http://foo.com', $req->getReferrer());
        $this->assertEquals('http://foo.com', $req->getReferer());
    }

    /**
     * Test get referer
     */
    public function testGetReferrerWhenNotExists()
    {
        $env = Environment::mock();
        $req = new Request($env);
        $this->assertNull($req->getReferrer());
        $this->assertNull($req->getReferer());
    }

    /**
     * Test get user agent string
     */
    public function testGetUserAgent()
    {
        $env = Environment::mock(array(
            'HTTP_USER_AGENT' => 'user-agent-string'
        ));
        $req = new Request($env);
        $this->assertEquals('user-agent-string', $req->getUserAgent());
    }

    /**
     * Test get user agent string when not set
     */
    public function testGetUserAgentWhenNotExists()
    {
        $env = Environment::mock();
        unset($env['HTTP_USER_AGENT']);
        $req = new Request($env);
        $this->assertNull($req->getUserAgent());
    }
}
