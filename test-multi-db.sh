#!/usr/bin/env bash

waiting() {
  SLEEP="${1:?Timeout not set}"

  echo "Waiting ${SLEEP} sec"

  for i in $(seq 1 "${SLEEP}"); do
    sleep 1
    echo -n '.'
  done

  echo
}

DOCKER_COMPOSE="${DOCKER_COMPOSE:-docker compose}"

############################################################
# Run SQLite
############################################################

echo 'Run tests for SQLite'
composer run phpunit

############################################################
# Run Postgres
############################################################

echo 'Run tests for Postgres'
POSTGRES_PORT="${POSTGRES_PORT:-5432}"
POSTGRES_PORT="${POSTGRES_PORT:?}" ${DOCKER_COMPOSE} up -d postgres || exit $?
waiting 5 # Waiting db

DATABASE_URL="pdo-pgsql://postgres:123@127.0.0.1:${POSTGRES_PORT:?}/views-sync" composer run phpunit || {
  EXIT_CODE=$?
  ${DOCKER_COMPOSE} down
  exit $?
}
${DOCKER_COMPOSE} down

############################################################
# Run MySQL
############################################################

echo 'Run tests for MySQL'
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_PORT="${MYSQL_PORT:?}" ${DOCKER_COMPOSE} up -d mysql || exit $?
waiting 10 # Waiting db

DATABASE_URL="pdo-mysql://mysql:123@127.0.0.1:${MYSQL_PORT:?}/views-sync" composer run phpunit || {
  EXIT_CODE=$?
  ${DOCKER_COMPOSE} down
  exit $?
}
${DOCKER_COMPOSE} down
