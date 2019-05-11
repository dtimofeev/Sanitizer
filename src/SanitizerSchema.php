<?php

namespace sanitizer;

use sanitizer\schemas\BooleanSchema;

abstract class SanitizerSchema {
    /** @var mixed */
    protected $default;

    /** @var bool */
    protected $optional;

    /** @var mixed */
    protected $value;

    /** @var array */
    protected $rules = [];

    /**
     * @param null $default
     *
     * @return self
     */
    public function optional($default = null): self {
        $this->optional = true;
        $this->default = $default;

        return $this;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     */
    abstract public function process($input);

    /**
     * @return BooleanSchema
     */
    final public static function boolean(): BooleanSchema {
        return new BooleanSchema();
    }
}