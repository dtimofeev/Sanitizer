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
        if (!is_scalar($input)) throw new SanitizerException(SanitizerException::ERR_DATE_INVALID);

        $this->value = \DateTime::createFromFormat($this->format, $input);
        if (!$this->value || $this->value->format($this->format) !== $input) {
            throw new SanitizerException(SanitizerException::ERR_DATE_INVALID);
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
        if (isset($default)) {
            if (!\is_string($default)) {
                throw new \InvalidArgumentException('Trying to set non-date default value for date schema.');
            }

            $parsedDefault = \DateTime::createFromFormat($this->format, $default);
            if (!$parsedDefault || $parsedDefault->format($this->format) !== $default) {
                throw new \InvalidArgumentException('Trying to set non-date default value for date schema.');
            }
        }

        $self = $this->aliased ? clone $this : $this;
        $self->optional = true;
        $self->default = $default;

        return $self;
    }

    /**
     * @param string $date
     *
     * @return DateSchema
     */
    public function before(string $date): DateSchema {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
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
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type' => self::RULE_AFTER,
            'date' => $date,
        ];

        return $self;
    }

    /**
     * @param string $date
     */
    private function processRuleBefore(string $date): void {
        if ($this->value >= new \DateTime($date)) {
            throw new SanitizerException(SanitizerException::ERR_DATE_BEFORE, ['date' => $date]);
        }
    }

    /**
     * @param string $date
     */
    private function processRuleAfter(string $date): void {
        if ($this->value <= new \DateTime($date)) {
            throw new SanitizerException(SanitizerException::ERR_DATE_AFTER, ['date' => $date]);
        }
    }
}