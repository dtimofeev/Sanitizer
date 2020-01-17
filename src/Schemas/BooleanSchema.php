<?php

namespace Sanitizer\Schemas;

use Sanitizer\SanitizerException;
use Sanitizer\SanitizerSchema;

class BooleanSchema extends SanitizerSchema
{
    /**
     * @param mixed $input
     *
     * @return bool
     */
    public function process($input): ?bool
    {
        if (!isset($input) && $this->optional) return $this->default;

        $this->value = filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($this->value === null) {
            throw new SanitizerException(SanitizerException::ERR_BOOL_INVALID);
        }

        return $this->value;
    }

    /**
     * @param null $default
     *
     * @return BooleanSchema
     */
    public function optional($default = null): BooleanSchema
    {
        if (isset($default) && !\is_bool($default)) {
            throw new \InvalidArgumentException('Trying to set non-boolean default value for boolean schema.');
        }

        $self = $this->aliased ? clone $this : $this;
        $self->optional = true;
        $self->default = $default;

        return $self;
    }
}