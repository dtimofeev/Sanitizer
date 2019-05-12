<?php

namespace sanitizer\tests;

use PHPUnit\Framework\TestCase;
use sanitizer\Sanitizer;
use sanitizer\SanitizerException;
use sanitizer\SanitizerSchema;
use sanitizer\SanitizerSchema as SS;
use sanitizer\schemas\DateSchema;

class DateTest extends TestCase {
    public function testIsInstanceOfSanitizerSchema(): void {
        $this->assertInstanceOf(
            SanitizerSchema::class,
            new DateSchema('Y-m-d H:i:s')
        );
    }

    public function testDateCreate(): void {
        foreach ([
            ['value' => '2019-01-01 10:00:02', 'format' => 'Y-m-d H:i:s'],
            ['value' => '2019-01-01 10:00', 'format' => 'Y-m-d H:i'],
            ['value' => '2019-01-01 10', 'format' => 'Y-m-d H'],
            ['value' => '2019-01-01', 'format' => 'Y-m-d'],
            ['value' => '2019-01', 'format' => 'Y-m'],
            ['value' => '2019', 'format' => 'Y'],
        ] as $case) {
            $this->assertEquals($case['value'], Sanitizer::process($case['value'], SS::date($case['format'])));
        }

        foreach ([
            ['value' => 'test'],
            ['value' => '20190'],
            ['value' => true],
            ['value' => 1],
            ['value' => []],
        ] as $case) {
            try {
                Sanitizer::process($case['value'], SS::date('Y-m-d H:i:s'));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
            }
        }
    }

    public function testRuleBefore(): void {
        $input = '2019-01-01 10:00:02';
        $before = '2019-01-01 10:00:03';

        $this->assertEquals($input, Sanitizer::process($input, SS::date('Y-m-d H:i:s')->before($before)));

        foreach ([
            '2019-01-01 10:00:02',
            '2019-01-01 10:00:01'
        ] as $invalidBefore) {
            try {
                Sanitizer::process($input, SS::date('Y-m-d H:i:s')->before($invalidBefore));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
            }
        }
    }

    public function testRuleAfter(): void {
        $input = '2019-01-01 10:00:02';
        $after = '2019-01-01 10:00:01';

        $this->assertEquals($input, Sanitizer::process($input, SS::date('Y-m-d H:i:s')->after($after)));

        foreach ([
            '2019-01-01 10:00:02',
            '2019-01-01 10:00:03'
        ] as $invalidBefore) {
            try {
                Sanitizer::process($input, SS::date('Y-m-d H:i:s')->after($invalidBefore));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
            }
        }
    }
}
