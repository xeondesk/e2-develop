# Event Architecture

Core events include ContentCreated, ContentUpdated, ContentDeleted, ContentPublished, ContentUnpublished, UserCreated, MediaUploaded, PluginInstalled, WorkflowTransitioned.

Use synchronous domain events for invariants and asynchronous integration events for search, analytics, notifications, webhooks, and external systems.

Events require stable names, versioning, IDs, timestamps, actor context, tenant context, correlation IDs, and idempotency.
