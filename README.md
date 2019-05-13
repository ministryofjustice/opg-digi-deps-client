# Complete the deputy report (Client)

## Overview

This app is the client used by deputy to submit their report to OPG.

Complete the deputy report is composed by
 - [Client](https://github.com/ministryofjustice/opg-digi-deps-client)
 - [API](https://github.com/ministryofjustice/opg-digi-deps-api)
 - [Docker config (private)](https://github.com/ministryofjustice/opg-digi-deps-docker)


## Frameworks and languages

- PHP 5.6
- Symfony 2.8
- Behat 3
- PHPUnit 4
- Twig
- Connects to API for data operations
- Uses [GOV.UK Frontend Toolkit](https://github.com/alphagov/govuk_frontend_toolkit)
- Uses [GOV.UK Template](https://github.com/alphagov/govuk_template)
- Uses [GOV.UK Elements](https://github.com/alphagov/govuk_elements)

## Setup

If you haven't already, you need to create a docker network called "digideps": `docker network create digideps`.

Run `docker-compose run --rm composer composer install` to install all dependencies prior to starting the application.

You will need to either have the API repository running locally, or point to an external instance by setting the `FRONTEND_API_URL` environment variable in `docker/env/frontend.env`.

Run `docker-compose up -d` to start the client containers.

## Architectural notes

### Testing
see [here](tests/README.md)

## Frontend technical notes

### Gulp
The frontend components rely on Gulp to be built and assembled. The main tasks involved in this part of the build are copying image assets, compiling SASS to CSS and concatinating JS into a single file and then running uglify to minify it.

You can run these against the `npm` container through Docker. To compile all the assets, watch them and automatically recompile when changes occur:

```
docker-compose run --rm npm run develop
```

You can alternatively run `build` to compile the assets once only or `lint` to lint the source files.

Each of the steps in Gulp are documented in the Gulpfile. The Gulp build file has many targets, but the 3 that are of most interest are **default**, **watch** and **development**.

### Browser Testing

There are notes in the readme file in the test folder to describe the best way to run regular tests and how to attempt to run those same tests with a real browser via browserstack.

With special thanks to [BrowserStack](https://www.browserstack.com) for providing cross browser testing.


### Dependencies


Dependencies are versioned to avoid accidently breaking the build. From time to time new review those dependencies to see if a valid new version is available, the chief of these should be [govuk-elements-sass](https://www.npmjs.com/package/govuk-elements-sass)


## Coding standards

[PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/)

Run `php-cs-fixer fix` to format files

## License

The OPG Digideps Client is released under the MIT license, a copy of which can be found in [LICENSE](LICENSE).
