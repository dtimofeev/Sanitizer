<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Sanitizer\Sanitizer;
use Sanitizer\SanitizerSchema as SS;

class AliasesTest extends TestCase
{
    public function testExampleFromReadmeWithAliases(): void
    {
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

    public function testAliasErrors(): void
    {
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

    public function testAliasExtend(): void
    {
        SS::createAlias('integer|maxInt', SS::integer()->equals(PHP_INT_MAX));

        $modified = SS::alias('integer|maxInt')->optional(null);

        $this->assertNotEquals($modified, SS::alias('integer|maxInt'));
    }

    public function testAliasesPersistence(): void
    {
        SS::createAlias('persistentInt', SS::integer(), true);
        SS::createAlias('nonPersistentInt', SS::integer(), false);

        Sanitizer::process(1, SS::alias('nonPersistentInt'));

        try {
            SS::alias('nonPersistentInt');

            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals('Undefined alias with name nonPersistentInt.', $e->getMessage());
        }
    }

    public function testAliasDestroy(): void
    {
        SS::createAlias('persistent', SS::integer(), true);
        SS::createAlias('temporary', SS::integer(), true);

        SS::destroyAlias('persistent');
        SS::destroyAlias('temporary');

        try {
            SS::destroyAlias('persistent');

            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals('Undefined alias with name persistent.', $e->getMessage());
        }

        try {
            SS::destroyAlias('temporary');

            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals('Undefined alias with name temporary.', $e->getMessage());
        }
    }
}
