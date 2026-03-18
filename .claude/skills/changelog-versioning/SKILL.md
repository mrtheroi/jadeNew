---
name: changelog-versioning
description: "Manages API versioning, CHANGELOG.md, and README.md in sync. Activates when making any functional change to the API: adding endpoints, modifying behavior, fixing bugs, removing features, or when the user mentions version, changelog, release, bump, readme update, or documentation sync."
license: MIT
metadata:
  author: cesarvalero
---

# Changelog & Versioning

## When to Apply

Activate this skill when:

- Adding, modifying, or removing API endpoints
- Fixing bugs that affect API behavior
- Changing request/response formats
- Updating service logic that impacts the API contract
- The user explicitly asks to bump the version or update the changelog

## Core Principle

Three files must ALWAYS stay in sync after any API change:

| File | Purpose |
|------|---------|
| `config/api.php` | Single source of truth for the current API version |
| `CHANGELOG.md` | Human-readable history of all changes |
| `README.md` | Up-to-date documentation of the API |

Never update one without updating the others. If any of these files is out of sync, fix it immediately.

## Semantic Versioning

Follow [Semantic Versioning 2.0.0](https://semver.org/):

| Change Type | Version Bump | Examples |
|-------------|-------------|---------|
| Breaking changes | MAJOR (X.0.0) | Removing endpoints, changing response structure, renaming fields |
| New functionality | MINOR (x.Y.0) | New endpoints, new optional fields, new services |
| Bug fixes | PATCH (x.y.Z) | Fixing validation, correcting error messages, fixing logic |

## CHANGELOG.md Format

Follow [Keep a Changelog 1.1.0](https://keepachangelog.com/es-ES/1.1.0/):

- Maintain an `[Unreleased]` section at the top for work in progress.
- When releasing, move `[Unreleased]` items into a new version section with the date: `[x.y.z] - YYYY-MM-DD`.
- Use these section headers (only include sections that have entries):

```markdown
### Added
### Changed
### Deprecated
### Removed
### Fixed
### Security
```

- Write entries in past tense, starting with what was added/changed.
- Group related changes together under the same section.
- Each entry should be a single line starting with `- `.

## config/api.php

- The version string in `config/api.php` must match the latest released version in `CHANGELOG.md`.
- Update the version value directly: `'version' => 'x.y.z'`.
- This is the single source of truth the API middleware uses to append `api_version` to responses.

## README.md Updates

When API changes occur, update these sections in `README.md`:

- **Configuracion**: Add or update environment variables if new config is introduced.
- **API endpoints**: Add new endpoints, update request/response examples, remove deprecated endpoints.
- **Estructura**: Update the file tree if new files were created (controllers, services, exceptions, requests, tests).
- **Response examples**: Ensure all JSON examples reflect the current `api_version` value from `config/api.php`.

## Checklist

Before finalizing any API change, verify:

1. `config/api.php` version is bumped appropriately (major/minor/patch).
2. `CHANGELOG.md` has an entry under the correct version with today's date.
3. `README.md` documents any new/changed endpoints, config, or structure.
4. The `api_version` in all README.md JSON response examples matches `config/api.php`.
5. No discrepancies exist between the three files.
