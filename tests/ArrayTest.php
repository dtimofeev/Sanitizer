<?php

namespace sanitizer\tests;

use PHPUnit\Framework\TestCase;
use sanitizer\Sanitizer;
use sanitizer\SanitizerException;
use sanitizer\SanitizerSchema;
use sanitizer\SanitizerSchema as SS;
use sanitizer\schemas\ArraySchema;

class ArrayTest extends TestCase {
    public function testIsInstanceOfSanitizerSchema(): void {
        $this->assertInstanceOf(
            SanitizerSchema::class,
            new ArraySchema()
        );
    }

    public function testBasicAssocArray(): void {
        $input = [
            'key' => 'value',
        ];

        $this->assertEquals($input, Sanitizer::process($input, SS::arr()));

        foreach (['string', true, 1] as $case) {
            try {
                Sanitizer::process($case, SS::arr());

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
                $this->assertEquals(SanitizerException::ERR_ARR_INVALID, $e->getCode());
            }
        }
    }

    public function testOptional(): void {
        $valid = [
            'key' => 'value'
        ];

        $this->assertEquals($valid, Sanitizer::process([], SS::arr()->optional($valid)));
        $this->assertEquals($valid, Sanitizer::process(null, SS::arr()->optional($valid)));

        foreach ([
            'test',
            true,
        ] as $case) {
            try {
                Sanitizer::process($valid, SS::arr()->optional($case));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(\InvalidArgumentException::class, $e);
                $this->assertEquals('Trying to set non-array default value for array schema.', $e->getMessage());
            }
        }
    }

    public function testRuleScalar(): void {
        foreach ([
            [1, 1, 1],
            [1, 'test', true],
        ] as $case) {
            $this->assertEquals($case, Sanitizer::process($case, SS::arr()->scalar()));
        }

        try {
            Sanitizer::process([
                'key' => 'value',
            ], SS::arr()->scalar());

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_ARR_SCALAR, $e->getCode());
        }
    }

    public function testRuleUnique(): void {
        foreach ([
            [1, 2, 3],
            ['test', 'test2'],
            [true, false],
            [1, 'test', null],
        ] as $case) {
            $this->assertEquals($case, Sanitizer::process($case, SS::arr()->unique()));
        }

        foreach ([
            [1, 2, 1],
            ['test', 'test2', 'test'],
            [true, false, true],
            [1, true, 'test', null],
        ] as $case) {
            try {
                Sanitizer::process($case, SS::arr()->unique());

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
                $this->assertEquals(SanitizerException::ERR_ARR_UNIQUE, $e->getCode());
            }
        }
    }

    public function testRuleEach(): void {
        $input = [
            'test1' => 1,
            'test2' => 2,
        ];

        $this->assertEquals($input, Sanitizer::process($input, SS::arr()->each(
            SS::integer()->between(1, 10)
        )));
    }

    public function testRuleSchema(): void {
        $input = [
            'ip'       => '127.0.0.1',
            'nickname' => 'user nickname-1',
            'age'      => 23,
            'logged'   => true,
        ];

        $this->assertEquals($input, Sanitizer::process($input, SS::arr()->schema([
            'ip'       => SS::string()->ip(),
            'nickname' => SS::string()->alphaNum(true, true),
            'age'      => SS::integer()->min(18),
            'logged'   => SS::boolean(),
        ])));
    }

    public function testRuleMin(): void {
        $input = ['key' => 'value', 'key2' => 'value2'];
        $this->assertEquals($input, Sanitizer::process($input, SS::arr()->min(1)));

        try {
            Sanitizer::process($input, SS::arr()->min(3));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_ARR_MIN, $e->getCode());
        }
    }

    public function testRuleMax(): void {
        $input = ['key' => 'value', 'key2' => 'value2'];
        $this->assertEquals($input, Sanitizer::process($input, SS::arr()->max(3)));

        try {
            Sanitizer::process($input, SS::arr()->max(1));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
            $this->assertEquals(SanitizerException::ERR_ARR_MAX, $e->getCode());
        }
    }
}
