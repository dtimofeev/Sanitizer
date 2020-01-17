<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Sanitizer\Sanitizer;
use Sanitizer\SanitizerException;
use Sanitizer\SanitizerSchema as SS;

class ComplexTest extends TestCase
{
    public function testExampleFromReadme(): void
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

    public function testErrorPath(): void
    {
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

            $this->fail();
        } catch (SanitizerException $e) {
            $expectedError = 'Validation for field $.favMovies.0.tags.0 has failed. Provided string does not match the alphaNum pattern.';
            $this->assertEquals($expectedError, $e->getMessage());
            $this->assertEquals('$.favMovies.0.tags.0', $e->getFieldPath());
            $this->assertEquals('$.favMovies.*.tags.*', $e->getFieldPath(true));
            $this->assertEquals('$.favMovies.z.tags.z', $e->getFieldPath(true, 'z'));
        }
    }

    public function testCustomErrorMessages(): void
    {
        SanitizerException::$messages[SanitizerException::ERR_INT_EQUALS] .= '__MODIFIED__';

        try {
            Sanitizer::process(0, SS::integer()->equals(1));
        } catch (\Exception $e) {
            $this->assertContains('__MODIFIED__', $e->getMessage());
        }
    }
}
