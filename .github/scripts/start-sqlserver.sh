#!/usr/bin/env bash
# Starts a throw-away SQL Server container for CI with a random SA password
# (generated per run, masked in the logs) and creates the given databases
# with READ_COMMITTED_SNAPSHOT, as recommended for EdisonPortal.
set -euo pipefail

IMAGE="${MSSQL_IMAGE:-mcr.microsoft.com/mssql/server:2022-latest}"
PASSWORD="Ci$(openssl rand -hex 16)!a1"
echo "::add-mask::${PASSWORD}"

docker run -d --name mssql -e ACCEPT_EULA=Y -e MSSQL_PID=Developer -e MSSQL_SA_PASSWORD="${PASSWORD}" -p 1433:1433 "${IMAGE}" > /dev/null

for attempt in $(seq 1 60); do
  if docker exec mssql /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "${PASSWORD}" -C -Q "SELECT 1" > /dev/null 2>&1; then
    break
  fi
  if [ "${attempt}" -eq 60 ]; then echo "SQL Server did not start" >&2; docker logs mssql >&2; exit 1; fi
  sleep 2
done

for db in "$@"; do
  docker exec mssql /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "${PASSWORD}" -C -b -I \
    -Q "CREATE DATABASE [${db}]; ALTER DATABASE [${db}] SET READ_COMMITTED_SNAPSHOT ON;"
done

{
  echo "DB_HOST=127.0.0.1"
  echo "DB_PORT=1433"
  echo "DB_USERNAME=sa"
  echo "DB_PASSWORD=${PASSWORD}"
  echo "DB_TRUST_SERVER_CERTIFICATE=true"
} >> "${GITHUB_ENV}"
