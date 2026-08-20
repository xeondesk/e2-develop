# Identity and Authorization

Support users, organizations, teams, projects, sessions, roles, permissions, policies, API keys, service accounts, MFA, and audit logs.

Authorization model:
resource + action + subject + scope + condition.

Start with RBAC; design policy interfaces so ABAC/resource policies can be added without rewriting domain logic.
