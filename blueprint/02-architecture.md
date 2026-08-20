# System Architecture

## Layers
1. Experience: Web, Admin, Studio, Mobile, AI.
2. API: REST, GraphQL, webhooks, events.
3. Platform: identity, workflow, media, search, notifications.
4. Core: content, schema, entity, repository, lifecycle, extension.
5. Infrastructure: PostgreSQL, Redis, object storage, queues, observability.

## Runtime rule
UI clients consume public APIs. Domain logic lives below the API boundary and is never duplicated in frontend applications.
