<?php

namespace Sanitizer\Schemas;

use Sanitizer\SanitizerException;
use Sanitizer\SanitizerSchema;

class StringSchema extends SanitizerSchema
{
    private const RULE_TRIM       = 'trim';
    private const RULE_LENGTH     = 'length';
    private const RULE_REGEX      = 'regex';
    private const RULE_ONE_OF     = 'oneOf';
    private const RULE_NOT_ONE_OF = 'notOneOf';
    private const RULE_EMAIL      = 'email';
    private const RULE_IP         = 'ip';
    private const RULE_URL        = 'url';

    /**
     * @param mixed $input
     *
     * @return string
     */
    public function process($input): ?string
    {
        if (!isset($input) && $this->optional) return $this->default;

        $this->value = filter_var($input, FILTER_SANITIZE_STRING);
        if (!\is_string($this->value)) throw new SanitizerException(SanitizerException::ERR_STR_INVALID);

        foreach ($this->rules as $rule) {
            switch ($rule['type']) {
                case self::RULE_TRIM:
                    $this->processRuleTrim($rule['left'], $rule['right']);
                    break;
                case self::RULE_LENGTH:
                    $this->processRuleLength($rule['min'], $rule['max'], $rule['charset']);
                    break;
                case self::RULE_ONE_OF:
                    $this->processRuleOneOf($rule['values'], $rule['strict']);
                    break;
                case self::RULE_NOT_ONE_OF:
                    $this->processRuleNotOneOf($rule['values'], $rule['strict']);
                    break;
                case self::RULE_EMAIL:
                    $this->processRuleEmail();
                    break;
                case self::RULE_IP:
                    $this->processRuleIP($rule['v4'], $rule['v6']);
                    break;
                case self::RULE_URL:
                    $this->processRuleURL($rule['httpsOnly']);
                    break;
                case self::RULE_REGEX:
                    $this->processRuleRegex($rule['pattern'], $rule['name']);
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
     * @return StringSchema
     */
    public function optional($default = null): StringSchema
    {
        if (isset($default) && !\is_string($default)) {
            throw new \InvalidArgumentException('Trying to set non-string default value for string schema.');
        }

        $self = $this->aliased ? clone $this : $this;
        $self->optional = true;
        $self->default = $default;

        return $self;
    }

    /**
     * @param bool $left
     * @param bool $right
     *
     * @return StringSchema
     */
    public function trim(bool $left = true, bool $right = true): StringSchema
    {
        if (!$left && !$right) {
            throw new \InvalidArgumentException('Trying to define string trim rule with both left & right disabled.');
        }

        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'  => self::RULE_TRIM,
            'left'  => $left,
            'right' => $right,
        ];

        return $self;
    }

    /**
     * @param int $length
     * @param string $charset
     *
     * @return StringSchema
     */
    public function length(int $length, string $charset = 'UTF-8'): StringSchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'    => self::RULE_LENGTH,
            'min'     => $length,
            'max'     => $length,
            'charset' => $charset,
        ];

        return $self;
    }

