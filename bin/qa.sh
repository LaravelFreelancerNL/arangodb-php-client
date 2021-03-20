#!/usr/bin/env bash

./vendor/bin/phpcbf

./vendor/bin/phpcs

./vendor/bin/phpmd src/ text phpmd-ruleset.xml

./vendor/bin/phpstan analyse -c phpstan.neon

./vendor/bin/psalm

./vendor/bin/phpunit