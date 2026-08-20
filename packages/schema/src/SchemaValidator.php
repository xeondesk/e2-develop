<?php
declare(strict_types=1);

namespace Nexo\Schema;

use Nexo\Domain\Result;

final class SchemaValidator
{
    public function __construct(
        private FieldRegistry $fieldRegistry
    ) {}

    public function validate(Schema $schema): Result
    {
        $errors = [];

        if (count($schema->getFields()) === 0) {
            $errors[] = 'Schema must have at least one field';
        }

        $hasPrimary = false;
        foreach ($schema->getFields() as $field) {
            if ($field->isPrimary()) {
                if ($hasPrimary) {
                    $errors[] = 'Schema can have only one primary field';
                }
                $hasPrimary = true;
            }

            if (!$this->fieldRegistry->hasType($field->type()->value)) {
                $errors[] = "Unknown field type: {$field->type()->value}";
            }

            if ($field->isPrimary() && $field->isNullable()) {
                $errors[] = "Primary field {$field->name()} cannot be nullable";
            }
        }

        if (!$hasPrimary) {
            $errors[] = 'Schema must have a primary field';
        }

        foreach ($schema->getIndexes() as $indexName => $index) {
            foreach ($index['columns'] as $column) {
                if (!$schema->hasField($column)) {
                    $errors[] = "Index {$indexName} references unknown field: {$column}";
                }
            }
        }

        if (!empty($errors)) {
            return Result::err(new \InvalidArgumentException(implode('; ', $errors)));
        }

        return Result::ok($schema);
    }

    public function validateData(Schema $schema, array $data): Result
    {
        $errors = [];

        foreach ($schema->getFields() as $field) {
            $name = $field->name();
            $value = $data[$name] ?? null;

            if (!array_key_exists($name, $data)) {
                if (!$field->isNullable() && !$field->hasDefault() && !$field->isPrimary()) {
                    $errors[] = "Field {$name} is required";
                }
                continue;
            }

            $validation = $this->validateFieldValue($field, $value);
            if ($validation->isErr()) {
                $errors[] = $validation->unwrapErr()->getMessage();
            }
        }

        if (!empty($errors)) {
            return Result::err(new \InvalidArgumentException(implode('; ', $errors)));
        }

        return Result::ok($data);
    }

    private function validateFieldValue(FieldDefinition $field, mixed $value): Result
    {
        if ($value === null) {
            return $field->isNullable() ? Result::ok(null) : Result::err(new \InvalidArgumentException("Field {$field->name()} cannot be null"));
        }

        return match ($field->type()) {
            FieldType::STRING, FieldType::TEXT, FieldType::SLUG, FieldType::EMAIL, FieldType::URL, FieldType::PASSWORD => 
                is_string($value) ? Result::ok($value) : Result::err(new \InvalidArgumentException("Field {$field->name()} must be a string")),
            
            FieldType::INTEGER, FieldType::BIGINT => 
                is_int($value) ? Result::ok($value) : Result::err(new \InvalidArgumentException("Field {$field->name()} must be an integer")),
            
            FieldType::FLOAT, FieldType::DECIMAL => 
                is_numeric($value) ? Result::ok($value) : Result::err(new \InvalidArgumentException("Field {$field->name()} must be a number")),
            
            FieldType::BOOLEAN => 
                is_bool($value) ? Result::ok($value) : Result::err(new \InvalidArgumentException("Field {$field->name()} must be a boolean")),
            
            FieldType::DATE, FieldType::DATETIME, FieldType::TIMESTAMP => 
                $value instanceof \DateTimeInterface ? Result::ok($value) : Result::err(new \InvalidArgumentException("Field {$field->name()} must be a DateTime")),
            
            FieldType::UUID => 
                (is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) 
                    ? Result::ok($value) 
                    : Result::err(new \InvalidArgumentException("Field {$field->name()} must be a valid UUID")),
            
            FieldType::JSON, FieldType::JSONB, FieldType::ARRAY => 
                is_array($value) ? Result::ok($value) : Result::err(new \InvalidArgumentException("Field {$field->name()} must be an array")),
            
            FieldType::ENUM => 
                (is_string($value) && in_array($value, $field->getOptions(), true)) 
                    ? Result::ok($value) 
                    : Result::err(new \InvalidArgumentException("Field {$field->name()} must be one of: " . implode(', ', $field->getOptions()))),
            
            default => Result::ok($value),
        };
    }
}