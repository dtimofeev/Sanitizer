<?php

namespace sanitizer\tests;

use PHPUnit\Framework\TestCase;
use sanitizer\Sanitizer;

class BootstrapTest extends TestCase {
    public function testSanitizerInstanceOfItself(): void {
        $this->assertInstanceOf(
            Sanitizer::class,
            new Sanitizer()
        );
    }
}
