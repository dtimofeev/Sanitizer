<?php

namespace sanitizer\schemas;

use sanitizer\Sanitizer;
use sanitizer\SanitizerSchema;

class ArraySchema extends SanitizerSchema {
    private const RULE_SCHEMAS = 'schemas';
    private const RULE_SCALAR  = 'scalar';
    private const RULE_UNIQUE  = 'unique';
    private const RULE_EACH    = 'each';

    public function process($input): ?array {
        if (!isset($input) && $this->optional) return $this->default;
        if (!\is_array($input)) throw new \InvalidArgumentException('Invalid array value.');

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
    public function processRuleSchema(array $schema): void {
        foreach ($schema as $key => $rules) {
            $this->value[$key] = Sanitizer::process($this->value[$key] ?? null, $rules, $key);
        }
    }

    /**
     * @param SanitizerSchema $schema
     */
    public function processRuleEach(SanitizerSchema $schema): void {
        foreach ($this->value as $key => &$value) {
            $value = Sanitizer::process($value, $schema, $key);
        }
    }

    public function processRuleScalar(): void {
        if (array_values($this->value) !== $this->value) {
            throw new \InvalidArgumentException('Array is not scalar.');
        }
    }

    public function processRuleUnique(): void {
        if (\count(array_unique($this->value)) !== \count($this->value)) {
            throw new \InvalidArgumentException('Values are not unique.');
        }
    }
}