# Testing Strategy

```text
tests/
├── unit
├── integration
├── contract
├── api
├── e2e
├── security
├── migration
├── plugin
├── performance
└── compatibility
```

Core packages require unit and integration coverage. Public APIs require contract tests. Migrations require upgrade/downgrade or forward-compatibility testing as applicable.
