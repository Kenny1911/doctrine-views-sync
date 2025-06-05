#!/usr/bin/env bash

for doctrine_dbal_ver in '^3.0' '^4.0'; do
  for doctrine_persistence in '^3.1' '^4.0'; do
    for symfony_console_version in '^5.4' '^6.0' '^7.0'; do
      composer install &&
      composer update \
        --with "doctrine/dbal:${doctrine_dbal_ver}" \
        --with "doctrine/persistence:${doctrine_persistence}" \
        --with "symfony/console:${symfony_console_version}"&&
      composer run checks || exit $?
    done
  done
done