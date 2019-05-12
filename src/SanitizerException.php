<?php

namespace sanitizer;


class SanitizerException extends \Exception {
    /** @var string[] */
    private $chain = [];

    /** @var string */
    private $ruleError;

    /**
     * SanitizerException constructor.
     *
     * @param string $message
     * @param string|null $field
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, string $field = null, \Throwable $previous = null) {
        if (!$previous) {
            $this->ruleError = $message;
        } elseif ($previous instanceof SanitizerException) {
            $this->chain = $previous->getChain();
            $this->ruleError = $previous->getRuleError();
        }

        if ($field) $this->chain[] = $field;

        $message = '';
        if ($this->chain) {
            $message = 'Validation for field ' . implode('.', array_reverse($this->chain)) . ' has failed. ';
        }
        $message .= $this->ruleError;

        parent::__construct($message);
    }

    /**
     * @return array
     */
    public function getChain(): array {
        return $this->chain;
    }

    /**
     * @return string
     */
    public function getRuleError(): string {
        return $this->ruleError;
    }
}