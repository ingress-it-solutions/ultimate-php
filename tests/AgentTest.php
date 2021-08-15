<?php

namespace Ultimate\Tests;


use Ultimate\Ultimate;
use Ultimate\Configuration;
use Ultimate\Models\Segment;
use PHPUnit\Framework\TestCase;

class AgentTest extends TestCase
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
        $this->ultimate->startTransaction('transaction-test');
    }

    /**
     * @throws \Ultimate\Exceptions\UltimateException
     */
    public function testUltimateInstance()
    {
        $this->assertInstanceOf(Ultimate::class, $this->ultimate);
    }

    public function testAddEntry()
    {
        $this->assertInstanceOf(
            Ultimate::class,
            $this->ultimate->addEntries($this->ultimate->startSegment('segment-test'))
        );

        $this->assertInstanceOf(
            Ultimate::class,
            $this->ultimate->addEntries([$this->ultimate->startSegment('segment-test')])
        );
    }

    public function testCallbackThrow()
    {
        $this->expectException(\Exception::class);

        $this->ultimate->addSegment(function () {
            throw new \Exception('Error in segment');
        }, 'callback', 'test exception throw', true);
    }

    public function testCallbackReturn()
    {
        $return = $this->ultimate->addSegment(function () {
            return 'Hello!';
        }, 'callback', 'test callback');

        $this->assertSame('Hello!', $return);
    }

    public function testAddSegmentWithInput()
    {
        $this->ultimate->addSegment(function ($segment) {
            $this->assertInstanceOf(Segment::class, $segment);
        }, 'callback', 'test callback', true);
    }

    public function testAddSegmentWithInputContext()
    {
        $segment = $this->ultimate->addSegment(function ($segment) {
            return $segment->setContext(['foo' => 'bar']);
        }, 'callback', 'test callback', true);

        $this->assertEquals(['foo' => 'bar'], $segment->context);
    }


    public function testStatusChecks()
    {
        $this->assertFalse($this->ultimate->isRecording());
        $this->assertFalse($this->ultimate->needTransaction());
        $this->assertFalse($this->ultimate->canAddSegments());

        $this->assertInstanceOf(Ultimate::class, $this->ultimate->startRecording());
        $this->assertTrue($this->ultimate->isRecording());
        $this->assertTrue($this->ultimate->canAddSegments());
    }
}
