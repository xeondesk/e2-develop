<?php
declare(strict_types=1);

namespace Nexo\Schema;

use Nexo\Domain\Identifier;

/**
 * @implements Identifier<string>
 */
final class Schema
{
    private SchemaId $id;
    private SchemaVersion $version;
    private string $label;
    private string $description = '';
    /** @var FieldDefinition[] */
    private array $fields = [];
    private array $indexes = [];
    private array $metadata = [];

    public function __construct(SchemaId $id, SchemaVersion $version, string $label)
    {
        $this->id = $id;
        $this->version = $version;
        $this->label = $label;
    }

    public function id(): string
    {
        return (string) $this->id;
    }

    public function schemaId(): SchemaId
    {
        return $this->id;
    }

    public function version(): SchemaVersion
    {
        return $this->version;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function addField(FieldDefinition $field): self
    {
        if (isset($this->fields[$field->name()])) {
            throw new \InvalidArgumentException("Field {$field->name()} already exists in schema {$this->id}");
        }
        $this->fields[$field->name()] = $field;
        return $this;
    }

    public function removeField(string $name): self
    {
        unset($this->fields[$name]);
        return $this;
    }

    public function getField(string $name): ?FieldDefinition
    {
        return $this->fields[$name] ?? null;
    }

    /** @return FieldDefinition[] */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    public function addIndex(string $name, array $columns, bool $unique = false): self
    {
        $this->indexes[$name] = ['columns' => $columns, 'unique' => $unique];
        return $this;
    }

    /** @return array<string, array{columns: string[], unique: bool}> */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function metadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'version' => $this->version->value(),
            'label' => $this->label,
            'description' => $this->description,
            'fields' => array_map(fn ($f) => $f->toArray(), $this->fields),
            'indexes' => $this->indexes,
            'metadata' => $this->metadata,
        ];
    }

    public static function fromArray(array $data): self
    {
        $schema = new self(
            SchemaId::create($data['id']),
            SchemaVersion::fromInt($data['version']),
            $data['label']
        );
        if (isset($data['description'])) $schema->description($data['description']);
        if (isset($data['fields'])) {
            foreach ($data['fields'] as $fieldData) {
                $schema->addField(FieldDefinition::fromArray($fieldData));
            }
        }
        if (isset($data['indexes'])) $schema->indexes = $data['indexes'];
        if (isset($data['metadata'])) $schema->metadata($data['metadata']);
        return $schema;
    }
}