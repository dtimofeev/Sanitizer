<?php

namespace sanitizer;

use sanitizer\schemas\ArraySchema;
use sanitizer\schemas\BooleanSchema;
use sanitizer\schemas\DateSchema;
use sanitizer\schemas\IntegerSchema;
use sanitizer\schemas\StringSchema;

abstract class SanitizerSchema {
    /** @var mixed */
    protected $default;

    /** @var bool */
    protected $optional;

    /** @var int|string|bool|array|\DateTime */
    protected $value;

    /** @var array */
    protected $rules = [];

    /**
     * @param null $default
     *
     * @return ArraySchema|BooleanSchema|DateSchema|IntegerSchema|StringSchema
     */
    abstract public function optional($default = null);

    /**
     * @param mixed $input
     *
     * @return mixed
     * @throws SanitizerException
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

    /**
     * @param string $format
     *
     * @return DateSchema
     */
    final public static function date(string $format): DateSchema {
        return new DateSchema($format);
    }
}