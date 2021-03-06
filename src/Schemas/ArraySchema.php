<?php

namespace Sanitizer\Schemas;

use Sanitizer\Sanitizer;
use Sanitizer\SanitizerException;
use Sanitizer\SanitizerSchema;

class ArraySchema extends SanitizerSchema
{
    private const RULE_SCHEMAS = 'schemas';
    private const RULE_SCALAR  = 'scalar';
    private const RULE_UNIQUE  = 'unique';
    private const RULE_EACH    = 'each';
    private const RULE_MIN     = 'min';
    private const RULE_MAX     = 'max';

    /**
     * @param mixed $input
     *
     * @return array|null
     * @throws SanitizerException
     */
    public function process($input): ?array
    {
        if ((!isset($input) || empty($input)) && $this->optional) return $this->default;
        if (!\is_array($input)) {
            throw new SanitizerException(SanitizerException::ERR_ARR_INVALID);
        }

        $this->value = $input;
        foreach ($this->rules as $rule) {
            switch ($rule['type']) {
                case self::RULE_SCHEMAS:
                    $this->processRuleSchema($rule['schema']);
                    break;
                case self::RULE_SCALAR:
                    $this->processRuleScalar();
                    break;
                case self::RULE_UNIQUE:
                    $this->processRuleUnique();
                    break;
                case self::RULE_EACH:
                    $this->processRuleEach($rule['schema']);
                    break;
                case self::RULE_MIN:
                    $this->processRuleMin($rule['min']);
                    break;
                case self::RULE_MAX:
                    $this->processRuleMax($rule['max']);
                    break;
                default:
                    break;
            }
        }

        return $this->value;
    }

    /**
     * @param null $default
     *
     * @return ArraySchema
     */
    public function optional($default = null): ArraySchema
    {
        if (isset($default) && !\is_array($default)) {
            throw new \InvalidArgumentException('Trying to set non-array default value for array schema.');
        }

        $self = $this->aliased ? clone $this : $this;
        $self->optional = true;
        $self->default = $default;

        return $self;
    }

    /**
     * @param array $schema
     *
     * @return ArraySchema
     */
    public function schema(array $schema): ArraySchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'   => self::RULE_SCHEMAS,
            'schema' => $schema,
        ];

        return $self;
    }

    public function scalar(): ArraySchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type' => self::RULE_SCALAR,
        ];

        return $self;
    }

    public function unique(): ArraySchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type' => self::RULE_UNIQUE,
        ];

        return $self;
    }

    /**
     * @param SanitizerSchema $schema
     *
     * @return ArraySchema
     */
    public function each(SanitizerSchema $schema): ArraySchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'   => self::RULE_EACH,
            'schema' => $schema,
        ];

        return $self;
    }

    /**
     * @param int $length
     *
     * @return ArraySchema
     */
    public function min(int $length): ArraySchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type' => self::RULE_MIN,
            'min'  => $length,
        ];

        return $self;
    }

    /**
     * @param int $length
     *
     * @return ArraySchema
     */
    public function max(int $length): ArraySchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type' => self::RULE_MAX,
            'max'  => $length,
        ];

        return $self;
    }

    /**
     * @param array $schema
     */
    private function processRuleSchema(array $schema): void
    {
        foreach ($schema as $key => $rules) {
            $this->value[$key] = Sanitizer::process($this->value[$key] ?? null, $rules, $key);
        }
    }

    /**
     * @param SanitizerSchema $schema
     */
    private function processRuleEach(SanitizerSchema $schema): void
    {
        foreach ($this->value as $key => &$value) {
            $value = Sanitizer::process($value, $schema, $key);
        }
    }

    private function processRuleScalar(): void
    {
        if (array_values($this->value) !== $this->value) {
            throw new SanitizerException(SanitizerException::ERR_ARR_SCALAR);
        }
    }

    private function processRuleUnique(): void
    {
        if (\count(array_unique($this->value)) !== \count($this->value)) {
            throw new SanitizerException(SanitizerException::ERR_ARR_UNIQUE);
        }
    }

    private function processRuleMin(int $min): void
    {
        if (\count($this->value) < $min) {
            throw new SanitizerException(SanitizerException::ERR_ARR_MIN, ['min' => $min]);
        }
    }

    private function processRuleMax(int $max): void
    {
        if (\count($this->value) > $max) {
            throw new SanitizerException(SanitizerException::ERR_ARR_MAX, ['max' => $max]);
        }
    }
}