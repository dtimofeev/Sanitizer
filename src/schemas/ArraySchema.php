<?php

namespace sanitizer\schemas;

use sanitizer\Sanitizer;
use sanitizer\SanitizerException;
use sanitizer\SanitizerSchema;

class ArraySchema extends SanitizerSchema {
    private const RULE_SCHEMAS = 'schemas';
    private const RULE_SCALAR  = 'scalar';
    private const RULE_UNIQUE  = 'unique';
    private const RULE_EACH    = 'each';

    /**
     * @param mixed $input
     *
     * @return array|null
     * @throws SanitizerException
     */
    public function process($input): ?array {
        if ((!isset($input) || empty($input)) && $this->optional) return $this->default;
        if (!\is_array($input)) throw new SanitizerException('Invalid array value.');

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
    public function optional($default = null): ArraySchema {
        if (isset($default) && !\is_array($default)) {
            throw new \InvalidArgumentException('Trying to set non-array default value for array schema.');
        }

        $this->optional = true;
        $this->default = $default;

        return $this;
    }

    /**
     * @param array $schema
     *
     * @return ArraySchema
     */
    public function schema(array $schema): ArraySchema {
        $this->rules[] = [
            'type'   => self::RULE_SCHEMAS,
            'schema' => $schema,
        ];

        return $this;
    }

    public function scalar(): ArraySchema {
        $this->rules[] = [
            'type' => self::RULE_SCALAR,
        ];

        return $this;
    }

    public function unique(): ArraySchema {
        $this->rules[] = [
            'type' => self::RULE_UNIQUE,
        ];

        return $this;
    }

    /**
     * @param SanitizerSchema $schema
     *
     * @return ArraySchema
     */
    public function each(SanitizerSchema $schema): ArraySchema {
        $this->rules[] = [
            'type'   => self::RULE_EACH,
            'schema' => $schema,
        ];

        return $this;
    }

    /**
     * @param array $schema
     */
    private function processRuleSchema(array $schema): void {
        foreach ($schema as $key => $rules) {
            $this->value[$key] = Sanitizer::process($this->value[$key] ?? null, $rules, $key);
        }
    }

    /**
     * @param SanitizerSchema $schema
     */
    private function processRuleEach(SanitizerSchema $schema): void {
        foreach ($this->value as $key => &$value) {
            $value = Sanitizer::process($value, $schema, $key);
        }
    }

    private function processRuleScalar(): void {
        if (array_values($this->value) !== $this->value) {
            throw new SanitizerException('Array is not scalar.');
        }
    }

    private function processRuleUnique(): void {
        if (\count(array_unique($this->value)) !== \count($this->value)) {
            throw new SanitizerException('Values are not unique.');
        }
    }
}