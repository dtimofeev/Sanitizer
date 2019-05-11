<?php

namespace sanitizer\schemas;

use sanitizer\SanitizerSchema;

class StringSchema extends SanitizerSchema {
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
    public function process($input): ?string {
        if (!isset($input) && $this->optional) return $this->default;

        $this->value = filter_var($input, FILTER_SANITIZE_STRING);
        if (!\is_string($this->value)) throw new \InvalidArgumentException('Invalid string value.');

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
     * @param bool $left
     * @param bool $right
     *
     * @return StringSchema
     */
    public function trim(bool $left = true, bool $right = true): StringSchema {
        if (!$left && !$right) {
            throw new \InvalidArgumentException('Trying to define string trim rule with both left & right disabled.');
        }

        $this->rules[] = [
            'type'  => self::RULE_TRIM,
            'left'  => $left,
            'right' => $right,
        ];

        return $this;
    }

    /**
     * @param int $length
     * @param string $charset
     *
     * @return StringSchema
     */
    public function length(int $length, string $charset = 'UTF-8'): StringSchema {
        $this->rules[] = [
            'type'    => self::RULE_LENGTH,
            'min'     => $length,
            'max'     => $length,
            'charset' => $charset,
        ];

        return $this;
    }

    /**
     * @param int $length
     * @param string $charset
     *
     * @return StringSchema
     */
    public function min(int $length, string $charset = 'UTF-8'): StringSchema {
        $this->rules[] = [
            'type'    => self::RULE_LENGTH,
            'min'     => $length,
            'max'     => null,
            'charset' => $charset,
        ];

        return $this;
    }

    /**
     * @param int $length
     * @param string $charset
     *
     * @return StringSchema
     */
    public function max(int $length, string $charset = 'UTF-8'): StringSchema {
        $this->rules[] = [
            'type'    => self::RULE_LENGTH,
            'min'     => null,
            'max'     => $length,
            'charset' => $charset,
        ];

        return $this;
    }

    /**
     * @param array $values
     * @param bool $strict
     */
    private function processRuleOneOf(array $values, bool $strict): void {
        if (!\in_array($this->value, $values, $strict)) {
            $valuesString = implode('|', $values);

            throw new \InvalidArgumentException("Value should be one of ($valuesString)");
        }
    }

    /**
     * @param array $values
     * @param bool $strict
     */
    private function processRuleNotOneOf(array $values, bool $strict): void {
        if (\in_array($this->value, $values, $strict)) {
            $valuesString = implode('|', $values);

            throw new \InvalidArgumentException("Value should not be one of ($valuesString)");
        }
    }

    /**
     * @param string[] $values
     * @param bool $strict
     *
     * @return StringSchema
     */
    public function oneOf(array $values, bool $strict = true): StringSchema {
        $this->rules[] = [
            'type'   => self::RULE_ONE_OF,
            'values' => $values,
            'strict' => $strict,
        ];

        return $this;
    }

    /**
     * @param string[] $values
     * @param bool $strict
     *
     * @return StringSchema
     */
    public function notOneOf(array $values, bool $strict = true): StringSchema {
        $this->rules[] = [
            'type'   => self::RULE_NOT_ONE_OF,
            'values' => $values,
            'strict' => $strict,
        ];

        return $this;
    }

    /**
     * @return StringSchema
     */
    public function email(): StringSchema {
        $this->rules[] = [
            'type' => self::RULE_EMAIL,
        ];

        return $this;
    }

    /**
     * @param bool $v4
     * @param bool $v6
     *
     * @return StringSchema
     */
    public function ip(bool $v4 = true, bool $v6 = false): StringSchema {
        $this->rules[] = [
            'type' => self::RULE_IP,
            'v4'   => $v4,
            'v6'   => $v6,
        ];

        return $this;
    }

    /**
     * @param bool $httpsOnly
     *
     * @return StringSchema
     */
    public function url(bool $httpsOnly = false): StringSchema {
        $this->rules[] = [
            'type'      => self::RULE_URL,
            'httpsOnly' => $httpsOnly,
        ];

        return $this;
    }

    /**
     * @param string $pattern
     * @param string|null $name
     *
     * @return StringSchema
     */
    public function regex(string $pattern, string $name = null): StringSchema {
        $this->rules[] = [
            'type'    => self::RULE_REGEX,
            'pattern' => $pattern,
            'name'    => $name,
        ];

        return $this;
    }

    /**
     * @param bool $dash
     * @param bool $space
     *
     * @return StringSchema
     */
    public function alpha(bool $dash = false, bool $space = false): StringSchema {
        $this->regex('([' . ($space ? ' ' : '') . 'a-zA-Z' . ($dash ? '_-' : '') . '])+', 'alpha');

        return $this;
    }

    /**
     * @param bool $dash
     * @param bool $space
     *
     * @return StringSchema
     */
    public function alphaNum(bool $dash = false, bool $space = false): StringSchema {
        $this->regex('([' . ($space ? ' ' : '') . 'a-zA-Z0-9' . ($dash ? '_-' : '') . '])+', 'alphaNum');

        return $this;
    }

    /**
     * @param bool $left
     * @param bool $right
     */
    private function processRuleTrim(bool $left, bool $right): void {
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
    private function processRuleLength(?int $min, ?int $max, string $charset): void {
        $length = mb_strlen($this->value, $charset);
        if ($min !== null && $length < $min) throw new \InvalidArgumentException("String length is below expected minimum of $min characters.");
        if ($max !== null && $length > $max) throw new \InvalidArgumentException("String length is above expected maximum of $max characters.");
    }

    private function processRuleEmail(): void {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Not a valid email.');
        }
    }

    /**
     * @param bool $v4
     * @param bool $v6
     */
    private function processRuleIP(bool $v4, bool $v6): void {
        $flags = 0;
        if ($v4) $flags |= FILTER_FLAG_IPV4;
        if ($v6) $flags |= FILTER_FLAG_IPV6;

        if (!filter_var($this->value, FILTER_VALIDATE_IP, $flags)) {
            throw new \InvalidArgumentException('Not a valid IP address.');
        }
    }

    /**
     * @param bool $onlyHttps
     */
    private function processRuleURL(bool $onlyHttps): void {
        $filtered = filter_var($this->value, FILTER_VALIDATE_URL);
        if (!$filtered) throw new \InvalidArgumentException('Not a valid URL.');

        if ($onlyHttps) {
            $parsed = parse_url($filtered);
            if ($parsed['scheme'] !== 'https') throw new \InvalidArgumentException('URL is not HTTPS.');
        }
    }

    /**
     * @param string $pattern
     * @param string|null $name
     */
    private function processRuleRegex(string $pattern, ?string $name): void {
        $options = [
            'options' => ['regexp' => '/^' . $pattern . '$/'],
        ];
        if (filter_var($this->value, FILTER_VALIDATE_REGEXP, $options) === false) {
            throw new \InvalidArgumentException('Provided string does not match the ' . ($name ?? $pattern) . ' pattern.');
        }
    }
}