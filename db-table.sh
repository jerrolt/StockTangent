#!/usr/bin/env bash
#
# mysql-db-manager.sh
#
# A portable MySQL maintenance script for macOS (Unix) and Ubuntu Linux.
#
# Modes:
#   -w / --wipe-data          Remove all data from every table + reset AUTO_INCREMENT
#   -d / --drop-all           Drop all tables (data + structure)
#   -t / --table NAME         Remove data AND drop only the specified table
#   -T / --wipe-table NAME    Remove data from only the specified table
#                             (keeps the table, resets AUTO_INCREMENT)
#
# Connection parameters (required):
#   -n / --db-name NAME
#   -u / --db-user USER
#   -p / --db-password PASS
#   -H / --host HOST       (optional, default: localhost)
#   -P / --port PORT       (optional, default: 3306)
#   -h / --help            Show usage
#
# Examples:
#   ./mysql-db-manager.sh -n stocktangent -u root -p secret -w
#   ./mysql-db-manager.sh -n stocktangent -u root -p secret -d
#   ./mysql-db-manager.sh -n stocktangent -u root -p secret -t vehicles
#   ./mysql-db-manager.sh -n stocktangent -u root -p secret -T vehicles
#



# ONLYWipe data from 'vehicles' but keep the table (AUTO_INCREMENT reset to 1)
#./mysql-db-manager.sh -n stocktangent -u root -p secret -T vehicles

# Contrast: this BOTH wipes all data from 'vehicles' AND drops the table 'vehicles'
# ./mysql-db-manager.sh -n stocktangent -u root -p secret -t vehicles
# set -euo pipefail



# ---------------------------------------------------------------------------
# Defaults
# ---------------------------------------------------------------------------
DB_NAME=""
DB_USER=""
DB_PASS=""
DB_HOST="localhost"
DB_PORT="3306"
MODE=""
TARGET_TABLE=""

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------
usage() {
  cat <<EOF
Usage: $(basename "$0") -n DB_NAME -u DB_USER -p DB_PASS [OPTIONS]

Required:
  -n, --db-name       NAME        Database name
  -u, --db-user       USER        MySQL username
  -p, --db-password   PASS        MySQL password

Optional:
  -H, --host          HOST        MySQL host (default: localhost)
  -P, --port          PORT        MySQL port (default: 3306)

Mode (choose exactly one):
  -w, --wipe-data                 Remove all data from every table and reset AUTO_INCREMENT
  -d, --drop-all                  Drop all tables (data + structure)
  -t, --table         NAME        Remove data AND drop only the specified table
  -T, --wipe-table    NAME        Remove data from only the specified table
                                  (keeps the table, resets AUTO_INCREMENT)

  -h, --help                      Show this help

Examples:
  $(basename "$0") -n stocktangent -u root -p secret -w
  $(basename "$0") -n stocktangent -u root -p secret -d
  $(basename "$0") -n stocktangent -u root -p secret -t vehicles
  $(basename "$0") -n stocktangent -u root -p secret -T vehicles
EOF
}

die() {
  echo "ERROR: $*" >&2
  exit 1
}

# Run a SQL statement against the target database.
# Reads SQL from stdin.
run_sql() {
  mysql \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USER" \
    --password="$DB_PASS" \
    --database="$DB_NAME" \
    --batch \
    --skip-column-names \
    "$@"
}

# Fetch the list of base tables (excludes views) from the target database.
list_tables() {
  run_sql <<SQL
SELECT table_name
FROM information_schema.tables
WHERE table_schema = '$DB_NAME'
  AND table_type = 'BASE TABLE'
ORDER BY table_name;
SQL
}

# Verify the mysql client is installed.
check_dependencies() {
  command -v mysql >/dev/null 2>&1 || die "'mysql' client not found in PATH. Install it first."
}

# Verify the database exists and credentials are valid.
check_connection() {
  if ! mysql \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --user="$DB_USER" \
        --password="$DB_PASS" \
        --batch \
        --skip-column-names \
        -e "SELECT 1 FROM information_schema.schemata WHERE schema_name = '$DB_NAME';" \
        | grep -q '^1$'; then
    die "Cannot connect to database '$DB_NAME' as '$DB_USER' on $DB_HOST:$DB_PORT (or database does not exist)."
  fi
}

# Verify that a given table exists as a base table in the target database.
require_table_exists() {
  local t="$1"
  local exists
  exists="$(run_sql <<SQL
SELECT COUNT(*)
FROM information_schema.tables
WHERE table_schema = '$DB_NAME'
  AND table_name = '$t'
  AND table_type = 'BASE TABLE';
SQL
)"
  if [ "$exists" -eq 0 ]; then
    die "Table '$t' does not exist in database '$DB_NAME'."
  fi
}

