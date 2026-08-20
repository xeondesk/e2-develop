# Technology Direction

Suggested baseline:
- PHP 8.4+ for the core platform
- TypeScript + React/Next.js for applications
- PostgreSQL primary database
- Redis for cache/session/queues where needed
- S3-compatible object storage
- Docker for reproducible environments
- OpenTelemetry for observability
- Rust for performance-sensitive CLI/workers when justified
- Python for AI/data workloads where justified
- TypeScript, Python, Go, and Rust SDKs over time

Avoid polyglot complexity until a clear boundary or performance need exists.
