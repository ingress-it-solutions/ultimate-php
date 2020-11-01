<?php

namespace Ultimate\Tests;


use Ultimate\Ultimate;
use Ultimate\Configuration;
use Ultimate\Models\Error;
use PHPUnit\Framework\TestCase;

class ExceptionEncoderTest extends TestCase
{
    /**
     * @var Ultimate
     */
    public $ultimate;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @throws \Exception
     */
    public function setUp(): void
    {
        $configuration = new Configuration('example-key');
        $configuration->setEnabled(false);
        $this->ultimate = new Ultimate($configuration);
        $this->ultimate->startTransaction('transaction-test');
    }

    public function testExceptionObjectResult()
    {
        $code = 1234;
        $message = 'Test Message';
        $exception = new \DomainException($message, $code);

        $error = new Error($exception, $this->ultimate->currentTransaction());

        $this->assertSame($message, $error['message']);
        $this->assertSame('DomainException', $error['class']);
        $this->assertSame($code, $error['code']);
        $this->assertSame(__FILE__, $error['file']);
        $this->assertNotEmpty($error['line']);
    }

    public function testStackTraceResult()
    {
        $exception = new \DomainException;
        $error = new Error($exception, $this->ultimate->currentTransaction());
        $originalStackTrace = $exception->getTrace();

        $this->assertTrue(is_array($error['stack']));

        // Contains vendor folder
        $vendor = false;
        foreach ($error['stack'] as $stack){
            if(array_key_exists('file', $stack) && strpos($stack['file'], 'vendor') !== false){
                $vendor = true;
                break;
            }
        }
        $this->assertTrue($vendor);

        $this->assertSame($originalStackTrace[0]['function'], $error['stack'][0]['function']);
        $this->assertSame($originalStackTrace[0]['class'], $error['stack'][0]['class']);
    }

    public function testEmptyExceptionMessageCase()
    {
        $exception = new \DomainException;
        $error = new Error($exception, $this->ultimate->currentTransaction());

        $this->assertSame('DomainException', $error['message']);
    }
}
