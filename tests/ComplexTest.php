<?php

namespace sanitizer\tests;

use PHPUnit\Framework\TestCase;
use sanitizer\Sanitizer;
use sanitizer\SanitizerSchema;
use sanitizer\SanitizerSchema as SS;
use sanitizer\schemas\BooleanSchema;

class ComplexTest extends TestCase {
    public function testExampleFromReadme(): void {
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

        $processed = Sanitizer::process($input, SS::arr()->schema([
            'id'        => SS::integer()->min(1),
            'nickname'  => SS::string()->alphaNum(),
            'email'     => SS::string()->email(),
            'ip'        => SS::string()->ip(),
            'sex'       => SS::string()->optional('na')->oneOf(['male', 'female', 'na']),
            'favMovies' => SS::arr()->each(
                SS::arr()->schema([
                    'title'   => SS::string()->trim()->max(200),
                    'release' => SS::date('Y-m-d'),
                    'tags'    => SS::arr()->unique()->each(
                        SS::string()->alphaNum()
                    ),
                ])
            ),
        ]));

        $this->assertEquals(array_merge($input, [
            'sex' => 'na',
        ]), $processed);
    }

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

    public function testErrorPath(): void {
        $input = [
            'favMovies' => [
                ['title' => 'Doctor Strange', 'tags' => ['marvel ', 'magic']],
            ],
        ];

        try {
            Sanitizer::process($input, SS::arr()->schema([
                'favMovies' => SS::arr()->each(
                    SS::arr()->schema([
                        'title' => SS::string()->trim()->max(200),
                        'tags'  => SS::arr()->unique()->each(
                            SS::string()->alphaNum()
                        ),
                    ])
                ),
            ]));
        } catch (\Exception $e) {
            $this->assertEquals('Validation for field $.favMovies.tags has failed. Provided string does not match the alphaNum pattern.',
                $e->getMessage());
        }
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
}
