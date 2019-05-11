<?php

namespace sanitizer\tests;

use PHPUnit\Framework\TestCase;
use sanitizer\Sanitizer;
use sanitizer\SanitizerSchema;
use sanitizer\SanitizerSchema as SS;
use sanitizer\schemas\IntegerSchema;

class IntegerTest extends TestCase {
    public function testIsInstanceOfSanitizerSchema(): void {
        $this->assertInstanceOf(
            SanitizerSchema::class,
            new IntegerSchema()
        );
    }

    /**
     * @param mixed $input
     * @param int $expected
     *
     * @dataProvider validCasesProvider
     */
    public function testValidCases($input, $expected): void {
        $sanitizer = new Sanitizer();

        $this->assertEquals($expected, $sanitizer->process($input, SS::integer()));
    }

    /**
     * @return array
     */
    public function validCasesProvider(): array {
        return [
            'Value -1' => [-1, -1],
            'Value 0' => [0, 0],
            'Value 999' => [999, 999],
            'Value PHP max int' => [PHP_INT_MAX, PHP_INT_MAX],
            'Value string 1' => ['1', 1],
            'Value string -1' => ['-1', -1],
        ];
    }

    /**
     * @param mixed $input
     *
     * @dataProvider invalidCasesProvider
     */
    public function testInvalidCases($input): void {
        $sanitizer = new Sanitizer();

        $this->expectException(\InvalidArgumentException::class);
        $sanitizer->process($input, SS::integer());
    }

    /**
     * @return array
     */
    public function invalidCasesProvider(): array {
        return [
            'Value true' => [true],
            'Value false' => [false],
            'Value string "invalid"' => ['invalid'],
            'Value floating number' => [3.14],
            'Value empty array' => [[]],
            'Value array' => [['key' => 'value']],
            'Value stdClass' => [new \stdClass()],
        ];
    }

    public function testRuleMin(): void {
        $sanitizer = new Sanitizer();

        $sanitizer->process(1, SS::integer()->min(1));

        try {
            $sanitizer->process(0, SS::integer()->min(1));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    public function testRuleMax(): void {
        $sanitizer = new Sanitizer();

        $sanitizer->process(1, SS::integer()->max(1));

        try {
            $sanitizer->process(2, SS::integer()->max(1));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    public function testRuleBetween(): void {
        $sanitizer = new Sanitizer();

        $sanitizer->process(1, SS::integer()->between(1, 100));
        $sanitizer->process(-1, SS::integer()->between(-100, 100));

        try {
            $sanitizer->process(-1, SS::integer()->between(1, 100));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }

        try {
            $sanitizer->process(0, SS::integer()->between(100, -100));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    /**
     * @param mixed $input
     * @param int $expected
     *
     * @dataProvider validCasesProvider
     */
    public function testRuleEquals($input, $expected): void {
        $sanitizer = new Sanitizer();

        $sanitizer->process($input, SS::integer()->equals($expected));

        try {
            $sanitizer->process(100, SS::integer()->equals(101));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    public function testRuleNot(): void {
        $sanitizer = new Sanitizer();

        $sanitizer->process(1, SS::integer()->not(0));

        try {
            $sanitizer->process(1, $sanitizer->process(1, SS::integer()->not(1)));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    public function testRuleOneOf(): void {
        $sanitizer = new Sanitizer();

        $sanitizer->process(1, SS::integer()->oneOf([0, 1]));

        try {
            $sanitizer->process(-1, SS::integer()->oneOf([0, 1]));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }

        try {
            $sanitizer->process(1, SS::integer()->oneOf([]));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Values for "oneOf" rule should not be an empty array.', $e->getMessage());
        }
    }

    public function testRuleNotOneOf(): void {
        $sanitizer = new Sanitizer();

        $sanitizer->process(2, SS::integer()->notOneOf([0, 1]));

        try {
            $sanitizer->process(0, SS::integer()->notOneOf([0, 1]));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        }

        try {
            $sanitizer->process(0, SS::integer()->notOneOf([]));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Values for "notOneOf" rule should not be an empty array.', $e->getMessage());
        }
    }
}
