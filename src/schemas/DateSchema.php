<?php

namespace sanitizer\schemas;

use sanitizer\SanitizerException;
use sanitizer\SanitizerSchema;

class DateSchema extends SanitizerSchema {
    private const RULE_BEFORE = 'before';
    private const RULE_AFTER  = 'after';

    /** @var string */
    private $format;

    /**
     * DateSchema constructor.
     *
     * @param string $format
     */
    public function __construct(string $format) {
        $this->format = $format;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     * @throws SanitizerException
     */
    public function process($input): string {
        if (!isset($input) && $this->optional) return $this->default;
        if (!is_scalar($input)) throw new SanitizerException('Not a valid date.');

        $this->value = \DateTime::createFromFormat($this->format, $input);
        if (!$this->value || $this->value->format($this->format) !== $input) {
            throw new SanitizerException('Not a valid date.');
        }

        foreach ($this->rules as $rule) {
            switch ($rule['type']) {
                case self::RULE_BEFORE:
                    $this->processRuleBefore($rule['date']);
                    break;
                case self::RULE_AFTER:
                    $this->processRuleAfter($rule['date']);
                    break;
                default:
                    break;
            }
        }

        return $this->value->format($this->format);
    }

    /**
     * @param null $default
     *
     * @return DateSchema
     */
    public function optional($default = null): DateSchema {
        if (!\is_string($default)) {
            throw new \InvalidArgumentException('Trying to set non-date default value for date schema.');
        }

        $parsedDefault = \DateTime::createFromFormat($this->format, $default);
        if (
            isset($default) &&
            (!$parsedDefault || $parsedDefault->format($this->format) !== $default)
        ) {
            throw new \InvalidArgumentException('Trying to set non-date default value for date schema.');
        }

        $this->optional = true;
        $this->default = $default;

        return $this;
    }

    /**
     * @param string $date
     *
     * @return DateSchema
     */
    public function before(string $date): DateSchema {
        $this->rules[] = [
            'type' => self::RULE_BEFORE,
            'date' => $date,
        ];

        return $this;
    }

    /**
     * @param string $date
     *
     * @return DateSchema
     */
    public function after(string $date): DateSchema {
        $this->rules[] = [
            'type' => self::RULE_AFTER,
            'date' => $date,
        ];

        return $this;
    }

    /**
     * @param string $date
     */
    private function processRuleBefore(string $date): void {
        $beforeDate = (new \DateTime($date));
        if ($this->value >= $beforeDate) throw new SanitizerException("Date should be before $date.");
    }

    /**
     * @param string $date
     */
    private function processRuleAfter(string $date): void {
        $afterDate = new \DateTime($date);
        if ($this->value <= $afterDate) throw new SanitizerException("Date should be after $date.");
    }
}