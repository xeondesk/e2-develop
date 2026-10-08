CREATE TABLE IF NOT EXISTS nexo_schemas (
  schema_id VARCHAR(100) PRIMARY KEY,
  version INTEGER NOT NULL,
  label VARCHAR(255) NOT NULL,
  description TEXT,
  fields JSONB NOT NULL DEFAULT '[]'::jsonb,
  indexes JSONB NOT NULL DEFAULT '[]'::jsonb,
  metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS nexo_content_entries (
  id UUID PRIMARY KEY,
  type_id VARCHAR(100) NOT NULL,
  data JSONB NOT NULL DEFAULT '{}'::jsonb,
  status VARCHAR(40) NOT NULL DEFAULT 'draft',
  revision_number INTEGER NOT NULL DEFAULT 1,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  created_by VARCHAR(100),
  updated_by VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS nexo_content_revisions (
  content_id UUID NOT NULL REFERENCES nexo_content_entries(id) ON DELETE CASCADE,
  number INTEGER NOT NULL,
  data JSONB NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  author_id VARCHAR(100),
  message TEXT,
  PRIMARY KEY (content_id, number)
);

CREATE TABLE IF NOT EXISTS nexo_content_publications (
  content_id UUID PRIMARY KEY REFERENCES nexo_content_entries(id) ON DELETE CASCADE,
  revision_number INTEGER NOT NULL,
  published_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  published_by VARCHAR(100),
  unpublished_at TIMESTAMPTZ,
  unpublished_by VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS nexo_events (
  event_id VARCHAR(100) PRIMARY KEY,
  event_name VARCHAR(255) NOT NULL,
  event_version INTEGER NOT NULL DEFAULT 1,
  occurred_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  actor_id VARCHAR(100),
  tenant_id VARCHAR(100),
  correlation_id VARCHAR(100),
  causation_id VARCHAR(100),
  payload JSONB NOT NULL DEFAULT '{}'::jsonb
);

CREATE TABLE IF NOT EXISTS nexo_users (
  id UUID PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS nexo_roles (
  id VARCHAR(100) PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  permissions JSONB NOT NULL DEFAULT '[]'::jsonb
);

CREATE TABLE IF NOT EXISTS nexo_user_roles (
  user_id UUID NOT NULL REFERENCES nexo_users(id) ON DELETE CASCADE,
  role_id VARCHAR(100) NOT NULL REFERENCES nexo_roles(id) ON DELETE CASCADE,
  PRIMARY KEY (user_id, role_id)
);

CREATE TABLE IF NOT EXISTS nexo_audit_events (
  id UUID PRIMARY KEY,
  actor_id VARCHAR(100),
  action VARCHAR(120) NOT NULL,
  resource_type VARCHAR(120) NOT NULL,
  resource_id VARCHAR(100),
  metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
  occurred_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS nexo_workflow_definitions (
  name VARCHAR(120) PRIMARY KEY,
  definition JSONB NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
