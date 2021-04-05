<?php

namespace Ang3\Component\Odoo\DBAL\Schema;

use Ang3\Component\Odoo\Client;
use Ang3\Component\Odoo\DBAL\Query\OrmQuery;

class Schema
{
    public const IR_MODEL = 'ir.model';
    public const IR_MODEL_FIELDS = 'ir.model.fields';
    public const IR_MODEL_FIELD_SELECTION = 'ir.model.fields.selection';

    private $client;

    /**
     * @var string[]
     */
    private $modelNames = [];

    /**
     * @var Model[]
     */
    private $loadedModels = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getModel(string $modelName): Model
    {
        if (!$this->hasModel($modelName)) {
            throw SchemaException::modelNotFound($modelName);
        }

        if (!isset($this->loadedModels[$modelName])) {
            $expr = $this->client->expr();
            $modelData = $this->client->call(self::IR_MODEL, OrmQuery::SEARCH_READ, $expr->normalizeDomains($expr->eq('model', $modelName)));
            $this->loadedModels[$modelName] = $this->createModel($modelData[0]);
        }

        return $this->loadedModels[$modelName];
    }

    public function hasModel(string $modelName): bool
    {
        return in_array($modelName, $this->getModelNames());
    }

    /**
     * Gets all model names.
     *
     * @return string[]
     */
    public function getModelNames(): array
    {
        if (!$this->modelNames) {
            $this->modelNames = array_column($this->client->call(self::IR_MODEL, OrmQuery::SEARCH_READ, [[]], [
                'fields' => ['model'],
            ]), 'model');
        }

        return $this->modelNames;
    }

    /**
     * @internal
     */
    private function createModel(array $modelData): Model
    {
        $expr = $this->client->expr();
        $fields = $this->client->call(
            self::IR_MODEL_FIELDS,
            OrmQuery::SEARCH_READ,
            $expr->normalizeDomains($expr->eq('model_id', $modelData['id']))
        );

        foreach ($fields as $key => $fieldData) {
            $choices = [];
            $selectionsIds = array_filter($fieldData['selection_ids'] ?? []);

            if (!empty($selectionsIds)) {
                $choices = $this->client->call(
                    self::IR_MODEL_FIELD_SELECTION,
                    OrmQuery::SEARCH_READ,
                    $expr->normalizeDomains($expr->eq('field_id', $fieldData['id']))
                );

                foreach ($choices as $index => $choice) {
                    if (is_array($choice)) {
                        $choices[$index] = new Choice((string) $choice['name'], $choice['value'], (int) $choice['id']);
                    }
                }
            } elseif (!empty($fieldData['selection'])) {
                if (preg_match_all('#^\[\s*(\(\'(\w+)\'\,\s*\'(\w+)\'\)\s*\,?\s*)*\s*\]$#', trim($fieldData['selection']), $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        if (isset($match[2], $match[3])) {
                            $choices[] = new Choice((string) $match[3], $match[2]);
                        }
                    }
                }
            }

            if ($choices) {
                $fieldData['selection'] = $choices;
            }

            $fields[$key] = new Field($fieldData);
        }

        return new Model($this, $modelData, $fields);
    }
}
