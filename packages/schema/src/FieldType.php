<?php
declare(strict_types=1);

namespace Nexo\Schema;

enum FieldType: string
{
    case STRING = 'string';
    case TEXT = 'text';
    case INTEGER = 'integer';
    case BIGINT = 'bigint';
    case FLOAT = 'float';
    case DECIMAL = 'decimal';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TIMESTAMP = 'timestamp';
    case UUID = 'uuid';
    case JSON = 'json';
    case JSONB = 'jsonb';
    case ARRAY = 'array';
    case ENUM = 'enum';
    case REFERENCE = 'reference';
    case MEDIA = 'media';
    case SLUG = 'slug';
    case EMAIL = 'email';
    case URL = 'url';
    case PASSWORD = 'password';

    public function getSqlType(): string
    {
        return match ($this) {
            self::STRING => 'VARCHAR(255)',
            self::TEXT => 'TEXT',
            self::INTEGER => 'INTEGER',
            self::BIGINT => 'BIGINT',
            self::FLOAT => 'DOUBLE PRECISION',
            self::DECIMAL => 'NUMERIC(10, 2)',
            self::BOOLEAN => 'BOOLEAN',
            self::DATE => 'DATE',
            self::DATETIME => 'TIMESTAMPTZ',
            self::TIMESTAMP => 'TIMESTAMPTZ',
            self::UUID => 'UUID',
            self::JSON => 'JSON',
            self::JSONB => 'JSONB',
            self::ARRAY => 'JSONB',
            self::ENUM => 'VARCHAR(100)',
            self::REFERENCE => 'UUID',
            self::MEDIA => 'UUID',
            self::SLUG => 'VARCHAR(255)',
            self::EMAIL => 'VARCHAR(255)',
            self::URL => 'VARCHAR(500)',
            self::PASSWORD => 'VARCHAR(255)',
        };
    }

    public function getPhpType(): string
    {
        return match ($this) {
            self::STRING, self::TEXT, self::SLUG, self::EMAIL, self::URL, self::PASSWORD, self::ENUM => 'string',
            self::INTEGER, self::BIGINT => 'int',
            self::FLOAT, self::DECIMAL => 'float',
            self::BOOLEAN => 'bool',
            self::DATE, self::DATETIME, self::TIMESTAMP => '\DateTimeInterface',
            self::UUID => 'string',
            self::JSON, self::JSONB, self::ARRAY => 'array',
            self::REFERENCE => 'string',
            self::MEDIA => 'string',
        };
    }
}