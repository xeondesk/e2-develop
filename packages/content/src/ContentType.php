<?php
declare(strict_types=1);

namespace Nexo\Content;

use Nexo\Schema\Schema;

final class ContentType
{
    private string $typeId;
    private Schema $schema;
    private string $label;
    private string $description = '';
    private array $metadata = [];

    public function __construct(string $typeId, Schema $schema, string $label)
    {
        $this->typeId = $typeId;
        $this->schema = $schema;
        $this->label = $label;
    }

    public function typeId(): string
    {
        return $this->typeId;
    }

    public function schema(): Schema
    {
        return $this->schema;
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

    public function metadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'type_id' => $this->typeId,
            'schema' => $this->schema->toArray(),
            'label' => $this->label,
            'description' => $this->description,
            'metadata' => $this->metadata,
        ];
    }

    public static function fromArray(array $data): self
    {
        $contentType = new self(
            $data['type_id'],
            Schema::fromArray($data['schema']),
            $data['label']
        );
        if (isset($data['description'])) {
            $contentType->setDescription($data['description']);
        }
        if (isset($data['metadata'])) {
            $contentType->metadata($data['metadata']);
        }
        return $contentType;
    }
}