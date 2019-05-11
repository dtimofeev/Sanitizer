<?php

namespace sanitizer\tests;

use PHPUnit\Framework\TestCase;
use sanitizer\Sanitizer;
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
                $this->assertInstanceOf(\InvalidArgumentException::class, $e);
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
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
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
                $this->assertInstanceOf(\InvalidArgumentException::class, $e);
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
}