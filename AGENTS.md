# WPCS Fork Project Rules for AI Agents

## Project Overview

**contactjavas/wpcs** is a fork of [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards) (WordPressCS), modernised for PHP >= 8.4 and [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer) 4. It ships the `WordPress`, `WordPress-Core`, `WordPress-Docs` and `WordPress-Extra` standards.

### Purpose
- Provide the WordPress sniffs to [`modern-wpcs-ruleset`](https://github.com/contactjavas/modern-wpcs-ruleset) (sibling checkout at `../modern-wpcs-ruleset`), which includes `WordPress-Core`
- Stay close to upstream WordPressCS so its fixes (including security fixes) can keep being merged in

### Tech Stack
- **Language**: PHP `^8.4` (CI matrix: 8.4, 8.5)
- **Framework**: PHP_CodeSniffer `^4.0.1`, PHPCSUtils, PHPCSExtra → [PHPCS Wiki](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki), [PHPCSUtils docs](https://phpcsutils.com/)
- **Tests**: PHPUnit `^13`
- **Package Manager**: Composer (package type `phpcodesniffer-standard`)
- **Dev machine**: Windows + PowerShell. `php` and `composer` are on `PATH`.

### Remotes and branches
- `origin` = `contactjavas/wpcs`, default branch **`develop`**.
- `upstream` = `WordPress/WordPress-Coding-Standards`. Unlike the sibling repos, upstream **is** tracked: merge `upstream/develop` when asked (see "Syncing with upstream").

### Constraints
- Must use the existing codebase and architecture
- The code style is WordPressCS's own (`.phpcs.xml.dist`: the `WordPress` standard + PHPCompatibility + `Generic.PHP.RequireStrictTypes`), not the PSR-2 style of the sibling repos
- Keep upstream mergeable: avoid reformatting or reorganising code you aren't otherwise changing

---

## Package Management

- **Package Manager**: Always use `composer`
- **Commands**:
  - Install: `composer install`
  - Add dev package: `composer require --dev <package>`
  - Update: `composer update`
  - Validate (CI runs this): `composer validate --strict`
- `composer.lock` is gitignored (`"lock": false`); do not commit it.

---

## Security

- Sniffs run on users' code. They must never execute, include or `eval` the code they analyse; work only on the token stream. (Upstream removed the last `eval()` in `EnqueuedResourceParameters` in 2026.)
- Never hardcode sensitive data (tokens, credentials) in code, CI or fixtures
- Handle unexpected token streams defensively (`findNext`/`findPrevious` may return `false`; parse errors leave missing `scope_*`/`parenthesis_*` keys)
- Keep dependency constraints current when security advisories appear (`composer audit`)

---

## Code Quality Standards

### 1. Code Structure
- Every PHP file starts with `declare( strict_types=1 );` (after the file docblock, before `namespace`)
- Every method has native parameter and return types. Docblocks stay, and must agree with the native types.
- Methods that may return a stack pointer to skip to are typed `?int` and end with an explicit `return null;`. A child must keep `?int` when overriding them (`process_token()`, `process_matched_token()`, `process_parameters()`, `process_no_parameters()`, `process_first_class_callable()`, `check_for_matches()`).
- Non-public properties and public **static** properties are natively typed.
- **Public non-static properties stay untyped.** They are ruleset-configurable. PHPCS assigns them `string|bool|null|array` (`''` → `null`, `'true'` → `true`, and array items can be `null`/`bool`) from code that is not in strict mode. A native type would turn a user's `phpcs.xml` setting into a fatal `TypeError`. Normalise them where used: `RulesetPropertyHelper::merge_custom_array()` (takes `mixed`), `(int)`/`(bool)`/`(string)` casts, or `is_string()`/`is_array()` guards. Caches holding the raw value (e.g. `$addedCustomNonceFunctions`) are typed `mixed`.
- With `strict_types`, PHPCS API calls need exact types: e.g. `File::recordMetric()` takes a `string` value, so cast ints.

### 2. Sniffs
- Location and naming: `WordPress/Sniffs/<Category>/<Name>Sniff.php`, namespace `WordPressCS\WordPress\Sniffs\<Category>`. The sniff code is `WordPress.<Category>.<Name>.<ErrorCode>`.
- **Sniff codes and public properties are public API.** Users and `modern-wpcs-ruleset` reference them in `phpcs.xml`. Do not rename or remove a category, sniff, error code or property without being asked, and call it out as a breaking change.
- PHPCS 4 tokenises names as single tokens (`T_NAME_FULLY_QUALIFIED`, `T_NAME_QUALIFIED`, `T_NAME_RELATIVE`), e.g. `\current_time` is one token. Use `Collections::nameTokens()` and strip a leading `\` when comparing names. A fixer that replaces such a token must keep the `\`.
- Prefer PHPCSUtils/PHPCS utilities over hand-rolled token scanning (`PassedParameters`, `FunctionDeclarations`, `Tokens::$emptyTokens`, `scope_closer`, `parenthesis_closer`, `$phpcsFile->eolChar`).
- Fixers must converge and must not fight another sniff (CI runs all fixers over the test fixtures).
- The abstract sniffs (`WordPress/Abstract*.php`), `WordPress/Sniff.php`, the helper traits, and the non-final sniffs (`ValidHookName`, `EscapeOutput`, `NonceVerification`, `ValidatedSanitizedInput`) can be extended by external standards. Their signatures are API.

### 3. Performance
- Sniffs run on every token of every file. Register only the tokens you need, and return early.
- Bound searches with `scope_closer` / `parenthesis_closer`; don't scan the whole file from `process()`.

---

## Development Workflow

### 1. Implementation
- Carefully read and interpret all user instructions
- Write a failing fixture or test first when fixing a false positive or false negative
- Follow existing code patterns in the project
- Ask for clarification when requirements are ambiguous or missing

### 2. Tests
- Sniff tests: `WordPress/Tests/<Category>/<Name>UnitTest.php` + `<Name>UnitTest.inc` (more fixtures: `.1.inc`, `.2.inc`, …; fixer output in `.inc.fixed`). The test class returns `line => count` arrays from `getErrorList()` / `getWarningList()`.
- Helper tests: `WordPress/Tests/Helpers/<Helper>/<Method>UnitTest.php`, usually extending `PHPCSUtils\TestUtils\UtilityMethodTestCase`.
- PHPUnit 13 ignores docblock annotations. Data providers need `#[DataProvider( 'name' )]` (with `use PHPUnit\Framework\Attributes\DataProvider;`), wrapped in `// phpcs:disable/enable PHPCompatibility.Attributes.NewAttributes.PHPUnitAttributeFound`. Keep the `@dataProvider`/`@covers` docblock tags too (upstream compatibility).
- `phpunit.xml.dist` fails on deprecations, notices and warnings.
- Fixture files must use **LF** line endings (`.gitattributes` forces `eol=lf`). PowerShell `Set-Content` can produce CRLF or mixed endings; use the Write tool.

### 3. Verification

**Always verify** before reporting a task as done. All must pass:

```
composer validate --strict
composer lint            # parallel-lint (parse errors)
composer check-cs        # phpcs on the codebase using .phpcs.xml.dist
composer test            # PHPUnit
composer check-complete  # every sniff has tests
```

`composer check-complete-strict` also requires XML docs; 10 sniffs inherited from upstream still lack them (warnings).

Quick manual check of a single sniff on an ad-hoc file (keep scratch files outside the repo):

```
php vendor/bin/phpcs -s --standard=WordPress --sniffs=WordPress.<Category>.<Name> path/to/file.php
php vendor/bin/phpcbf --standard=WordPress --sniffs=WordPress.<Category>.<Name> path/to/file.php
```

For broad changes (typing, strict_types, helper changes), also run the whole `WordPress` standard over a large real codebase and check that no `Internal.Exception` is reported: `phpcs --standard=WordPress -q --report=source <dir>`.

---

## Syncing with upstream

Only when the user asks.

1. `git fetch upstream`, then review `git log --oneline --no-merges HEAD..upstream/develop` and `git diff --stat HEAD...upstream/develop`. Tell the user what is worth taking (security fixes and sniff fixes are; CI/workflow changes usually aren't, because this fork has its own `ci.yml`).
2. `git merge upstream/develop`. Expected conflict areas:
   - `composer.json`: keep this fork's name, `php`, PHPCS 4 and PHPUnit 13 constraints; take upstream's newer minimums of other packages.
   - `.github/workflows/*`: this fork deleted upstream's workflows. Keep them deleted (`git rm`).
   - Method signatures: upstream has no native types. Keep the typed signature and take upstream's body change.
3. Adapt what upstream added to this fork's conventions: `declare( strict_types=1 );`, native types, `#[DataProvider]` attributes, PHPCS 4 name tokens in tests (`getTargetToken( $marker, Collections::nameTokens(), '\Full\Name' )`).
4. Run the full verification above. Don't push unless asked.

---

## Release & Downstream Impact

- `modern-wpcs-ruleset` requires this package by tag (`contactjavas/wpcs` in `../modern-wpcs-ruleset/composer.json`, via a VCS repository). Fixes reach it only after a tagged release.
- A major release here (raised PHP/PHPCS minimum, signature changes, renamed codes) needs a matching constraint bump and release in the ruleset.
- The clone has an `upstream` remote, and `gh` defaults to it. Always pass `--repo contactjavas/wpcs` to `gh`.
- The clone also holds upstream's tags (`3.4.1`, …, no `v`). Never `git push --tags`.
- Do not create tags or releases unless the user asks. When asked, follow the `release` skill.

---

## Skills

Task-specific workflows live in `.agents/skills/<name>/SKILL.md`. When a request matches a skill's description, read that file and follow it.

| Skill | Path | Use when |
| --- | --- | --- |
| **release** | [`.agents/skills/release/SKILL.md`](.agents/skills/release/SKILL.md) | The user asks to release, tag or publish a version: verify, pick the semver bump, annotated `vX.Y.Z` tag, push, GitHub Release. |

---

## Git Workflow

> **IMPORTANT**: Do NOT commit or push unless the user explicitly asks you to.

### When Asked to Commit & Push

1. **Stage changes**: `git add .`. Check `git status` first, so nothing unexpected is included.

2. **Commit with this repo's format**:
   - **Subject line**: imperative mood, naming the sniff or area, e.g. `Fix CurrentTimeTimestamp: keep leading backslash` or `Housekeeping: …`
   - **Description**: Structured explanation (can be a list). Do not mention the AI agent, and do not add an AI co-author.

   ```bash
   git commit -m "Fix <Sniff>: brief subject" -m "Detailed description here.

   - Change 1
   - Change 2"
   ```

3. **Push to GitHub**: `git push origin develop`. Never push to `upstream`.

---

## Project Structure

```
wpcs/
├── .agents/skills/             # Task-specific agent skills & workflows
├── .github/workflows/ci.yml    # validate, lint, check-cs, tests, completeness, ruleset + fixer-conflict checks (PHP 8.4/8.5)
├── WordPress/
│   ├── ruleset.xml             # The `WordPress` standard (Core + Docs + Extra)
│   ├── Sniff.php               # Base sniff class
│   ├── Abstract*Sniff.php      # Abstract sniffs (extensible API)
│   ├── Helpers/                # Shared helper classes and traits
│   ├── Sniffs/<Category>/      # The sniffs
│   ├── Docs/<Category>/        # XML sniff documentation
│   └── Tests/                  # Sniff and helper tests + fixtures
├── WordPress-Core/ruleset.xml  # Used by modern-wpcs-ruleset
├── WordPress-Docs/ruleset.xml
├── WordPress-Extra/ruleset.xml
├── Tests/
│   ├── bootstrap.php           # PHPCS test bootstrap (+ PHPCS 4 AbstractSniffUnitTest alias)
│   └── RulesetCheck/           # Files the rulesets are checked against in CI
├── .phpcs.xml.dist             # Lint config for this repo's own code
├── phpunit.xml.dist
├── CHANGELOG.md
└── README.md
```

---

## Important

- Maintain clarity and request confirmation when needed
- Ensure high-quality, scoped, and standards-compliant implementations
- Regularly report progress during multi-step tasks
- Pause and confirm at critical decision points (sniff-code renames, behaviour changes, version bumps)
- Clearly report errors or blockers, and propose practical resolutions

---

## Reference Documentation

**Do NOT read these preemptively.** Consult one only when the task touches its domain:

| Document | Path | Description |
| --- | --- | --- |
| **Changelog** | [`CHANGELOG.md`](CHANGELOG.md) | Fork releases (`4.0.0`+) on top of upstream's history. |
| **Usage / Installation** | [`README.md`](README.md) | How consumers install and configure the standards. |
| **Upstream wiki** | https://github.com/WordPress/WordPress-Coding-Standards/wiki | Sniff properties and customisation. |
| **Downstream ruleset** | `../modern-wpcs-ruleset/AGENTS.md` | Rules for the meta-ruleset that consumes this package. |
