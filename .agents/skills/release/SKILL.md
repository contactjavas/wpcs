---
name: release
description: Cut a new version of contactjavas/wpcs by verifying, picking the semver bump, creating an annotated vX.Y.Z tag, pushing it to origin (Composer reads versions from tags), and publishing a GitHub Release. Use when the user says "release", "tag a release", "cut a version", "publish", or "tag and push".
---

# Release (verify → pick version → tag → push → GitHub Release)

There is no `version` field in `composer.json` and no deploy workflow. Consumers (including
`../modern-wpcs-ruleset`) install this package from the GitHub VCS repository, so **pushing a
`vX.Y.Z` tag to `origin` is the release**. A pushed tag is public and can't be taken back cleanly.

**Always pass `--repo contactjavas/wpcs` to `gh`.** The clone has an `upstream` remote
(WordPress/WordPress-Coding-Standards), and `gh` resolves to it by default, so without `--repo` it reads or
writes the wrong repository.

**This fork's tags are `vX.Y.Z` (leading `v`).** The clone also contains upstream's tags (`3.4.1`, `3.4.0`, …,
no `v`). Ignore those when looking for the latest fork release, and never push them.

## Steps

1. **Inspect state**
   - You must be on `develop`, with a clean working tree (`git status`). If there are uncommitted changes,
     stop and ask whether they belong in the release. If they do, commit them first using the format
     from `AGENTS.md` → Git Workflow.
   - `git fetch origin --tags` so the remote state is known. Local `develop` must not be behind `origin/develop`.
   - Latest fork release: `git tag -l 'v*' --sort=-v:refname | Select-Object -First 1`.
   - What's unreleased: `git log --oneline <latest-tag>..HEAD` and `git diff <latest-tag>..HEAD --stat`.
     If nothing is unreleased, tell the user and stop.

2. **Verify**. Every command must pass:
   ```
   composer validate --strict
   composer lint
   composer check-cs
   composer test
   composer check-complete
   ```
   Also check CI on the commit you're tagging:
   `gh run list --repo contactjavas/wpcs --workflow ci.yml --commit <HEAD sha> --limit 1`.
   It must be `completed success`. If it's still running, wait with
   `gh run watch <id> --repo contactjavas/wpcs --exit-status`. If it failed, stop.

3. **Pick the version** (semver). Read `git diff <latest-tag>..HEAD -- composer.json WordPress/ WordPress-*/`:
   - **Major**: the minimum `php` or `squizlabs/php_codesniffer` requirement was raised; a sniff, category,
     error code or public property was renamed or removed (used in users' `phpcs.xml` and in
     `modern-wpcs-ruleset`); or a public/protected signature of an extensible class changed
     (`WordPress/Sniff.php`, `WordPress/Abstract*.php`, helper traits, non-final sniffs).
   - **Minor**: a new sniff, a new error code, a new public sniff property, or upstream features merged in.
   - **Patch**: bug fixes (false positives/negatives, fixer fixes), refactors, docs, CI.
   - Decide patch/minor yourself. For a **major**, tell the user why. Get confirmation before tagging, unless
     the user already asked for this release and accepted whatever version makes sense.

4. **Changelog**: `CHANGELOG.md` must have a section for the new version (rename `[Unreleased]` to
   `[X.Y.Z] - YYYY-MM-DD`, and add the compare link at the bottom). Commit it before tagging.

5. **Tag** the release commit with an **annotated** tag, leading `v`, three parts:
   ```powershell
   git tag -a vX.Y.Z -m @'
   Release vX.Y.Z: <one-line summary>

   - <notable change>
   - <notable change>
   '@
   ```
   Keep the body to one line per user-visible change. Don't restate the diff. Don't mention the AI agent.

6. **Push** the branch first (if it has unpushed commits), then **only this tag**:
   ```
   git push origin develop
   git push origin vX.Y.Z
   ```
   **Never** `git push --tags` (it would publish upstream's tags). Never push to `upstream`. Don't use
   `--force`. If the branch push is rejected, `git pull --rebase origin develop`, re-run step 2, then move
   the still-unpushed tag (`git tag -d vX.Y.Z` and re-create it) and push again.

7. **GitHub Release** (every version has one). Write the notes to a file in the scratchpad, in the format of
   earlier releases (`gh release view v4.0.0 --repo contactjavas/wpcs`): `# Release vX.Y.Z`, a one-paragraph
   summary, then `## Breaking Changes` (if any), `## Fixes` / `## Added`, `## Maintenance`, and
   `## Installation` with the VCS repository snippet and `composer require --dev contactjavas/wpcs:^X.Y.Z`.
   For a major, say exactly what users (and extenders of the abstract sniffs) must change.
   ```
   gh release create vX.Y.Z --repo contactjavas/wpcs --verify-tag --title vX.Y.Z --latest --notes-file <file>
   ```

8. **Report**: the tag, its commit hash, the previous → new version, the release URL, and the summary.
   - Remind the user about downstream. Look up the constraint in `../modern-wpcs-ruleset/composer.json`
     (`contactjavas/wpcs`). A **major** release needs a constraint bump there (see that repo's `release`
     skill). A minor or patch release reaches it with `composer update contactjavas/wpcs`.

## Don'ts

- Don't add a `version` field to `composer.json`. Composer takes the version from the tag.
- Don't skip hooks (`--no-verify`) or amend commits that were already pushed.
- Don't move, delete or re-push a tag that already exists on `origin`. Release the next version instead.
