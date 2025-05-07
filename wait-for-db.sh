#!/bin/bash
# wait-for-db.sh - Wait for database to be ready

set -e

# Get database type from environment
DB_TYPE="${DB_TYPE:-mysql}"
if [[ $DB_HOST == dpg-* ]]; then
  # Render PostgreSQL instance
  DB_TYPE="pgsql"
fi

# Log which database we're connecting to
echo "Waiting for $DB_TYPE database at $DB_HOST to become available..."

# Try connection based on database type
if [ "$DB_TYPE" = "pgsql" ]; then
  # PostgreSQL
  until PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -c '\q' 2>/dev/null; do
    >&2 echo "PostgreSQL is unavailable - sleeping"
    sleep 1
  done
else
  # MySQL
  until mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e 'SELECT 1' 2>/dev/null; do
    >&2 echo "MySQL is unavailable - sleeping"
    sleep 1
  done
fi

>&2 echo "Database is up - executing command"
exec "$@" 