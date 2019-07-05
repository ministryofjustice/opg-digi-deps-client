# Testing

This application uses two main testing technologies:

- PHPUnit performs unit tests for individual classes
- Behat performs user tests to ensure whole application journies work

## How to run the tests

To run the entire test suite, use the container script. Note that this resets the database first.

```sh
docker-compose run --rm api sh scripts/resetdb.sh
docker-compose run --rm test sh scripts/clienttest.sh
```

You can run an individual Behat test suite by calling Behat directly in the test container. For example, to run the `admin` tests:

```sh
docker-compose run --rm test bin/behat --config=tests/behat/behat.yml --stop-on-failure --suite=admin
```

## PHPUnit

The PHPUnit tests are stored in a directory structure matching `src/AppBundle`. Tests classes should extend `PHPUnit\Framework\TestCase` and use methods starting `test` to define actual tests.

We use [Mockery][mockery] to mock classes and entities which are not being tested.

## Behat

Behat tests are run against the environment's client container, meaning test data is stored in the corresponding database. This makes test failures easy to debug, but means that using the application during tests can cause failures.

### Suites

The Behat tests are divided into 6 suites:

- `infra`: A basic set of tests which check the end-to-end application. This allows the tests to fail fast on a critical problems.
- `admin`: Tests for the private administration part of the application.
- `lay`: Tests for lay deputy user functionality. Also tests functionality used by all deputies.
- `ndr`: Tests for the New Deputy Report, filled out by all new new deputies.
- `prof`: Tests for professional deputy user functionality.
- `pa`: Tests for public authority deputy user functionality.

[mockery]: http://docs.mockery.io/en/latest/
