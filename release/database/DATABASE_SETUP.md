# Database Setup — Hostinger MySQL / MariaDB

## Database Type

The current project uses:

```text
MySQL / MariaDB through PHP PDO
```

Connection file:

```text
backend/php/config/db.php
```

Environment variables:

```text
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASS
DB_CHARSET
```

## Create Database on Hostinger

1. Open Hostinger hPanel.
2. Go to Websites.
3. Choose the target domain.
4. Open Dashboard.
5. Go to Databases → MySQL Databases.
6. Create a new database.
7. Create a database user and strong password.
8. Assign the user to the database with full permissions.
9. Save the database name, username, host, and password in a private password manager.
10. Do not commit real credentials to GitHub.

## Environment Values

In production `.env`, set:

```text
DB_HOST=your_hostinger_mysql_host
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
DB_CHARSET=utf8mb4
```

## Migration Files

Project migrations live in:

```text
backend/php/database/migrations
```

For this release, copy/import all migration files in numeric order.

Example order:

```text
001_*.sql
002_*.sql
...
033_performance_indexes.sql
```

Run only once on a fresh production database unless the migration is explicitly safe to re-run.

## How to Import Migrations with phpMyAdmin

1. Open Hostinger hPanel.
2. Go to Databases → phpMyAdmin.
3. Choose the production database.
4. Click Import.
5. Upload the first SQL migration file.
6. Run it.
7. Repeat for each migration file in order.
8. If a migration fails, stop and review the error before continuing.

## How to Import a Full SQL File

If a full schema file exists:

```text
release/database/database_schema.sql
```

Import it into an empty database using phpMyAdmin.

If it does not exist, use the migration files from `backend/php/database/migrations`.

## Seed Data

Only safe demo data should be imported.

Rules:

```text
Do not import real private student data.
Do not import real phone numbers.
Do not import real emails unless they are demo placeholders.
Do not import real payment records.
```

If a demo seed file exists, import only in staging/demo:

```text
release/database/demo_seed_data.sql
```

## Backup Database

Before any production change:

1. Open phpMyAdmin.
2. Select the database.
3. Click Export.
4. Choose SQL format.
5. Save the file securely.

Suggested backup name:

```text
habiba_nabil_backup_YYYY_MM_DD.sql
```

## Restore Database

1. Create an empty database or clear the target database carefully.
2. Open phpMyAdmin.
3. Click Import.
4. Upload the backup SQL file.
5. Test login and dashboards after restore.

## SSH Import for Large SQL Files

If phpMyAdmin upload size is too small, use Hostinger SSH if available:

```bash
mysql -u DB_USER -p DB_NAME < backup.sql
```

Do not paste real passwords into shared documents.

## Important Notes

- The app does not use Supabase PostgreSQL in the current approved setup.
- The app does not use Replit internal `@base` database.
- Keep `utf8mb4` to support Arabic text correctly.
- Production database credentials must remain server-side only.
