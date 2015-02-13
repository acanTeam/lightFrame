<?php

use Light\Mvc\Middleware\Flash;

class FlashTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup
     */
    public function setUp()
    {
        $_SESSION = array();
    }

    /**
     * Test set flash message for next request
     */
    public function testSetFlashForNextRequest()
    {
        $f = new Flash();
        $f->set('foo', 'bar');
        $f->save();
        $this->assertEquals('bar', $_SESSION['light.flash']['foo']);
    }

    /**
     * Test set flash message for current request
     */
    public function testSetFlashForCurrentRequest()
    {
        $f = new Flash();
        $f->now('foo', 'bar');
        $this->assertEquals('bar', $f['foo']);
    }

    /**
     * Test loads flash from previous request
     */
    public function testLoadsFlashFromPreviousRequest()
    {
        $_SESSION['light.flash'] = array('info' => 'foo');
        $f = new Flash();
        $f->loadMessages();
        $this->assertEquals('foo', $f['info']);
    }

    /**
     * Test keep flash message for next request
     */
    public function testKeepFlashFromPreviousRequest()
    {
        $_SESSION['light.flash'] = array('info' => 'foo');
        $f = new Flash();
        $f->loadMessages();
        $f->keep();
        $f->save();
        $this->assertEquals('foo', $_SESSION['light.flash']['info']);
    }

    /**
     * Test flash messages from previous request do not persist to next request
     */
    public function testFlashMessagesFromPreviousRequestDoNotPersist()
    {
        $_SESSION['light.flash'] = array('info' => 'foo');
        $f = new Flash();
        $f->save();
        $this->assertEmpty($_SESSION['light.flash']);
    }

    /**
     * Test set Flash using array access
     */
    public function testFlashArrayAccess()
    {
        $_SESSION['light.flash'] = array('info' => 'foo');
        $f = new Flash();
        $f['info'] = 'bar';
        $f->save();
        $this->assertTrue(isset($f['info']));
        $this->assertEquals('bar', $f['info']);
        unset($f['info']);
        $this->assertFalse(isset($f['info']));
    }

    /**
     * Test iteration
     */
    public function testIteration()
    {
        $_SESSION['light.flash'] = array('info' => 'foo', 'error' => 'bar');
        $f = new Flash();
        $f->loadMessages();
        $output = '';
        foreach ($f as $key => $value) {
            $output .= $key . $value;
        }
        $this->assertEquals('infofooerrorbar', $output);
    }

    /**
     * Test countable
     */
    public function testCountable()
    {
        $_SESSION['light.flash'] = array('info' => 'foo', 'error' => 'bar');
        $f = new Flash();
        $f->loadMessages();
        $this->assertEquals(2, count($f));
    }


}
