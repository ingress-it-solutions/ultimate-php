<?php


namespace Ultimate\Tests;


use Ultimate\Ultimate;
use Ultimate\Configuration;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
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
        $configuration = new Configuration('example-api-key');
        $configuration->setEnabled(false);

        $this->ultimate = new Ultimate($configuration);
        $this->ultimate->startTransaction('testcase');
    }

    public function testTransactionData()
    {
        $this->assertSame($this->ultimate->currentTransaction()::MODEL_NAME, $this->ultimate->currentTransaction()->model);
        $this->assertSame($this->ultimate->currentTransaction()::TYPE_PROCESS, $this->ultimate->currentTransaction()->type);
        $this->assertSame('testcase', $this->ultimate->currentTransaction()->name);
    }

    public function testSegmentData()
    {
        $segment = $this->ultimate->startSegment(__FUNCTION__, 'hello segment!');

        $this->assertIsArray($segment->toArray());
        $this->assertSame($segment::MODEL_NAME, $segment->model);
        $this->assertSame(__FUNCTION__, $segment->type);
        $this->assertSame('hello segment!', $segment->label);
        $this->assertSame($this->ultimate->currentTransaction()->only(['hash', 'timestamp']), $segment->transaction);
    }

    public function testErrorData()
    {
        $error = $this->ultimate->reportException(new \Exception('test error'));
        $error_arr = $error->toArray();

        $this->assertArrayHasKey('message', $error_arr);
        $this->assertArrayHasKey('stack', $error_arr);
        $this->assertArrayHasKey('file', $error_arr);
        $this->assertArrayHasKey('line', $error_arr);
        $this->assertArrayHasKey('code', $error_arr);
        $this->assertArrayHasKey('class', $error_arr);
        $this->assertArrayHasKey('timestamp', $error_arr);

        $this->assertSame($error::MODEL_NAME, $error->model);
        $this->assertSame($this->ultimate->currentTransaction()->only(['name', 'hash']), $error->transaction);
    }

    public function testSetContext()
    {
        $this->ultimate->currentTransaction()->addContext('test', ['foo' => 'bar']);

        $this->assertEquals(['test' => ['foo' => 'bar']], $this->ultimate->currentTransaction()->context);
    }

    public function testEncoding()
    {
        $this->assertStringContainsString(trim(json_encode([
            'model' => 'transaction',
        ]), '{}'), json_encode($this->ultimate->currentTransaction()));

        $this->assertStringContainsString(trim(json_encode([
            'model' => 'segment',
            'type' => 'test',
        ]), '{}'), json_encode($this->ultimate->startSegment('test')));

        $error = $this->ultimate->reportException(new \DomainException('test error'));
        $this->assertStringContainsString(trim(json_encode([
            'model' => 'error'
        ]), '{}'), json_encode($error));
    }
}
