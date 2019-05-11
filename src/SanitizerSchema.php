<?php

namespace sanitizer;

use sanitizer\schemas\ArraySchema;
use sanitizer\schemas\BooleanSchema;
use sanitizer\schemas\IntegerSchema;
use sanitizer\schemas\StringSchema;

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

    /**
     * @return IntegerSchema
     */
    final public static function integer(): IntegerSchema {
        return new IntegerSchema();
    }

    /**
     * @return StringSchema
     */
    final public static function string(): StringSchema {
        return new StringSchema();
    }

    /**
     * @return ArraySchema
     */
    final public static function arr(): ArraySchema {
        return new ArraySchema();
    }
}