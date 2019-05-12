<?php

namespace sanitizer\tests;

use PHPUnit\Framework\TestCase;
use sanitizer\Sanitizer;
use sanitizer\SanitizerException;
use sanitizer\SanitizerSchema;
use sanitizer\SanitizerSchema as SS;
use sanitizer\schemas\StringSchema;

class StringTest extends TestCase {
    public function testIsInstanceOfSanitizerSchema(): void {
        $this->assertInstanceOf(
            SanitizerSchema::class,
            new StringSchema()
        );
    }

    /**
     * @param mixed $value
     * @param mixed|null $expected
     *
     * @dataProvider validCasesProvider
     */
    public function testValidValues($value, $expected = null): void {
        $this->assertEquals($expected ?? $value, Sanitizer::process($value, SS::string()));
    }

    /**
     * @return array
     */
    public function validCasesProvider(): array {
        return [
            'Value string "test"' => ['test'],
            'Value string "123"'  => ['123'],
            'Value string "true"' => ['true'],
            'Value integer "123"' => [123, '123'],
            'Value float "3.14"'  => [3.14, '3.14'],
        ];
    }

    public function testRuleTrim(): void {
        $this->assertEquals('test  ', Sanitizer::process('  test  ', SS::string()->trim(true, false)));
        $this->assertEquals('  test', Sanitizer::process('  test  ', SS::string()->trim(false, true)));
        $this->assertEquals('test', Sanitizer::process('  test  ', SS::string()->trim()));

        try {
            Sanitizer::process('  test  ', SS::string()->trim(false, false));
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Trying to define string trim rule with both left & right disabled.', $e->getMessage());
        }
    }

    public function testRuleLength(): void {
        Sanitizer::process('test', SS::string()->length(4));

        try {
            Sanitizer::process('test', SS::string()->length(5));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }

        try {
            Sanitizer::process('test1', SS::string()->length(4));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }
    }

    public function testRuleMin(): void {
        Sanitizer::process('test', SS::string()->min(4));

        try {
            Sanitizer::process('test', SS::string()->min(5));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }
    }

    public function testRuleMax(): void {
        Sanitizer::process('test', SS::string()->max(4));

        try {
            Sanitizer::process('test', SS::string()->max(3));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }
    }

    public function testRuleOneOf(): void {
        Sanitizer::process('success', SS::string()->oneOf(['success', 'error']));

        try {
            Sanitizer::process('unknown', SS::string()->oneOf(['success', 'error']));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }
    }

    public function testRuleNotOneOf(): void {
        Sanitizer::process('unknown', SS::string()->notOneOf(['success', 'error']));

        try {
            Sanitizer::process('success', SS::string()->notOneOf(['success', 'error']));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }
    }

    public function testRuleEmail(): void {
        Sanitizer::process('firstname.lastname@mailprovider.org', SS::string()->email());

        try {
            Sanitizer::process('@mailprovider.org', SS::string()->email());

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }

        try {
            Sanitizer::process('firstname.lastname@mailprovider', SS::string()->email());

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }
    }

    public function testRuleIp(): void {
        Sanitizer::process('127.0.0.1', SS::string()->ip());
        Sanitizer::process('255.255.255.255', SS::string()->ip());
        Sanitizer::process('1200:0000:AB00:1234:0000:2552:7777:1313', SS::string()->ip(true, true));
        Sanitizer::process('1200:0000:AB00:1234:0000:2552:7777:1313', SS::string()->ip(false, true));

        try {
            Sanitizer::process('255.255.255.256', SS::string()->ip());

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }

        try {
            Sanitizer::process('1200::AB00:1234::2552:7777:1313', SS::string()->ip(true, true));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }
    }

    public function testRuleUrl(): void {
        Sanitizer::process('http://site.org', SS::string()->url());
        Sanitizer::process('https://site.org', SS::string()->url());
        Sanitizer::process('https://site.org', SS::string()->url(true));
        Sanitizer::process('https://site.org?param1=1&param2=2', SS::string()->url());

        try {
            Sanitizer::process('http://site.org', SS::string()->url(true));

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(SanitizerException::class, $e);
        }
    }

    public function testRuleAlpha(): void {
        Sanitizer::process('test', SS::string()->alpha());
        Sanitizer::process('test-', SS::string()->alpha(true));
        Sanitizer::process('test_', SS::string()->alpha(true));
        Sanitizer::process('test ', SS::string()->alpha(true, true));

        foreach ([
            ['value' => 'test1', 'dash' => false, 'space' => false],
            ['value' => 'test-', 'dash' => false, 'space' => false],
            ['value' => 'test_', 'dash' => false, 'space' => false],
            ['value' => 'test ', 'dash' => false, 'space' => false],
            ['value' => 'test ', 'dash' => true, 'space' => false],
            ['value' => 'test-', 'dash' => false, 'space' => true],
        ] as $case) {
            try {
                Sanitizer::process($case['value'], SS::string()->alpha($case['dash'], $case['space']));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
            }
        }
    }

    public function testRuleAlphaNum(): void {
        Sanitizer::process('test', SS::string()->alphaNum());
        Sanitizer::process('test1', SS::string()->alphaNum());
        Sanitizer::process('test1', SS::string()->alphaNum());
        Sanitizer::process('test1-', SS::string()->alphaNum(true));
        Sanitizer::process('test1_', SS::string()->alphaNum(true));
        Sanitizer::process('test1 ', SS::string()->alphaNum(true, true));

        foreach ([
            ['value' => 'test1-', 'dash' => false, 'space' => false],
            ['value' => 'test1_', 'dash' => false, 'space' => false],
            ['value' => 'test1 ', 'dash' => false, 'space' => false],
            ['value' => 'test1 ', 'dash' => true, 'space' => false],
            ['value' => 'test1-', 'dash' => false, 'space' => true],
        ] as $case) {
            try {
                Sanitizer::process($case['value'], SS::string()->alphaNum($case['dash'], $case['space']));

                $this->fail();
            } catch (\Exception $e) {
                $this->assertInstanceOf(SanitizerException::class, $e);
            }
        }
    }

    public function testRuleRegex(): void {
        $this->assertEquals('#d3d3d3', Sanitizer::process('#d3d3d3', SS::string()->regex('#[a-f0-9]{6}')));
    }
}
