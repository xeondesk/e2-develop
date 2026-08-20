<?php
declare(strict_types=1);

namespace Nexo\Schema;

final class FieldRegistry
{
    /** @var array<string, class-string<FieldDefinition>> */
    private array $types = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    private function registerDefaults(): void
    {
        foreach (FieldType::cases() as $type) {
            $this->types[$type->value] = FieldDefinition::class;
        }
    }

    public function register(string $typeName, string $fieldClass): void
    {
        if (!is_a($fieldClass, FieldDefinition::class, true)) {
            throw new \InvalidArgumentException("{$fieldClass} must extend FieldDefinition");
        }
        $this->types[$typeName] = $fieldClass;
    }

    public function create(string $name, string $typeName, array $options = []): FieldDefinition
    {
        $class = $this->types[$typeName] ?? FieldDefinition::class;
        $fieldType = FieldType::tryFrom($typeName);
        
        if (!$fieldType) {
            throw new \InvalidArgumentException("Unknown field type: {$typeName}");
        }

        $field = new $class($name, $fieldType);

        if (isset($options['nullable'])) $field->nullable($options['nullable']);
        if (isset($options['unique'])) $field->unique($options['unique']);
        if (isset($options['primary'])) $field->primary($options['primary']);
        if (isset($options['default'])) $field->default($options['default']);
        if (isset($options['description'])) $field->description($options['description']);
        if (isset($options['options'])) $field->options($options['options']);

        return $field;
    }

    public function hasType(string $typeName): bool
    {
        return isset($this->types[$typeName]);
    }

    /** @return string[] */
    public function getTypes(): array
    {
        return array_keys($this->types);
    }
}