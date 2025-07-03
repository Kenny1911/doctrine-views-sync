#!/usr/bin/env bash

DOCKER_COMPOSE="${DOCKER_COMPOSE:-docker compose}"

# Run SQLite
echo 'Run tests for SQLite'
composer run phpunit

# Run Postgres
echo 'Run tests for Postgres'
POSTGRES_PORT="${POSTGRES_PORT:-5432}"
POSTGRES_PORT="127.0.0.1:${POSTGRES_PORT:?}" ${DOCKER_COMPOSE} up -d postgres || exit $?
sleep 5 # Wait starting db

DATABASE_URL="pdo-pgsql://postgres:123@127.0.0.1:${POSTGRES_PORT:?}/views-sync" composer run phpunit || {
  EXIT_CODE=$?
  ${DOCKER_COMPOSE} down
  exit $?
}
${DOCKER_COMPOSE} down