    /**
     * @param int $length
     * @param string $charset
     *
     * @return StringSchema
     */
    public function min(int $length, string $charset = 'UTF-8'): StringSchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'    => self::RULE_LENGTH,
            'min'     => $length,
            'max'     => null,
            'charset' => $charset,
        ];

        return $self;
    }

    /**
     * @param int $length
     * @param string $charset
     *
     * @return StringSchema
     */
    public function max(int $length, string $charset = 'UTF-8'): StringSchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'    => self::RULE_LENGTH,
            'min'     => null,
            'max'     => $length,
            'charset' => $charset,
        ];

        return $self;
    }

    /**
     * @param string[] $values
     * @param bool $strict
     *
     * @return StringSchema
     */
    public function oneOf(array $values, bool $strict = true): StringSchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'   => self::RULE_ONE_OF,
            'values' => $values,
            'strict' => $strict,
        ];

        return $self;
    }

    /**
     * @param string[] $values
     * @param bool $strict
     *
     * @return StringSchema
     */
    public function notOneOf(array $values, bool $strict = true): StringSchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'   => self::RULE_NOT_ONE_OF,
            'values' => $values,
            'strict' => $strict,
        ];

        return $self;
    }

    /**
     * @return StringSchema
     */
    public function email(): StringSchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type' => self::RULE_EMAIL,
        ];

        return $self;
    }

    /**
     * @param bool $v4
     * @param bool $v6
     *
     * @return StringSchema
     */
    public function ip(bool $v4 = true, bool $v6 = false): StringSchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type' => self::RULE_IP,
            'v4'   => $v4,
            'v6'   => $v6,
        ];

        return $self;
    }

    /**
     * @param bool $httpsOnly
     *
     * @return StringSchema
     */
    public function url(bool $httpsOnly = false): StringSchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'      => self::RULE_URL,
            'httpsOnly' => $httpsOnly,
        ];

        return $self;
    }

    /**
     * @param string $pattern
     * @param string|null $name
     *
     * @return StringSchema
     */
    public function regex(string $pattern, string $name = null): StringSchema
    {
        $self = $this->aliased ? clone $this : $this;
        $self->rules[] = [
            'type'    => self::RULE_REGEX,
            'pattern' => $pattern,
            'name'    => $name,
        ];

        return $self;
    }

    /**
     * @param bool $dash
     * @param bool $space
     *
     * @return StringSchema
     */
    public function alpha(bool $dash = false, bool $space = false): StringSchema
    {
        return $this->regex('([' . ($space ? ' ' : '') . 'a-zA-Z' . ($dash ? '_-' : '') . '])+', 'alpha');
    }

    /**
     * @param bool $dash
     * @param bool $space
     *
     * @return StringSchema
     */
    public function alphaNum(bool $dash = false, bool $space = false): StringSchema
    {
        return $this->regex('([' . ($space ? ' ' : '') . 'a-zA-Z0-9' . ($dash ? '_-' : '') . '])+', 'alphaNum');
    }

    /**
     * @param bool $left
     * @param bool $right
     */
    private function processRuleTrim(bool $left, bool $right): void
    {
        if ($left && $right) {
            $this->value = trim($this->value);
        } elseif ($left) {
            $this->value = ltrim($this->value);
        } else {
            $this->value = rtrim($this->value);
        }
    }

    /**
     * @param int|null $min
     * @param int|null $max
     * @param string $charset
     */
    private function processRuleLength(?int $min, ?int $max, string $charset): void
    {
        $length = mb_strlen($this->value, $charset);
        if ($min !== null && $length < $min) {
            throw new SanitizerException(SanitizerException::ERR_STR_MIN, ['min' => $min]);
        }
        if ($max !== null && $length > $max) {
            throw new SanitizerException(SanitizerException::ERR_STR_MAX, ['max' => $max]);
        }
    }

    /**
     * @param array $values
     * @param bool $strict
     */
    private function processRuleOneOf(array $values, bool $strict): void
    {
        if (!\in_array($this->value, $values, $strict)) {
            throw new SanitizerException(SanitizerException::ERR_STR_ONE_OF, ['values' => $values]);
        }
    }

    /**
     * @param array $values
     * @param bool $strict
     */
    private function processRuleNotOneOf(array $values, bool $strict): void
    {
        if (\in_array($this->value, $values, $strict)) {
            throw new SanitizerException(SanitizerException::ERR_STR_NOT_ONE_OF, ['values' => $values]);
        }
    }

    private function processRuleEmail(): void
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new SanitizerException(SanitizerException::ERR_STR_EMAIL);
        }
    }

    /**
     * @param bool $v4
     * @param bool $v6
     */
    private function processRuleIP(bool $v4, bool $v6): void
    {
        $flags = 0;
        if ($v4) $flags |= FILTER_FLAG_IPV4;
        if ($v6) $flags |= FILTER_FLAG_IPV6;

        if (!filter_var($this->value, FILTER_VALIDATE_IP, $flags)) {
            throw new SanitizerException(SanitizerException::ERR_STR_IP);
        }
    }

    /**
     * @param bool $onlyHttps
     */
    private function processRuleURL(bool $onlyHttps): void
    {
        $filtered = filter_var($this->value, FILTER_VALIDATE_URL);
        if (!$filtered) throw new SanitizerException(SanitizerException::ERR_STR_URL);

        if ($onlyHttps) {
            $parsed = parse_url($filtered);
            if ($parsed['scheme'] !== 'https') throw new SanitizerException(SanitizerException::ERR_STR_URL_NOT_HTTPS);
        }
    }

    /**
     * @param string $pattern
     * @param string|null $name
     */
    private function processRuleRegex(string $pattern, ?string $name): void
    {
        $options = [
            'options' => ['regexp' => '/^' . $pattern . '$/'],
        ];
        if (filter_var($this->value, FILTER_VALIDATE_REGEXP, $options) === false) {
            throw new SanitizerException(SanitizerException::ERR_STR_REGEX, ['pattern' => $name ?? $pattern]);
        }
    }
}