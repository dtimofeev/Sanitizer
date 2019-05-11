<?php

namespace sanitizer;

class Sanitizer {
    /**
     * @param mixed $input
     * @param SanitizerSchema $schema
     * @param string $field
     *
     * @return mixed
     */
    public static function process($input, SanitizerSchema $schema, string $field = '$') {
        try {
            return $schema->process($input);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Validation for field $field has failed. " . $e->getMessage());
        }
    }
}