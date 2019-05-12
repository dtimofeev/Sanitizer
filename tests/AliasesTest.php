<?php

namespace sanitizer\tests;

use PHPUnit\Framework\TestCase;
use sanitizer\Sanitizer;
use sanitizer\SanitizerException;
use sanitizer\SanitizerSchema;
use sanitizer\SanitizerSchema as SS;
use sanitizer\schemas\BooleanSchema;

class AliasesTest extends TestCase {
    public function testExampleFromReadmeWithAliases(): void {
        $input = [
            'id'        => 111,
            'nickname'  => 'userNickname',
            'email'     => 'user@mailprovider.org',
            'ip'        => '127.0.0.1',
            'favMovies' => [
                ['title' => 'Doctor Strange', 'release' => '2019-01-01', 'tags' => ['marvel', 'magic']],
                ['title' => 'Star Wars', 'release' => '1998-05-23', 'tags' => ['space']],
            ],
        ];

        SS::createAlias('alphaNum', SS::string()->alphaNum());

        $processed = Sanitizer::process($input, SS::arr()->schema([
            'id'        => SS::integer()->min(1),
            'nickname'  => SS::alias('alphaNum'),
            'email'     => SS::string()->email(),
            'ip'        => SS::string()->ip(),
            'sex'       => SS::string()->optional('na')->oneOf(['male', 'female', 'na']),
            'favMovies' => SS::arr()->each(
                SS::arr()->schema([
                    'title'   => SS::string()->trim()->max(200),
                    'release' => SS::date('Y-m-d'),
                    'tags'    => SS::arr()->unique()->each(SS::alias('alphaNum')),
                ])
            ),
        ]));

        $this->assertEquals(array_merge($input, [
            'sex' => 'na',
        ]), $processed);
    }

    public function testAliasErrors(): void {
        SS::createAlias('integer', SS::integer());

        try {
            SS::createAlias('integer', SS::integer());

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Schema alias with name integer is already set.', $e->getMessage());
        }

        try {
            SS::alias('missingSchema');

            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Undefined alias with name missingSchema.', $e->getMessage());
        }
    }

    public function testAliasModification(): void {
        $error = 'Change of aliased schemas is not allowed.';

        SS::createAlias('array', SS::arr());
        try { SS::alias('array')->optional(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('array')->unique(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('array')->scalar(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('array')->each(SS::string()); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('array')->schema(['k' => SS::string()]); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }

        SS::createAlias('boolean', SS::boolean());
        try { SS::alias('boolean')->optional(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }

        SS::createAlias('date', SS::date('Y-m-d H:i:s'));
        try { SS::alias('date')->optional(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('date')->before('2019-01-01 00:00:00'); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('date')->after('2019-01-01 00:00:00'); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }

        SS::createAlias('string', SS::string());
        try { SS::alias('string')->optional(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->trim(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->length(1); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->min(1); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->max(1); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->oneOf([1]); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->notOneOf([1]); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->email(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->ip(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->url(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->regex('a-Z'); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->alpha(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('string')->alphaNum(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }

        SS::createAlias('int', SS::integer());
        try { SS::alias('int')->optional(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('int')->min(1); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('int')->max(1); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('int')->between(1, 2); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('int')->equals(1); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('int')->not(1); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('int')->oneOf([1]); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('int')->notOneOf([1]); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }

        SS::createAlias('dec', SS::decimal());
        try { SS::alias('dec')->optional(); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('dec')->min(1); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
        try { SS::alias('dec')->max(1); } catch (\Exception $e) { $this->assertEquals($error, $e->getMessage()); }
    }
}
