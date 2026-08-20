# Schema Engine

The schema engine defines content types and fields.

Example:
```yaml
type: article
fields:
  title: {type: string, required: true}
  slug: {type: slug, unique: true}
  body: {type: richtext}
  author: {type: relation, target: user}
  cover: {type: media}
```

The engine should drive validation, storage metadata, API contracts, admin forms, generated types, search configuration, and documentation.

Requirements: versioned schemas, migrations, validation, field registry, relations, localization, defaults, constraints.
