<?php

namespace sanitizer;


class SanitizerException extends \Exception {
    /** @var string[] */
    private $chain = [];

    /** @var string */
    private $ruleError;

    public function __construct(string $field, \Throwable $previous = null) {
        if ($previous) {
            if ($previous instanceof SanitizerException) {
                $this->chain = $previous->getChain();
                $this->ruleError = $previous->getRuleError();
            } else {
                $this->ruleError = $previous->getMessage();
            }
        }
        $this->chain[] = $field;

        parent::__construct('Validation for field ' . implode('.', array_reverse($this->chain)) . ' has failed. ' . $this->ruleError);
    }

    public function getChain(): array {
        return $this->chain;
    }

    public function getRuleError(): string {
        return $this->ruleError;
    }
}