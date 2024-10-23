<?php

namespace dmstr\jsoneditor\helpers;

use \Opis\JsonSchema\Errors\ErrorFormatter;
use \Opis\JsonSchema\Validator;
use \Opis\JsonSchema\Helper;

class JsonEditorValidatorHelper
{
    /**
     * Validate JSON data against a schema and return formatted errors.
     *
     * @param array $json The JSON string to be validated.
     * @param array $jsonSchema The JSON schema to validate against.
     * @param array $filters An array of filter classes.
     * @return array The array of validation errors, or an empty array if valid.
     */
    public function validateJson($json, $jsonSchema, $filters = [])
    {
        $validator = new Validator();
        $validator->setMaxErrors(9999);
        $filterResolver = $validator->parser()->getFilterResolver();

        foreach ($filters as $filterClass) {
            if (class_exists($filterClass)) {
                $filter = new $filterClass();
                $filterResolver->registerCallable($filter->getType(), $filter->getName(), $filter->getCallable());
            } else {
                throw new \InvalidArgumentException("Filter class $filterClass does not exist.");
            }
        }

        $result = $validator->validate(Helper::toJSON($json), Helper::toJSON($jsonSchema));

        if ($result->isValid()) {
            return [];
        } else {
            $formatter = new ErrorFormatter();
            $error = $result->error();
            $errors = $formatter->formatFlat( $error, function ($error) use ($formatter) {
                return [
                    'keyword' => $error->keyword(),
                    'path' => $formatter->formatErrorKey($error),
                    'message' => implode(', ', $formatter->format($error, false))
                ];
            });

            return $errors;
        }
    }
}