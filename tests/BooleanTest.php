<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Sanitizer\Sanitizer;
use Sanitizer\SanitizerException;
use Sanitizer\SanitizerSchema;
use Sanitizer\SanitizerSchema as SS;
use Sanitizer\Schemas\BooleanSchema;

class BooleanTest extends TestCase
{
    public function testIsInstanceOfSanitizerSchema(): void
    {
        $this->assertInstanceOf(
            SanitizerSchema::class,
            new BooleanSchema()
        );
    }

    public function testValueTrue(): void
    {
        $this->assertTrue(Sanitizer::process(true, SS::boolean()));
    }

    public function testValueFalse(): void
    {
        $this->assertFalse(Sanitizer::process(false, SS::boolean()));
    }

    public function testValueTruthlyInteger(): void
    {
        $this->assertTrue(Sanitizer::process(1, SS::boolean()));
    }

    public function testValueFalsyInteger(): void
    {
        $this->assertFalse(Sanitizer::process(0, SS::boolean()));
    }

    public function testDefault(): void
    {
        $this->assertNull(Sanitizer::process(null, SS::boolean()->optional()));
    }

    public function testDefaultExplicitTrue(): void
    {
        $this->assertTrue(Sanitizer::process(null, SS::boolean()->optional(true)));
    }

    public function testDefaultExplicitFalse(): void
    {
        $this->assertFalse(Sanitizer::process(null, SS::boolean()->optional(false)));
    }

    public function testInvalidValueString(): void
    {
        $this->expectException(SanitizerException::class);

        Sanitizer::process('invalid', SS::boolean());
    }

    public function testInvalidValueArrayEmpty(): void
    {
        $this->expectException(SanitizerException::class);

        Sanitizer::process([], SS::boolean());
    }

    public function testInvalidValueArrayNotEmpty(): void
    {
        $this->expectException(SanitizerException::class);

        Sanitizer::process(['key' => 'value'], SS::boolean());
    }

    public function testOptional(): void
    {
        $this->assertEquals(true, Sanitizer::process(null, SS::boolean()->optional(true)));

        foreach ([
            'test',
            [],
            100
        ] as $case) {
            try {
                Sanitizer::process(null, SS::boolean()->optional($case));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(\InvalidArgumentException::class, $e);
                $this->assertEquals('Trying to set non-boolean default value for boolean schema.', $e->getMessage());
            }
        }
    }
}
