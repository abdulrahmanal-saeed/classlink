# Hostinger Database Guide

## Database Type

The current approved database is:

```text
MySQL / MariaDB
```

The PHP app connects through PDO in:

```text
backend/php/config/db.php
```

## Hostinger Database Setup

1. Open Hostinger hPanel.
2. Go to Websites.
3. Choose your website/domain.
4. Open Dashboard.
5. Go to Databases → MySQL Databases.
6. Create a database.
7. Create a database user and password.
8. Assign the user to the database.
9. Save the credentials securely.
10. Add the credentials to the server `.env` file.

## Required Environment Variables

```text
DB_HOST=your_hostinger_mysql_host
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
DB_CHARSET=utf8mb4
```

## Import Using phpMyAdmin

1. Open phpMyAdmin from Hostinger.
2. Select the new database.
3. Click Import.
4. Import SQL migrations in numeric order from:

```text
backend/php/database/migrations
```

5. If a migration fails, stop and review the error before continuing.

## Migration Order

Run migrations by filename order:

```text
001_*.sql
002_*.sql
003_*.sql
...
033_performance_indexes.sql
```

## Demo Seed Data

Only import safe demo data.

Do not import:

```text
real student names
real parent phone numbers
real payment data
real private files
real AI logs
real API keys
```

If seed scripts exist, use them only for staging or demo environments.

## Large SQL Import via SSH

If phpMyAdmin cannot import a large SQL file, use SSH if your Hostinger plan supports it:

```bash
mysql -u DB_USER -p DB_NAME < database_file.sql
```

Do not write real passwords in documentation or shared screenshots.

## Backup

Before changes:

1. Open phpMyAdmin.
2. Select database.
3. Click Export.
4. Choose SQL.
5. Download and store securely.

Suggested name:

```text
habiba_nabil_backup_YYYY_MM_DD.sql
```

## Restore

1. Create a new empty database or carefully clear target database.
2. Open phpMyAdmin.
3. Click Import.
4. Upload the backup SQL file.
5. Test login, dashboards, checkout, and uploads after restore.

## Compatibility Notes

- This project is not currently configured for PostgreSQL.
- Hostinger MySQL/MariaDB is compatible with the current PHP connection layer.
- Keep `utf8mb4` for Arabic text.
- Do not use Replit internal `@base` database.
