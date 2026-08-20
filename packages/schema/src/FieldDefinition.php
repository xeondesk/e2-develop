<?php
declare(strict_types=1);

namespace Nexo\Schema;

final class FieldDefinition
{
    private string $name;
    private FieldType $type;
    private bool $nullable = false;
    private bool $unique = false;
    private bool $primary = false;
    private mixed $default = null;
    private array $options = [];
    private string $description = '';

    public function __construct(string $name, FieldType $type)
    {
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
            throw new \InvalidArgumentException("Field name must be lowercase alphanumeric with underscores: {$name}");
        }
        $this->name = $name;
        $this->type = $type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): FieldType
    {
        return $this->type;
    }

    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function unique(bool $unique = true): self
    {
        $this->unique = $unique;
        return $this;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function primary(bool $primary = true): self
    {
        $this->primary = $primary;
        return $this;
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }

    public function default(mixed $default): self
    {
        $this->default = $default;
        return $this;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function hasDefault(): bool
    {
        return $this->default !== null;
    }

    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'nullable' => $this->nullable,
            'unique' => $this->unique,
            'primary' => $this->primary,
            'default' => $this->default,
            'options' => $this->options,
            'description' => $this->description,
        ];
    }

    public static function fromArray(array $data): self
    {
        $field = new self($data['name'], FieldType::from($data['type']));
        if (isset($data['nullable'])) $field->nullable($data['nullable']);
        if (isset($data['unique'])) $field->unique($data['unique']);
        if (isset($data['primary'])) $field->primary($data['primary']);
        if (isset($data['default'])) $field->default($data['default']);
        if (isset($data['options'])) $field->options($data['options']);
        if (isset($data['description'])) $field->description($data['description']);
        return $field;
    }
}