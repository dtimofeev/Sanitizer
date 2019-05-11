<?php

namespace sanitizer\tests;

use PHPUnit\Framework\TestCase;
use sanitizer\Sanitizer;
use sanitizer\SanitizerSchema;
use sanitizer\SanitizerSchema as SS;
use sanitizer\schemas\BooleanSchema;

class BooleanTest extends TestCase {
    public function testIsInstanceOfSanitizerSchema(): void {
        $this->assertInstanceOf(
            SanitizerSchema::class,
            new BooleanSchema()
        );
    }

    public function testValueTrue(): void {
        $this->assertTrue((new Sanitizer())->process(true, SS::boolean()));
    }

    public function testValueFalse(): void {
        $this->assertFalse((new Sanitizer())->process(false, SS::boolean()));
    }

    public function testValueTruthlyInteger(): void {
        $this->assertTrue((new Sanitizer())->process(1, SS::boolean()));
    }

    public function testValueFalsyInteger(): void {
        $this->assertFalse((new Sanitizer())->process(0, SS::boolean()));
    }

    public function testDefault(): void {
        $this->assertNull((new Sanitizer())->process(null, SS::boolean()->optional()));
    }

    public function testDefaultExplicitTrue(): void {
        $this->assertTrue((new Sanitizer())->process(null, SS::boolean()->optional(true)));
    }

    public function testDefaultExplicitFalse(): void {
        $this->assertFalse((new Sanitizer())->process(null, SS::boolean()->optional(false)));
    }

    public function testInvalidValueString(): void {
        $this->expectException(\InvalidArgumentException::class);

        (new Sanitizer())->process('invalid', SS::boolean());
    }

    public function testInvalidValueArrayEmpty(): void {
        $this->expectException(\InvalidArgumentException::class);

        (new Sanitizer())->process([], SS::boolean());
    }

    public function testInvalidValueArrayNotEmpty(): void {
        $this->expectException(\InvalidArgumentException::class);

        (new Sanitizer())->process(['key' => 'value'], SS::boolean());
    }
}
