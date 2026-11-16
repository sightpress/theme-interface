# SightPress theme interface

The contracts that every SightPress theme implements. SightPress is a powerful CMS for everyone, designed with
blind and low-vision creators and audiences in mind, built on modern, object-oriented PHP.

This package holds interfaces, small value objects and the `theme.json` schema only. It contains no implementation
code and has no runtime dependencies. The default theme and third-party themes use the same contracts.

It is versioned on its own, with strict semantic versioning: a new major version means a breaking change in a contract.

## Requirements

- PHP 8.5 or later

## Development

This package has no dependencies on the other SightPress repos. To work on it:

```sh
composer install
composer check
```

Composer commands:

- `composer check`: everything that must pass before a commit. It validates `composer.json`, checks the code
  style, runs static analysis, runs the unit suite with the coverage gate, then runs the integration suite.
- `composer test`: both test suites. `composer test:unit` and `composer test:integration` run one suite each.
- `composer test:coverage`: the unit suite with coverage. It fails if unit tests cover less than 100% of the lines
  or methods in `src/`, and it lists what is not covered. `@codeCoverageIgnore` is not allowed. The HTML report
  is written to `build/coverage/html/`.
- `composer fix`: applies Rector refactorings, then fixes the code style.

Coverage needs the PCOV extension, or Xdebug in coverage mode. If your PHP has neither, use the
development image from the [core](https://github.com/sightpress/core) repo (`docker/dev-php/`), which has
PHP 8.5, PCOV and Composer. With `core` cloned next to this repo, run this from this folder:

```sh
docker build -t sightpress/dev-php:8.5 ../core/docker/dev-php
docker run --rm --user "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD:/workspace" -w /workspace \
  sightpress/dev-php:8.5 composer check
```

### Continuous integration

Every pull request and every push to `main` runs the `CI` workflow (`composer check`): `composer validate`, code
style, PHPStan, the unit suite with the 100% coverage gate, the integration suite and `composer audit`. The
coverage report is uploaded as an artifact.

Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/); release-please uses them to
keep a release pull request and `CHANGELOG.md` up to date.

`main` is protected by the ruleset in `.github/rulesets/main.json`. Changes reach it only through a pull request that
passes the `composer check` check and has one approval. Pull requests are squash-merged. Admins can bypass the ruleset.

## Licence

GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
