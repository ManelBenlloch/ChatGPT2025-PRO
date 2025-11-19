# Database Migration Guide - Role and Permission System

## Overview

This guide explains how to migrate an existing `login6_db` database to support the role and permission system.

## Problem Solved

**Error:** `Fatal error: Column not found: 1054 Unknown column 'role_id' in 'field list'`

This error occurred because the code expects a complete role and permission system with:
- `users.role_id` column
- `roles` table
- `permissions` table  
- `role_permissions` table

## Migration Options

### Option 1: Fresh Installation (Recommended for new deployments)

If you're setting up a new database, use the complete schema:

```sql
mysql -u root -p < database/INSTALAR_LOGIN6_DB.sql
```

This creates everything from scratch with the full role and permission system.

### Option 2: Migrate Existing Database (For existing installations)

If you already have a `login6_db` database with users and data, use the migration script:

```sql
mysql -u root -p login6_db < database/migration_add_roles_permissions.sql
```

**Important:** This migration script is:
- ✅ **Idempotent** - Safe to run multiple times
- ✅ **Non-destructive** - Doesn't delete existing data
- ✅ **Safe** - Uses IF NOT EXISTS checks for all operations

### What the Migration Does

1. **Adds `role_id` column** to the `users` table (if not exists)
2. **Creates new tables:**
   - `roles` - System roles (root, admin, personal, user) and custom roles
   - `permissions` - Granular permissions for all system features
   - `role_permissions` - Links roles to their permissions
3. **Seeds system data:**
   - 4 system roles marked as "INTOCABLES" (untouchable)
   - Comprehensive permissions across categories: users, posts, system, sessions, security
   - Default permission assignments for each system role
4. **Adds foreign key constraints** for data integrity

## System Roles (INTOCABLES)

The system has 4 special roles that **CANNOT** be:
- ❌ Deleted
- ❌ Renamed  
- ❌ Deactivated

### 1. Root
- **Permissions:** ALL (hardcoded in Permission.php)
- **Purpose:** Super administrator with complete system access
- **Notes:** The code always returns `true` for root users on any permission check

### 2. Admin
- **Permissions:** Extensive - can manage users, content, sessions, and view logs
- **Purpose:** System administrator
- **Limitation:** Cannot manage other root users

### 3. Personal
- **Permissions:** Intermediate - can view users and manage own content
- **Purpose:** Staff/personnel level access

### 4. User
- **Permissions:** Basic - can create/edit own posts and manage own sessions
- **Purpose:** Standard user access

## Permission Logic

The system uses a 3-tier permission resolution:

1. **Root override:** If `users.role = 'root'` → ALL permissions (hardcoded)
2. **Custom role:** If `users.role_id IS NOT NULL` → Permissions from that custom role
3. **System role:** If `users.role IN ('admin','personal','user')` → Permissions from system role

## Custom Roles

You can create unlimited custom roles via the `/roles` interface:
- Custom roles have `is_system_role = 0`
- Assign any combination of permissions
- Users with custom roles have `role_id` pointing to the custom role
- The `role` column defaults to 'user' for custom role users

## After Migration

1. **Verify tables exist:**
   ```sql
   SHOW TABLES LIKE 'roles';
   SHOW TABLES LIKE 'permissions';
   SHOW TABLES LIKE 'role_permissions';
   ```

2. **Check role_id column:**
   ```sql
   DESCRIBE users;
   ```

3. **Test the /roles page:**
   - Login as root user
   - Navigate to: `http://localhost/login6/roles`
   - Should display roles without errors

4. **Existing users:**
   - All existing users keep their `users.role` value
   - No `role_id` assigned (NULL by default)
   - They continue working with system roles

## Rollback

To rollback the migration (NOT RECOMMENDED if you have custom roles):

```sql
-- Remove foreign key constraints first
ALTER TABLE users DROP FOREIGN KEY fk_users_role_id;
ALTER TABLE role_permissions DROP FOREIGN KEY fk_role_permissions_role_id;
ALTER TABLE role_permissions DROP FOREIGN KEY fk_role_permissions_permission_id;
ALTER TABLE role_permissions DROP FOREIGN KEY fk_role_permissions_granted_by;

-- Drop tables
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;

-- Remove column
ALTER TABLE users DROP COLUMN role_id;
```

## Troubleshooting

### Error: "Table 'roles' already exists"
- **Solution:** This is normal - the migration checks for existing tables
- The script will skip creating existing tables

### Error: "Column 'role_id' already exists"  
- **Solution:** This is normal - the migration is idempotent
- The script will skip adding the column if it exists

### Error: "Access denied for user"
- **Solution:** Make sure you're running the migration with a MySQL user that has ALTER, CREATE privileges

### Users can't access /roles page
- **Check:** Is the user logged in as 'root'?
- **Check:** Does the `manage_roles` permission exist?
- **Run:** 
  ```sql
  SELECT * FROM permissions WHERE name = 'manage_roles';
  ```

## File Reference

- `database/migration_add_roles_permissions.sql` - Migration script (use for existing DBs)
- `database/INSTALAR_LOGIN6_DB.sql` - Complete schema (use for fresh installs)
- `database.sql` - Updated legacy schema file
- `app/Models/Permission.php` - Permission checking logic
- `app/Models/Role.php` - Role management
- `app/Controllers/RoleController.php` - Role UI and operations

## Support

If you encounter issues:
1. Check MySQL error logs
2. Verify you have MySQL 5.7+ or MariaDB 10.2+
3. Ensure the user has proper database permissions
4. Review the migration script comments for details
