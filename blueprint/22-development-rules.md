# Development Rules

1. Domain logic does not belong in controllers.
2. UI does not directly access persistence.
3. Public interfaces require documentation.
4. Breaking changes require an RFC.
5. New core features require tests.
6. Security-sensitive changes require threat review.
7. Avoid premature abstractions.
8. Prefer composition over inheritance.
9. Keep modules independently testable.
10. Keep migrations deterministic.
11. Never silently change persisted semantics.
12. Treat performance as measurable, not assumed.
13. Treat accessibility and localization as platform concerns.
14. Preserve a clear upgrade path.
