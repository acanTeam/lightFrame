<?php

class UtilTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }
    
    /**
     * Test strip slashes when magic quotes disabled
     */
    public function testStripSlashesWithoutMagicQuotes()
    {
        $data = "This should have \"quotes\" in it";
        $stripped = \Light\Stdlib\Util::stripSlashesIfMagicQuotes($data, false);
        $this->assertEquals($data, $stripped);
    }

    /**
     * Test strip slashes from array when magic quotes disabled
     */
    public function testStripSlashesFromArrayWithoutMagicQuotes()
    {
        $data = array("This should have \"quotes\" in it", "And this \"too\" has quotes");
        $stripped = \Light\Stdlib\Util::stripSlashesIfMagicQuotes($data, false);
        $this->assertEquals($data, $stripped);
    }

    /**
     * Test strip slashes when magic quotes enabled
     */
    public function testStripSlashesWithMagicQuotes()
    {
        $data = "This should have \"quotes\" in it";
        $stripped = \Light\Stdlib\Util::stripSlashesIfMagicQuotes($data, true);
        $this->assertEquals('This should have "quotes" in it', $stripped);
    }

    /**
     * Test strip slashes from array when magic quotes enabled
     */
    public function testStripSlashesFromArrayWithMagicQuotes()
    {
        $data = array("This should have \"quotes\" in it", "And this \"too\" has quotes");
        $stripped = \Light\Stdlib\Util::stripSlashesIfMagicQuotes($data, true);
        $this->assertEquals($data = array('This should have "quotes" in it', 'And this "too" has quotes'), $stripped);
    }

    /**
     * Test encrypt and decrypt with valid data
     */
    public function testEncryptAndDecryptWithValidData()
    {
        $data = 'foo';
        $key = 'secret';
        $iv = md5('initializationVector');
        $encrypted = \Light\Stdlib\Util::encrypt($data, $key, $iv);
        $decrypted = \Light\Stdlib\Util::decrypt($encrypted, $key, $iv);
        $this->assertEquals($data, $decrypted);
        $this->assertTrue($data !== $encrypted);
    }

    /**
     * Test encrypt when data is empty string
     */
    public function testEncryptWhenDataIsEmptyString()
    {
        $data = '';
        $key = 'secret';
        $iv = md5('initializationVector');
        $encrypted = \Light\Stdlib\Util::encrypt($data, $key, $iv);
        $this->assertEquals('', $encrypted);
    }

    /**
     * Test decrypt when data is empty string
     */
    public function testDecryptWhenDataIsEmptyString()
    {
        $data = '';
        $key = 'secret';
        $iv = md5('initializationVector');
        $decrypted = \Light\Stdlib\Util::decrypt($data, $key, $iv);
        $this->assertEquals('', $decrypted);
    }

    /**
     * Test encrypt when IV and key sizes are too long
     */
    public function testEncryptAndDecryptWhenKeyAndIvAreTooLong()
    {
        $data = 'foo';
        $key = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $iv = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $encrypted = \Light\Stdlib\Util::encrypt($data, $key, $iv);
        $decrypted = \Light\Stdlib\Util::decrypt($encrypted, $key, $iv);
        $this->assertEquals($data, $decrypted);
        $this->assertTrue($data !== $encrypted);
    }

    public function testEncodeAndDecodeSecureCookieWithValidData()
    {
        //Prepare cookie value
        $value = 'foo';
        $expires = time() + 86400;
        $secret = 'password';
        $algorithm = MCRYPT_RIJNDAEL_256;
        $mode = MCRYPT_MODE_CBC;
        $encodedValue = \Light\Stdlib\Util::encodeSecureCookie($value, $expires, $secret, $algorithm, $mode);
        $decodedValue = \Light\Stdlib\Util::decodeSecureCookie($encodedValue, $secret, $algorithm, $mode);

        //Test secure cookie value
        $parts = explode('|', $encodedValue);
        $this->assertEquals(3, count($parts));
        $this->assertEquals($expires, $parts[0]);
        $this->assertEquals($value, $decodedValue);
    }

    /**
     * Test encode/decode secure cookie with old expiration
     *
     * In this test, the expiration date is purposefully set to a time before now.
     * When decoding the encoded cookie value, FALSE is returned since the cookie
     * will have expired before it is decoded.
     */
    public function testEncodeAndDecodeSecureCookieWithOldExpiration()
    {
        $value = 'foo';
        $expires = time() - 100;
        $secret = 'password';
        $algorithm = MCRYPT_RIJNDAEL_256;
        $mode = MCRYPT_MODE_CBC;
        $encodedValue = \Light\Stdlib\Util::encodeSecureCookie($value, $expires, $secret, $algorithm, $mode);
        $decodedValue = \Light\Stdlib\Util::decodeSecureCookie($encodedValue, $secret, $algorithm, $mode);
        $this->assertFalse($decodedValue);
    }

    /**
     * Test encode/decode secure cookie with tampered data
     *
     * In this test, the encoded data is purposefully changed to simulate someone
     * tampering with the client-side cookie data. When decoding the encoded cookie value,
     * FALSE is returned since the verification key will not match.
     */
    public function testEncodeAndDecodeSecureCookieWithTamperedData()
    {
        $value = 'foo';
        $expires = time() + 86400;
        $secret = 'password';
        $algorithm = MCRYPT_RIJNDAEL_256;
        $mode = MCRYPT_MODE_CBC;
        $encodedValue = \Light\Stdlib\Util::encodeSecureCookie($value, $expires, $secret, $algorithm, $mode);
        $encodedValueParts = explode('|', $encodedValue);
        $encodedValueParts[1] = $encodedValueParts[1] . 'changed';
        $encodedValue = implode('|', $encodedValueParts);
        $decodedValue = \Light\Stdlib\Util::decodeSecureCookie($encodedValue, $secret, $algorithm, $mode);
        $this->assertFalse($decodedValue);
    }

    public function testSetCookieHeaderWithNameAndValue()
    {
        $name = 'foo';
        $value = 'bar';
        $header = array();
        \Light\Stdlib\Util::setCookieHeader($header, $name, $value);
        $this->assertEquals('foo=bar', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueWhenCookieAlreadySet()
    {
        $name = 'foo';
        $value = 'bar';
        $header = array('Set-Cookie' => 'one=two');
        \Light\Stdlib\Util::setCookieHeader($header, $name, $value);
        $this->assertEquals("one=two\nfoo=bar", $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomain()
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $header = array();
        \Light\Stdlib\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain
        ));
        $this->assertEquals('foo=bar; domain=foo.com', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPath()
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $header = array();
        \Light\Stdlib\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => $path
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAsString()
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = '2 days';
        $expiresFormat = gmdate('D, d-M-Y H:i:s e', strtotime($expires));
        $header = array();
        \Light\Stdlib\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo; expires=' . $expiresFormat, $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAsInteger()
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = strtotime('2 days');
        $expiresFormat = gmdate('D, d-M-Y H:i:s e', $expires);
        $header = array();
        \Light\Stdlib\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo; expires=' . $expiresFormat, $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAsZero()
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = 0;
        $header = array();
        \Light\Stdlib\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAndSecure()
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = strtotime('2 days');
        $expiresFormat = gmdate('D, d-M-Y H:i:s e', $expires);
        $secure = true;
        $header = array();
        \Light\Stdlib\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires,
            'secure' => $secure
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo; expires=' . $expiresFormat . '; secure', $header['Set-Cookie']);
    }

    public function testSetCookieHeaderWithNameAndValueAndDomainAndPathAndExpiresAndSecureAndHttpOnly()
    {
        $name = 'foo';
        $value = 'bar';
        $domain = 'foo.com';
        $path = '/foo';
        $expires = strtotime('2 days');
        $expiresFormat = gmdate('D, d-M-Y H:i:s e', $expires);
        $secure = true;
        $httpOnly = true;
        $header = array();
        \Light\Stdlib\Util::setCookieHeader($header, $name, array(
            'value' => $value,
            'domain' => $domain,
            'path' => '/foo',
            'expires' => $expires,
            'secure' => $secure,
            'httponly' => $httpOnly
        ));
        $this->assertEquals('foo=bar; domain=foo.com; path=/foo; expires=' . $expiresFormat . '; secure; HttpOnly', $header['Set-Cookie']);
    }

    /**
     * Test serializeCookies and decrypt with string expires
     *
     * In this test a cookie with a string typed value for 'expires' is set,
     * which should be parsed by `strtotime` to a timestamp when it's added to
     * the headers; this timestamp should then be correctly parsed, and the
     * value correctly decrypted, by `decodeSecureCookie`.
     */
    public function testSerializeCookiesAndDecryptWithStringExpires()
    {
        $value = 'bar';

        $headers = new \Light\Http\Headers();

        $settings = array(
            'cookies.encrypt' => true,
            'cookies.secret_key' => 'secret',
            'cookies.cipher' => MCRYPT_RIJNDAEL_256,
            'cookies.cipher_mode' => MCRYPT_MODE_CBC
        );

        $cookies = new \Light\Http\Cookies();
        $cookies->set('foo',  array(
            'value' => $value,
            'expires' => '1 hour'
        ));

        \Light\Stdlib\Util::serializeCookies($headers, $cookies, $settings);

        $encrypted = $headers->get('Set-Cookie');
        $encrypted = strstr($encrypted, ';', true);
        $encrypted = urldecode(substr(strstr($encrypted, '='), 1));

        $decrypted = \Light\Stdlib\Util::decodeSecureCookie(
            $encrypted,
            $settings['cookies.secret_key'],
            $settings['cookies.cipher'],
            $settings['cookies.cipher_mode']
        );

        $this->assertEquals($value, $decrypted);
        $this->assertTrue($value !== $encrypted);
    }

    public function testDeleteCookieHeaderWithSurvivingCookie()
    {
        $header = array('Set-Cookie' => "foo=bar\none=two");
        \Light\Stdlib\Util::deleteCookieHeader($header, 'foo');
        $this->assertEquals(1, preg_match("@^one=two\nfoo=; expires=@", $header['Set-Cookie']));
    }

    public function testDeleteCookieHeaderWithoutSurvivingCookie()
    {
        $header = array('Set-Cookie' => "foo=bar");
        \Light\Stdlib\Util::deleteCookieHeader($header, 'foo');
        $this->assertEquals(1, preg_match("@foo=; expires=@", $header['Set-Cookie']));
    }

    public function testDeleteCookieHeaderWithMatchingDomain()
    {
        $header = array('Set-Cookie' => "foo=bar; domain=foo.com");
        \Light\Stdlib\Util::deleteCookieHeader($header, 'foo', array(
            'domain' => 'foo.com'
        ));
        $this->assertEquals(1, preg_match("@foo=; domain=foo.com; expires=@", $header['Set-Cookie']));
    }

    public function testDeleteCookieHeaderWithoutMatchingDomain()
    {
        $header = array('Set-Cookie' => "foo=bar; domain=foo.com");
        \Light\Stdlib\Util::deleteCookieHeader($header, 'foo', array(
            'domain' => 'bar.com'
        ));
        $this->assertEquals(1, preg_match("@foo=bar; domain=foo\.com\nfoo=; domain=bar\.com@", $header['Set-Cookie']));
    }

    /**
     * Test parses Cookie: HTTP header
     */
    public function testParsesCookieHeader()
    {
        $header = 'foo=bar; one=two; colors=blue';
        $result = \Light\Stdlib\Util::parseCookieHeader($header);
        $this->assertEquals(3, count($result));
        $this->assertEquals('bar', $result['foo']);
        $this->assertEquals('two', $result['one']);
        $this->assertEquals('blue', $result['colors']);
    }

    /**
     * Test parses Cookie: HTTP header with `=` in the cookie value
     */
    public function testParsesCookieHeaderWithEqualSignInValue()
    {
        $header = 'foo=bar; one=two=; colors=blue';
        $result = \Light\Stdlib\Util::parseCookieHeader($header);
        $this->assertEquals(3, count($result));
        $this->assertEquals('bar', $result['foo']);
        $this->assertEquals('two=', $result['one']);
        $this->assertEquals('blue', $result['colors']);
    }

    public function testParsesCookieHeaderWithCommaSeparator()
    {
        $header = 'foo=bar, one=two, colors=blue';
        $result = \Light\Stdlib\Util::parseCookieHeader($header);
        $this->assertEquals(3, count($result));
        $this->assertEquals('bar', $result['foo']);
        $this->assertEquals('two', $result['one']);
        $this->assertEquals('blue', $result['colors']);
    }

    public function testPrefersLeftmostCookieWhenManyCookiesWithSameName()
    {
        $header = 'foo=bar; foo=beer';
        $result = \Light\Stdlib\Util::parseCookieHeader($header);
        $this->assertEquals('bar', $result['foo']);
    }
}
