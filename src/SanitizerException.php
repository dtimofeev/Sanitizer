<?php

namespace sanitizer;


class SanitizerException extends \Exception {
    public const ERR_ARR_INVALID       = 100;
    public const ERR_ARR_SCALAR        = 101;
    public const ERR_ARR_UNIQUE        = 102;
    public const ERR_ARR_MIN           = 103;
    public const ERR_ARR_MAX           = 104;
    public const ERR_BOOL_INVALID      = 200;
    public const ERR_DATE_INVALID      = 300;
    public const ERR_DATE_BEFORE       = 301;
    public const ERR_DATE_AFTER        = 302;
    public const ERR_INT_INVALID       = 400;
    public const ERR_INT_ONE_OF        = 401;
    public const ERR_INT_NOT_ONE_OF    = 402;
    public const ERR_INT_MIN           = 403;
    public const ERR_INT_MAX           = 404;
    public const ERR_INT_EQUALS        = 405;
    public const ERR_INT_NOT_EQUALS    = 406;
    public const ERR_STR_INVALID       = 500;
    public const ERR_STR_MIN           = 501;
    public const ERR_STR_MAX           = 502;
    public const ERR_STR_ONE_OF        = 503;
    public const ERR_STR_NOT_ONE_OF    = 504;
    public const ERR_STR_EMAIL         = 505;
    public const ERR_STR_IP            = 506;
    public const ERR_STR_URL           = 507;
    public const ERR_STR_URL_NOT_HTTPS = 508;
    public const ERR_STR_REGEX         = 509;
    public const ERR_DEC_INVALID       = 600;
    public const ERR_DEC_MIN           = 601;
    public const ERR_DEC_MAX           = 602;

    public static $baseMessage = 'Validation for field {{path}} has failed.';

    /** @var array */
    public static $messages = [
        self::ERR_ARR_INVALID       => 'Invalid array value.',
        self::ERR_ARR_SCALAR        => 'Array is not scalar.',
        self::ERR_ARR_UNIQUE        => 'Array is not unique.',
        self::ERR_ARR_MIN           => 'Array length is below expected minimum of {{min}} items.',
        self::ERR_ARR_MAX           => 'Array length is above expected minimum of {{max}} items.',
        self::ERR_BOOL_INVALID      => 'Invalid boolean value.',
        self::ERR_DATE_INVALID      => 'Invalid date value.',
        self::ERR_DATE_BEFORE       => 'Date is not before {{date}}.',
        self::ERR_DATE_AFTER        => 'Date is not after {{date}}.',
        self::ERR_INT_INVALID       => 'Invalid integer value.',
        self::ERR_INT_ONE_OF        => 'Integer is not one of {{values}}.',
        self::ERR_INT_NOT_ONE_OF    => 'Integer is one of {{values}}.',
        self::ERR_INT_MIN           => 'Value is below the minimum of {{value}}.',
        self::ERR_INT_MAX           => 'Value is above the maximum of {{value}}.',
        self::ERR_INT_EQUALS        => 'Value equals {{value}}.',
        self::ERR_INT_NOT_EQUALS    => 'Value does not equal {{value}}.',
        self::ERR_STR_INVALID       => 'Invalid string value.',
        self::ERR_STR_MIN           => 'String length is below expected minimum of {{min}} characters.',
        self::ERR_STR_MAX           => 'String length is above expected maximum of {{max}} characters.',
        self::ERR_STR_ONE_OF        => 'Value is not one of {{values}}.',
        self::ERR_STR_NOT_ONE_OF    => 'Value is one of {{values}}.',
        self::ERR_STR_EMAIL         => 'Not a valid email address.',
        self::ERR_STR_IP            => 'Not a valid IP address.',
        self::ERR_STR_URL           => 'Not a valid URL.',
        self::ERR_STR_URL_NOT_HTTPS => 'URL is not HTTPS.',
        self::ERR_STR_REGEX         => 'Provided string does not match the {{pattern}} pattern.',
        self::ERR_DEC_INVALID       => 'Invalid decimal.',
        self::ERR_DEC_MIN           => 'Value is below the minimum of {{value}}.',
        self::ERR_DEC_MAX           => 'Value is above the maximum of {{value}}.',
    ];

    /** @var string[] */
    private $chain = [];

    /** @var array */
    private $params;

    /**
     * SanitizerException constructor.
     *
     * @param int $code
     * @param array $params
     * @param string|null $field
     * @param \Throwable|null $previous
     */
    public function __construct(int $code, array $params = [], string $field = null, \Throwable $previous = null) {
        if ($previous instanceof SanitizerException) {
            $this->chain = $previous->getChain();
        }

        if ($field) $this->chain[] = $field;
        $this->params = $params;

        $message = '';
        if ($this->chain) {
            $message = self::$baseMessage . ' ';
            $this->params['path'] = implode('.', array_reverse($this->chain));
        }
        $message .= self::$messages[$code] ?? 'Unknown error type.';

        // Replace placeholders
        foreach ($this->params as $key => $value) {
            if (\is_array($value)) $value = implode('|', $value);

            $message = str_replace('{{' . $key . '}}', $value, $message);
        }

        parent::__construct($message, $code);
    }

    /**
     * @return array
     */
    public function getChain(): array {
        return $this->chain;
    }

    /**
     * @return array
     */
    public function getParams(): array {
        return $this->params ?? [];
    }
}