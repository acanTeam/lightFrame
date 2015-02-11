<?php

class MyWriter
{
    public function write( $object, $level )
    {
        echo (string) $object;

        return true;
    }
}

class LogTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = new \Light\Logger\Logger(new MyWriter());
    }

    public function tearDown()
    {
    }
    
    public function testEnabled()
    {
        $this->assertTrue($this->logger->isEnabled()); // Default case
        $this->logger->setEnabled(false);
        $this->assertFalse($this->logger->isEnabled());
        $this->logger->setEnabled(true);
        $this->assertTrue($this->logger->isEnabled());
    }

    public function testGetLevel()
    {
        $this->assertEquals(\Light\Logger\Logger::DEBUG, $this->logger->getLevel());
    }

    public function testSetLevel()
    {
        $this->logger->setLevel(\Light\Logger\Logger::WARN);
        $this->assertEquals(\Light\Logger\Logger::WARN, $this->logger->getLevel());
    }

    public function testSetInvalidLevel()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->logger->setLevel(\Light\Logger\Logger::DEBUG + 1);
    }

    public function testLogDebug()
    {
        $this->expectOutputString('Debug');
        $result = $this->logger->debug('Debug');
        $this->assertTrue($result);
    }

    public function testLogDebugExcludedByLevel()
    {
        $this->logger->setLevel(\Light\Logger\Logger::INFO);
        $this->assertFalse($this->logger->debug('Debug'));
    }

    public function testLogInfo()
    {
        $this->expectOutputString('Info');
        $result = $this->logger->info('Info');
        $this->assertTrue($result);
    }

    public function testLogInfoExcludedByLevel()
    {
        $this->logger->setLevel(\Light\Logger\Logger::NOTICE);
        $this->assertFalse($this->logger->info('Info'));
    }

    public function testLogNotice()
    {
        $this->expectOutputString('Notice');
        $result = $this->logger->notice('Notice');
        $this->assertTrue($result);
    }

    public function testLogNoticeExcludedByLevel()
    {
        $this->logger->setLevel(\Light\Logger\Logger::WARN);
        $this->assertFalse($this->logger->info('Info'));
    }

    public function testLogWarn()
    {
        $this->expectOutputString('Warn');
        $result = $this->logger->warning('Warn');
        $this->assertTrue($result);
    }

    public function testLogWarnExcludedByLevel()
    {
        $this->logger->setLevel(\Light\Logger\Logger::ERROR);
        $this->assertFalse($this->logger->warning('Warn'));
    }

    public function testLogError()
    {
        $this->expectOutputString('Error');
        $result = $this->logger->error('Error');
        $this->assertTrue($result);
    }

    public function testLogErrorExcludedByLevel()
    {
        $this->logger->setLevel(\Light\Logger\Logger::CRITICAL);
        $this->assertFalse($this->logger->error('Error'));
    }

    public function testLogCritical()
    {
        $this->expectOutputString('Critical');
        $result = $this->logger->critical('Critical');
        $this->assertTrue($result);
    }

    public function testLogCriticalExcludedByLevel()
    {
        $this->logger->setLevel(\Light\Logger\Logger::ALERT);
        $this->assertFalse($this->logger->critical('Critical'));
    }

    public function testLogAlert()
    {
        $this->expectOutputString('Alert');
        $result = $this->logger->alert('Alert');
        $this->assertTrue($result);
    }

    public function testLogAlertExcludedByLevel()
    {
        $this->logger->setLevel(\Light\Logger\Logger::EMERGENCY);
        $this->assertFalse($this->logger->alert('Alert'));
    }

    public function testLogEmergency()
    {
        $this->expectOutputString('Emergency');
        $result = $this->logger->emergency('Emergency');
        $this->assertTrue($result);
    }

    public function testInterpolateMessage()
    {
        $this->expectOutputString('Hello Light !');
        $result = $this->logger->debug(
            'Hello {framework} !',
            array('framework' => "Light")
        );
        $this->assertTrue($result);
    }

    public function testGetAndSetWriter()
    {
        $writer1 = new MyWriter();
        $writer2 = new MyWriter();
        $this->logger = new \Light\Logger\Logger($writer1);
        $this->assertSame($writer1, $this->logger->getWriter());
        $this->logger->setWriter($writer2);
        $this->assertSame($writer2, $this->logger->getWriter());
    }
}
