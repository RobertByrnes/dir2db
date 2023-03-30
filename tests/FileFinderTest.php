<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class FileFInderTest extends TestCase
{
    private $stack;

    protected function setUp(): void
    {
        $this->stack = [];
    }

    public function testEmpty(): void
    {
        $this->assertTrue(empty($this->stack));
    }

    public function testPush(): void
    {
        array_push($this->stack, 'foo');

        $this->assertSame('foo', $this->stack[count($this->stack)-1]);
        $this->assertFalse(empty($this->stack));
    }

    public function testPop(): void
    {
        array_push($this->stack, 'foo');

        $this->assertSame('foo', array_pop($this->stack));
        $this->assertTrue(empty($this->stack));
    }
}