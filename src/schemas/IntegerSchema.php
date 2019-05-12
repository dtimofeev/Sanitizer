<?php

namespace sanitizer\schemas;

use sanitizer\SanitizerException;
use sanitizer\SanitizerSchema;

class IntegerSchema extends SanitizerSchema {
    private const RULE_MIN        = 'min';
    private const RULE_MAX        = 'max';
    private const RULE_EQUALS     = 'equals';
    private const RULE_NOT        = 'not';
    private const RULE_ONE_OF     = 'oneOf';
    private const RULE_NOT_ONE_OF = 'notOneOf';

    /**
     * @param mixed $input
     *
     * @return int
     */
    public function process($input): ?int {
        if (!isset($input) && $this->optional) return $this->default;
        if (!\is_numeric($input)) throw new SanitizerException(SanitizerException::ERR_INT_INVALID);

        $this->value = filter_var($input, FILTER_VALIDATE_INT);
        if ($this->value === false) throw new SanitizerException(SanitizerException::ERR_INT_INVALID);

        foreach ($this->rules as $rule) {
            switch ($rule['type']) {
                case self::RULE_MIN:
                    $this->processRuleMin($rule['value']);
                    break;
                case self::RULE_MAX:
                    $this->processRuleMax($rule['value']);
                    break;
                case self::RULE_EQUALS:
                    $this->processRuleEquals($rule['expected']);
                    break;
                case self::RULE_NOT:
                    $this->processRuleNot($rule['unexpected']);
                    break;
                case self::RULE_ONE_OF:
                    $this->processRuleOneOf($rule['values']);
                    break;
                case self::RULE_NOT_ONE_OF:
                    $this->processRuleNotOneOf($rule['values']);
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
     * @return IntegerSchema
     */
    public function optional($default = null): IntegerSchema {
        if (isset($default) && !\is_int($default)) {
            throw new \InvalidArgumentException('Trying to set non-integer default value for integer schema.');
        }

        $this->checkAliased();
        $this->optional = true;
        $this->default = $default;

        return $this;
    }

    /**
     * @param int $value
     *
     * @return IntegerSchema
     */
    public function min(int $value): IntegerSchema {
        $this->checkAliased();
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
        $this->checkAliased();
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

        $this->checkAliased();
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
     * @param int $expected
     *
     * @return IntegerSchema
     */
    public function equals(int $expected): IntegerSchema {
        $this->checkAliased();
        $this->rules[] = [
            'type'     => self::RULE_EQUALS,
            'expected' => $expected,
        ];

        return $this;
    }

    /**
     * @param int $unexpected
     *
     * @return IntegerSchema
     */
    public function not(int $unexpected): IntegerSchema {
        $this->checkAliased();
        $this->rules[] = [
            'type'       => self::RULE_NOT,
            'unexpected' => $unexpected,
        ];

        return $this;
    }

    /**
     * @param int[] $values
     *
     * @return IntegerSchema
     */
    public function oneOf(array $values): IntegerSchema {
        if (empty($values)) {
            throw new \InvalidArgumentException('Values for "oneOf" rule should not be an empty array.');
        }

        $this->checkAliased();
        $this->rules[] = [
            'type'   => self::RULE_ONE_OF,
            'values' => $values,
        ];

        return $this;
    }

    /**
     * @param int[] $values
     *
     * @return IntegerSchema
     */
    public function notOneOf(array $values): IntegerSchema {
        if (empty($values)) {
            throw new \InvalidArgumentException('Values for "notOneOf" rule should not be an empty array.');
        }

        $this->checkAliased();
        $this->rules[] = [
            'type'   => self::RULE_NOT_ONE_OF,
            'values' => $values,
        ];

        return $this;
    }

    /**
     * @param int $min
     */
    private function processRuleMin(int $min): void {
        if ($this->value < $min) throw new SanitizerException(SanitizerException::ERR_INT_MIN, ['value' => $min]);
    }

    /**
     * @param int $max
     */
    private function processRuleMax(int $max): void {
        if ($this->value > $max) throw new SanitizerException(SanitizerException::ERR_INT_MAX, ['value' => $max]);
    }

    /**
     * @param int $expected
     */
    private function processRuleEquals(int $expected): void {
        if ($this->value !== $expected) {
            throw new SanitizerException(SanitizerException::ERR_INT_EQUALS, ['value' => $expected]);
        }
    }

    /**
     * @param int $unexpected
     */
    private function processRuleNot(int $unexpected): void {
        if ($this->value === $unexpected) {
            throw new SanitizerException(SanitizerException::ERR_INT_NOT_EQUALS, ['value' => $unexpected]);
        }
    }

    /**
     * @param array $values
     */
    private function processRuleOneOf(array $values): void {
        if (!\in_array($this->value, $values, true)) {
            throw new SanitizerException(SanitizerException::ERR_INT_ONE_OF , ['values' => $values]);
        }
    }

    /**
     * @param array $values
     */
    private function processRuleNotOneOf(array $values): void {
        if (\in_array($this->value, $values, true)) {
            throw new SanitizerException(SanitizerException::ERR_INT_NOT_ONE_OF, ['values' => $values]);
        }
    }
}