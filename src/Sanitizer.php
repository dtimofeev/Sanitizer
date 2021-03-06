<?php

namespace Sanitizer;

class Sanitizer
{
    /**
     * @param mixed $input
     * @param SanitizerSchema $schema
     * @param string $field
     *
     * @return mixed
     * @throws SanitizerException
     */
    public static function process($input, SanitizerSchema $schema, string $field = '$')
    {
        try {
            $result = $schema->process($input);
        } catch (SanitizerException $e) {
            throw new SanitizerException($e->getCode(), $e->getParams(), $field, $e);
        }

        // Destroy non-persistent aliases when the whole validation is complete.
        SanitizerSchema::destroyNonPersistentAliases();

        return $result;
    }
}