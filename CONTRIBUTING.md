# Contributing

 * Coding standard for the project is [PSR-12](https://www.php-fig.org/psr/psr-12/), all files use
   `declare(strict_types=1);`.
 * Any contribution must provide **new tests**, with PHPUnit (`tests/`) and with Behat (`features/`), for the
   introduced or fixed behaviors.
 * Existing tests must not be modified : a failing existing test after a change is a regression to fix in the code.
   If an existing test is really wrong, explain why in the pull request before changing it.
 * Any un-confirmed issue needs a failing test case before being accepted.
 * One commit per bug fix or feature, with its tests and its line in `CHANGELOG.md`.
 * Pull requests must be sent from a new hotfix/feature branch, not from `master`.

See also `AGENTS.md` for a complete description of the concepts, the tooling and the development rules.

## Installation

To install the project and run the tests, you need to clone it first:

```sh
$ git clone https://github.com/TeknooSoftware/recipe.git
```

You will then need to run a composer installation:

```sh
$ cd recipe
$ make depend
```

(`make depend` runs `composer update`, use `DEPENDENCIES=lowest make depend` to test with the lowest dependencies).

## Testing

The PHPUnit and Behat versions to be used are the ones installed as dev-dependencies via composer.
`make test` runs the PHPUnit test suite with the code coverage (the Xdebug extension is required), then the
Behat scenarios. `make qa` runs the linter, PHPStan (level max), PHP_CodeSniffer (PSR-12) and `composer audit`.

```sh
$ make test
$ make qa
```

Accepted coverage for new contributions is 90% (see the coverage report of `make test`). Any contribution not
satisfying this requirement won't be merged.

For any questions, contact me : [richard@teknoo.software](mailto:richard@teknoo.software) :)

## Support this project

This project is free and will remain free. It is fully supported by commercial activities of SASU Teknoo Software
and EIRL Richard DELOGE. If you like it and help me maintain it and evolve it, don't hesitate to support me on
[Patreon](https://patreon.com/teknoo_software) or [Github](https://github.com/sponsors/TeknooSoftware).
Thanks :) Richard.
