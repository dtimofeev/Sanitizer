<?php

namespace Sanitizer;

use Sanitizer\Schemas\ArraySchema;
use Sanitizer\Schemas\BooleanSchema;
use Sanitizer\Schemas\DateSchema;
use Sanitizer\Schemas\DecimalSchema;
use Sanitizer\Schemas\IntegerSchema;
use Sanitizer\Schemas\StringSchema;

abstract class SanitizerSchema
{
    /** @var mixed */
    protected $default;

    /** @var bool */
    protected $optional;

    /** @var int|string|bool|array|\DateTime */
    protected $value;

    /** @var array */
    protected $rules = [];

    /** @var bool */
    protected $aliased = false;

    /** @var SanitizerSchema[] */
    private static $aliases = [];

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
    final public static function boolean(): BooleanSchema
    {
        return new BooleanSchema();
    }

    /**
     * @return IntegerSchema
     */
    final public static function integer(): IntegerSchema
    {
        return new IntegerSchema();
    }

    /**
     * @return StringSchema
     */
    final public static function string(): StringSchema
    {
        return new StringSchema();
    }

    /**
     * @return ArraySchema
     */
    final public static function arr(): ArraySchema
    {
        return new ArraySchema();
    }

    /**
     * @param string $format
     *
     * @return DateSchema
     */
    final public static function date(string $format): DateSchema
    {
        return new DateSchema($format);
    }

    /**
     * @return DecimalSchema
     */
    final public static function decimal(): DecimalSchema
    {
        return new DecimalSchema();
    }

    /**
     * @param string $name
     *
     * @return ArraySchema|BooleanSchema|DateSchema|IntegerSchema|StringSchema
     */
    final public static function alias(string $name): SanitizerSchema
    {
        if (!isset(self::$aliases[$name])) {
            throw new \InvalidArgumentException("Undefined alias with name $name.");
        }

        return self::$aliases[$name]['schema'];
    }

    /**
     * @param string $name
     * @param SanitizerSchema $schema
     * @param bool $persistent
     */
    final public static function createAlias(string $name, SanitizerSchema $schema, bool $persistent = false): void
    {
        if (isset(self::$aliases[$name])) {
            throw new \InvalidArgumentException("Schema alias with name $name is already set.");
        }

        self::$aliases[$name] = [
            'schema'     => $schema,
            'persistent' => $persistent,
        ];

        $schema->aliased = true;
    }

    /**
     * @param string $name
     */
    final public static function destroyAlias(string $name): void
    {
        if (!isset(self::$aliases[$name])) {
            throw new \InvalidArgumentException("Undefined alias with name $name.");
        }

        unset(self::$aliases[$name]);
    }

    final public static function destroyNonPersistentAliases(): void
    {
        foreach (self::$aliases as $index => &$alias) {
            if (!$alias['persistent']) unset(self::$aliases[$index]);
        }
    }

    /**
     * Used to remove aliased property for cloned object.
     */
    final public function __clone()
    {
        $this->aliased = false;
    }
}