<?php

namespace dmstr\jsoneditor\actions;

use Yii;
use yii\base\Action;
use dmstr\jsoneditor\helpers\JsonEditorValidatorHelper;

class JsonEditorValidationAction extends Action
{
    protected function getFilters()
    {
        return [];
    }

    public static function getValidationAction()
    {
        return '/frontend/default/validate-json';
    }

    public static function getActionName()
    {
        $name = self::getValidationAction();
        $parts = explode('/', $name);
        return end($parts);
    }

    public function run()
    {
        $rawBody = Yii::$app->request->getRawBody();
        $postData = json_decode($rawBody, true);
        $json = $postData['json'] ?? null;
        $jsonSchema = $postData['jsonSchema'] ?? null;

        if (!$json || !$jsonSchema) {
            return $this->controller->asJson([
                'status' => 'error',
                'message' => 'Invalid request. Both value and schema are required.'
            ]);
        }

        $jsonValidatorHelper = new JsonEditorValidatorHelper();
        $validationErrors = $jsonValidatorHelper->validateJson($json, $jsonSchema, $this->getFilters());

        if (!empty($validationErrors)) {
            return $this->controller->asJson([
                'status' => 'error',
                'errors' => $validationErrors
            ]);
        }

        return $this->controller->asJson([
            'status' => 'success',
            'message' => 'Validation passed.'
        ]);
    }
}
