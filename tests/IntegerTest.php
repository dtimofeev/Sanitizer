<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Sanitizer\Sanitizer;
use Sanitizer\SanitizerException;
use Sanitizer\SanitizerSchema;
use Sanitizer\SanitizerSchema as SS;
use Sanitizer\Schemas\IntegerSchema;

class IntegerTest extends TestCase
{
    public function testIsInstanceOfSanitizerSchema(): void
    {
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
    public function testValidCases($input, $expected): void
    {
        $this->assertEquals($expected, Sanitizer::process($input, SS::integer()));
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
    public function testInvalidCases($input): void
    {
        $this->expectException(SanitizerException::class);
        $this->expectExceptionCode(SanitizerException::ERR_INT_INVALID);
        Sanitizer::process($input, SS::integer());
    }

    /**
     * @return array
     */
    public function invalidCasesProvider(): array
    {
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

    public function testRuleMin(): void
    {
        Sanitizer::process(1, SS::integer()->min(1));

        try {
            Sanitizer::process(0, SS::integer()->min(1));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_INT_MIN, $e->getCode());
            $this->assertContains('1', $e->getMessage());
        }
    }

    public function testRuleMax(): void
    {
        Sanitizer::process(1, SS::integer()->max(1));

        try {
            Sanitizer::process(2, SS::integer()->max(1));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_INT_MAX, $e->getCode());
            $this->assertContains('1', $e->getMessage());
        }
    }

    public function testRuleBetween(): void
    {
        Sanitizer::process(1, SS::integer()->between(1, 100));
        Sanitizer::process(-1, SS::integer()->between(-100, 100));

        try {
            Sanitizer::process(-1, SS::integer()->between(1, 100));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_INT_MIN, $e->getCode());
        }

        try {
            Sanitizer::process(0, SS::integer()->between(100, -100));

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
    public function testRuleEquals($input, $expected): void
    {
        Sanitizer::process($input, SS::integer()->equals($expected));

        try {
            Sanitizer::process(100, SS::integer()->equals(101));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_INT_EQUALS, $e->getCode());
            $this->assertContains('101', $e->getMessage());
        }
    }

    public function testRuleNot(): void
    {
        Sanitizer::process(1, SS::integer()->not(0));

        try {
            Sanitizer::process(1, Sanitizer::process(1, SS::integer()->not(1)));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_INT_NOT_EQUALS, $e->getCode());
            $this->assertContains('1', $e->getMessage());
        }
    }

    public function testRuleOneOf(): void
    {
        Sanitizer::process(1, SS::integer()->oneOf([0, 1]));

        try {
            Sanitizer::process(-1, SS::integer()->oneOf([0, 1]));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_INT_ONE_OF, $e->getCode());
            $this->assertContains(implode('|', [0, 1]), $e->getMessage());
        }

        try {
            Sanitizer::process(1, SS::integer()->oneOf([]));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Values for "oneOf" rule should not be an empty array.', $e->getMessage());
        }
    }

    public function testRuleNotOneOf(): void
    {
        Sanitizer::process(2, SS::integer()->notOneOf([0, 1]));

        try {
            Sanitizer::process(0, SS::integer()->notOneOf([0, 1]));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_INT_NOT_ONE_OF, $e->getCode());
            $this->assertContains(implode('|', [0, 1]), $e->getMessage());
        }

        try {
            Sanitizer::process(0, SS::integer()->notOneOf([]));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Values for "notOneOf" rule should not be an empty array.', $e->getMessage());
        }
    }

    public function testOptional(): void
    {
        $this->assertEquals(1, Sanitizer::process(null, SS::integer()->optional(1)));

        foreach ([
            'test',
            [],
            true
        ] as $case) {
            try {
                Sanitizer::process(null, SS::integer()->optional($case));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(\InvalidArgumentException::class, $e);
                $this->assertEquals('Trying to set non-integer default value for integer schema.', $e->getMessage());
            }
        }
    }
}
