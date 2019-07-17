# Technical debt

A log of technical debt we're aware of and have accepted. These aren't "problems", but we might like to address them in the future if we have suitable time and resource, if related work gives an opportunity, or if they become more pressing.

- There are several files and folders we don't use any more which can be safely removed
- We should use environment variables as feature switches, rather than having separate config files/envtrypoints for each environment
- We don't need to generate `behat.yml` or `parameters.yml` through confd. We should use environment variables to populate them instead, deobfuscating our code.
- We define status label translations in `common.en.yml` (twice), `ndr-overview.en.yml` (twice) and `report-overview.en.yml` (twice). Using one, clear definition would make it easier to make changes in the future
- We should use `bin/phpunit` when running tests (instead of `vendor/phpunit/phpunit/phpunit`)
- `behat-debugger.php` is a poor way of dealing with test failures: it requires explicit setup in our NGINX configuration files and doesn't always collect useful information (e.g. just showing "Application error" when something crashes)
