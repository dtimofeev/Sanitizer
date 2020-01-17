<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Sanitizer\Sanitizer;
use Sanitizer\SanitizerException;
use Sanitizer\SanitizerSchema;
use Sanitizer\SanitizerSchema as SS;
use Sanitizer\Schemas\DecimalSchema;

class DecimalTest extends TestCase
{
    public function testIsInstanceOfSanitizerSchema(): void
    {
        $this->assertInstanceOf(
            SanitizerSchema::class,
            new DecimalSchema()
        );
    }

    public function testBasic(): void
    {
        $this->assertEquals('1', Sanitizer::process('1', SS::decimal()));
        $this->assertEquals('1.01', Sanitizer::process('1.01', SS::decimal()));
        $this->assertEquals('1.01', Sanitizer::process(1.01, SS::decimal()));
        $this->assertEquals('7', Sanitizer::process(7, SS::decimal()));
        $this->assertEquals('31250000', Sanitizer::process(3.125e7, SS::decimal()));
        $this->assertEquals('3.3333333333333', Sanitizer::process(3.33333333333333333333, SS::decimal()));

        foreach (['3.125e7', 'test'] as $case) {
            try {
                Sanitizer::process($case, SS::decimal());

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
                $this->assertEquals(SanitizerException::ERR_DEC_INVALID, $e->getCode());
            }
        }
    }

    public function testRuleMin(): void
    {
        foreach ([9, 9.01, '9', '9.01'] as $case) {
            $this->assertEquals(10, Sanitizer::process(10, SS::decimal()->min($case)));
        }

        foreach ([11, 11.01, '11', '11.01'] as $case) {
            try {
                Sanitizer::process(10, SS::decimal()->min($case));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
                $this->assertEquals(SanitizerException::ERR_DEC_MIN, $e->getCode());
            }
        }
    }

    public function testRuleMax(): void
    {
        foreach ([11, 11.01, '11', '11.01'] as $case) {
            $this->assertEquals(10, Sanitizer::process(10, SS::decimal()->max($case)));
        }

        foreach ([9, 9.01, '9', '9.01'] as $case) {
            try {
                Sanitizer::process(10, SS::decimal()->max($case));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
                $this->assertEquals(SanitizerException::ERR_DEC_MAX, $e->getCode());
            }
        }
    }

    public function testOptional(): void
    {
        $this->assertEquals(1, Sanitizer::process(null, SS::decimal()->optional(1)));

        foreach ([
            'test',
            [],
            true
        ] as $case) {
            try {
                Sanitizer::process(null, SS::decimal()->optional($case));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(\InvalidArgumentException::class, $e);
                $this->assertEquals('Trying to set non-decimal default value for decimal schema.', $e->getMessage());
            }
        }
    }
}
