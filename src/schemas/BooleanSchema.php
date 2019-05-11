<?php

namespace sanitizer\schemas;

use sanitizer\SanitizerRuleException;
use sanitizer\SanitizerSchema;

class BooleanSchema extends SanitizerSchema {
    /**
     * @param mixed $input
     *
     * @return bool
     */
    public function process($input): ?bool {
        if (!isset($input) && $this->optional) return $this->default;

        $this->value = filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($this->value === null) throw new SanitizerRuleException('Expected boolean value.');

        return $this->value;
    }
}