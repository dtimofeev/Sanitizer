<?php

namespace sanitizer\schemas;

use sanitizer\SanitizerSchema;

class IntegerSchema extends SanitizerSchema {
    private const RULE_MIN = 'min';
    private const RULE_MAX = 'max';

    /**
     * @param mixed $input
     *
     * @return int
     */
    public function process($input): ?int {
        if (!isset($input) && $this->optional) return $this->default;
        if (!\is_numeric($input)) throw new \InvalidArgumentException('Invalid integer value.');

        $this->value = filter_var($input, FILTER_VALIDATE_INT);
        if ($this->value === false) throw new \InvalidArgumentException('Invalid integer value.');

        foreach ($this->rules as $rule) {
            switch ($rule['type']) {
                case self::RULE_MIN:
                    $this->processRuleMin($rule['value']);
                    break;
                case self::RULE_MAX:
                    $this->processRuleMax($rule['value']);
                    break;
                default:
                    break;
            }
        }

        return $this->value;
    }

    /**
     * @param int $value
     *
     * @return IntegerSchema
     */
    public function min(int $value): IntegerSchema {
        $this->rules[] = [
            'type'  => self::RULE_MIN,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * @param int $value
     *
     * @return IntegerSchema
     */
    public function max(int $value): IntegerSchema {
        $this->rules[] = [
            'type'  => self::RULE_MAX,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * @param int $min
     * @param int $max
     *
     * @return IntegerSchema
     */
    public function between(int $min, int $max): IntegerSchema {
        if ($max < $min) throw new \InvalidArgumentException('Trying to define integer between validation with max < min.');

        $this->rules[] = [
            'type'  => self::RULE_MIN,
            'value' => $min,
        ];
        $this->rules[] = [
            'type'  => self::RULE_MAX,
            'value' => $max,
        ];

        return $this;
    }

    /**
     * @param int $min
     */
    private function processRuleMin(int $min): void {
        if ($this->value < $min) throw new \InvalidArgumentException('Value is less than expected minimum of ' . $min);
    }

    /**
     * @param int $min
     */
    private function processRuleMax(int $min): void {
        if ($this->value > $min) throw new \InvalidArgumentException('Value is more than expected maximum of ' . $min);
    }
}