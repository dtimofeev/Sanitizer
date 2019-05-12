<?php

namespace sanitizer\schemas;

use sanitizer\Sanitizer;
use sanitizer\SanitizerException;
use sanitizer\SanitizerSchema;
use sanitizer\SanitizerSchema as SS;

class DecimalSchema extends SanitizerSchema {
    private const RULE_MIN = 'min';
    private const RULE_MAX = 'max';

    /**
     * @param mixed $input
     *
     * @return mixed
     * @throws SanitizerException
     */
    public function process($input): string {
        if (!isset($input) && $this->optional) return $this->default;

        if (!is_numeric($input) || filter_var($input, FILTER_VALIDATE_FLOAT) === false) {
            throw new SanitizerException(SanitizerException::ERR_DEC_INVALID);
        }

        try {
            $this->value = Sanitizer::process($input, SS::string()->trim()->regex('([0-9.])+'));
        } catch (SanitizerException $e) {
            throw new SanitizerException(SanitizerException::ERR_DEC_INVALID);
        }

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
     * @param null $default
     *
     * @return DecimalSchema
     */
    public function optional($default = null): DecimalSchema {
        if (isset($default) && !\is_numeric($default)) {
            throw new \InvalidArgumentException('Trying to set non-decimal default value for decimal schema.');
        }

        $self = $this->aliased ? clone $this : $this;
        $self->optional = true;
        $self->default = $default;

        return $self;
    }

    /**
     * @param int $value
     *
     * @return DecimalSchema
     */
    public function min(int $value): DecimalSchema {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'  => self::RULE_MIN,
            'value' => $value,
        ];

        return $self;
    }

    /**
     * @param int $value
     *
     * @return DecimalSchema
     */
    public function max(int $value): DecimalSchema {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'  => self::RULE_MAX,
            'value' => $value,
        ];

        return $self;
    }

    /**
     * @param string|float|int $min
     */
    private function processRuleMin($min): void {
        if ($this->value < $min) throw new SanitizerException(SanitizerException::ERR_DEC_MIN, ['value' => $min]);
    }

    /**
     * @param string|float|int $max
     */
    private function processRuleMax($max): void {
        if ($this->value > $max) throw new SanitizerException(SanitizerException::ERR_DEC_MAX, ['value' => $max]);
    }
}