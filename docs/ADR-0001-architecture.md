# ADR-0001: Layered Modular Architecture

Status: Accepted

Nexo uses a layered modular architecture. UI and transport depend on application/domain
services; domain logic does not depend on UI. Persistence is accessed through explicit
repositories. Extensions integrate through stable contracts.