# ---------------------------------------------------------------------------
# Argument parsing
# ---------------------------------------------------------------------------
if [ $# -eq 0 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -n|--db-name)      DB_NAME="$2";      shift 2 ;;
    -u|--db-user)      DB_USER="$2";      shift 2 ;;
    -p|--db-password)  DB_PASS="$2";      shift 2 ;;
    -H|--host)         DB_HOST="$2";      shift 2 ;;
    -P|--port)         DB_PORT="$2";      shift 2 ;;
    -w|--wipe-data)    MODE="wipe";       shift ;;
    -d|--drop-all)     MODE="dropall";    shift ;;
    -t|--table)        MODE="table";      TARGET_TABLE="$2"; shift 2 ;;
    -T|--wipe-table)   MODE="wipetable";  TARGET_TABLE="$2"; shift 2 ;;
    -h|--help)         usage; exit 0 ;;
    *)                 die "Unknown argument: $1 (use -h for help)" ;;
  esac
done

# ---------------------------------------------------------------------------
# Validation
# ---------------------------------------------------------------------------
[ -n "$DB_NAME" ] || die "Missing required argument: --db-name"
[ -n "$DB_USER" ] || die "Missing required argument: --db-user"
[ -n "$DB_PASS" ] || die "Missing required argument: --db-password"
[ -n "$MODE" ]    || die "Missing mode: use --wipe-data, --drop-all, --table NAME, or --wipe-table NAME"

case "$MODE" in
  table|wipetable)
    [ -n "$TARGET_TABLE" ] || die "Mode '$MODE' requires a table name."
    ;;
esac

check_dependencies
check_connection

# ---------------------------------------------------------------------------
# Modes
# ---------------------------------------------------------------------------

# A) Wipe all data from every table, then reset AUTO_INCREMENT to 1.
wipe_all_data() {
  echo ">> Mode: WIPE DATA (all tables in '$DB_NAME')"

  local tables
  tables="$(list_tables)"

  if [ -z "$tables" ]; then
    echo "   No tables found. Nothing to do."
    return
  fi

  {
    echo "SET FOREIGN_KEY_CHECKS = 0;"
    while IFS= read -r t; do
      [ -n "$t" ] || continue
      # TRUNCATE clears data AND resets AUTO_INCREMENT to 1.
      echo "TRUNCATE TABLE \`$t\`;"
    done <<< "$tables"
    echo "SET FOREIGN_KEY_CHECKS = 1;"
  } | run_sql

  echo "   Done. All tables wiped and AUTO_INCREMENT reset."
}

# B) Drop every table (data + structure) from the database.
drop_all_tables() {
  echo ">> Mode: DROP ALL TABLES (database '$DB_NAME')"

  local tables
  tables="$(list_tables)"

  if [ -z "$tables" ]; then
    echo "   No tables found. Nothing to do."
    return
  fi

  {
    echo "SET FOREIGN_KEY_CHECKS = 0;"
    while IFS= read -r t; do
      [ -n "$t" ] || continue
      echo "DROP TABLE IF EXISTS \`$t\`;"
    done <<< "$tables"
    echo "SET FOREIGN_KEY_CHECKS = 1;"
  } | run_sql

  echo "   Done. All tables dropped."
}

# C) Wipe data from a single table AND drop it.
wipe_and_drop_table() {
  local t="$TARGET_TABLE"
  echo ">> Mode: WIPE + DROP TABLE '$t' in '$DB_NAME'"

  require_table_exists "$t"

  {
    echo "SET FOREIGN_KEY_CHECKS = 0;"
    echo "TRUNCATE TABLE \`$t\`;"   # remove all data + reset AUTO_INCREMENT
    echo "DROP TABLE \`$t\`;"       # remove structure
    echo "SET FOREIGN_KEY_CHECKS = 1;"
  } | run_sql

  echo "   Done. Table '$t' wiped and dropped."
}

# D) Wipe data from a single table only (keep table + reset AUTO_INCREMENT).
wipe_single_table() {
  local t="$TARGET_TABLE"
  echo ">> Mode: WIPE TABLE '$t' only in '$DB_NAME' (table is kept)"

  require_table_exists "$t"

  {
    echo "SET FOREIGN_KEY_CHECKS = 0;"
    echo "TRUNCATE TABLE \`$t\`;"   # remove all data + reset AUTO_INCREMENT
    echo "SET FOREIGN_KEY_CHECKS = 1;"
  } | run_sql

  echo "   Done. Table '$t' wiped and AUTO_INCREMENT reset. Structure kept."
}

# ---------------------------------------------------------------------------
# Dispatch
# ---------------------------------------------------------------------------
case "$MODE" in
  wipe)      wipe_all_data ;;
  dropall)   drop_all_tables ;;
  table)     wipe_and_drop_table ;;
  wipetable) wipe_single_table ;;
  *)         die "Internal error: unknown mode '$MODE'" ;;
esac

echo ">> Finished."