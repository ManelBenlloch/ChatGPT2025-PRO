# RBAC System - Quick Start Guide

## Installation Steps

### 1. Database Setup

If you're starting fresh or need to update your database with RBAC tables:

```bash
# Import the RBAC schema update script
mysql -u your_username -p login6_db < login6/database/RBAC_SCHEMA_UPDATE.sql
```

Or via **phpMyAdmin**:
1. Select `login6_db` database
2. Go to "Import" tab
3. Choose file: `login6/database/RBAC_SCHEMA_UPDATE.sql`
4. Click "Go"

### 2. Verify Installation

Run the test script to verify everything is working:

```bash
cd login6
php tests/test_rbac.php
```

Expected output:
```
=================================
RBAC System Test
=================================

Test 1: Checking system roles...
  ✓ Role 'root' exists and is_system_role = 1
  ✓ Role 'admin' exists and is_system_role = 1
  ✓ Role 'personal' exists and is_system_role = 1
  ✓ Role 'user' exists and is_system_role = 1
  PASSED: All system roles exist

Test 2: Checking key permissions...
  ✓ Permission 'manage_users' exists
  ✓ Permission 'manage_roles' exists
  ✓ Permission 'view_dashboard' exists
  ✓ Permission 'manage_2fa' exists
  PASSED: Key permissions exist

Test 3: Checking admin role has permissions...
  ✓ Admin role has X permissions assigned
  PASSED: Role-permission mapping works

Test 4: Testing permission checking...
  ✓ Root user has manage_users permission
  ✓ Root user has all permissions (even nonexistent ones)
  PASSED: Root permission check works

Test 5: Testing system role protection...
  ✓ Cannot delete system role (root)
  PASSED: System role protection works

=================================
ALL TESTS PASSED ✓
=================================

RBAC system is working correctly!
```

### 3. Access the Web Interface

1. **Log in** as a root or admin user
2. Navigate to: `http://your-domain/login6/roles`
3. You should see the roles management interface

### 4. Grant Access to Roles Management

If your user doesn't have access to the roles page, grant the permission manually:

```sql
-- For user ID X, grant manage_roles permission
INSERT INTO user_permissions (user_id, permission_id, is_granted)
SELECT X, id, 1 FROM permissions WHERE name = 'manage_roles';
```

Replace `X` with your actual user ID.

## What Was Installed

### Tables Created/Updated
- ✅ `roles` - System and custom roles
- ✅ `permissions` - Granular permissions
- ✅ `role_permissions` - Role-permission mappings
- ✅ `user_permissions` - User-specific permission overrides
- ✅ `users.role_id` column added (if missing)

### Data Seeded
- ✅ 4 system roles (root, admin, personal, user)
- ✅ 33+ permissions across 6 categories:
  - users (7 permissions)
  - roles (3 permissions)
  - posts (7 permissions)
  - system (7 permissions)
  - sessions (4 permissions)
  - security (4 permissions)
- ✅ Default permission assignments for all roles

## Common Use Cases

### Create a Custom Role
1. Go to `/roles`
2. Click "Crear Nuevo Rol"
3. Fill in:
   - **Name:** Internal identifier (lowercase, underscores only)
   - **Display Name:** User-friendly name
   - **Description:** What this role is for
4. Click "Crear Rol y Asignar Permisos"
5. Select permissions for the role
6. Click "Guardar Permisos"

### Assign a Role to a User
```sql
UPDATE users 
SET role_id = (SELECT id FROM roles WHERE name = 'your_role_name')
WHERE id = user_id;
```

### View System Role Permissions
1. Go to `/roles`
2. Click "👁️ Ver Permisos" on any system role (root, admin, personal, user)
3. View permissions in read-only mode

### Grant User-Specific Permission Override
```sql
-- Grant manage_users to user ID 5 (even if their role doesn't have it)
INSERT INTO user_permissions (user_id, permission_id, is_granted)
SELECT 5, id, 1 FROM permissions WHERE name = 'manage_users';

-- Revoke delete_users from user ID 5 (even if their role has it)
INSERT INTO user_permissions (user_id, permission_id, is_granted)
SELECT 5, id, 0 FROM permissions WHERE name = 'delete_users';
```

## Troubleshooting

### "Unknown column 'role_id'" Error
Run the RBAC schema update script - it adds this column automatically.

### Can't Access /roles Page
Grant yourself the `manage_roles` permission:
```sql
INSERT INTO user_permissions (user_id, permission_id, is_granted)
SELECT YOUR_USER_ID, id, 1 FROM permissions WHERE name = 'manage_roles';
```

### No Permissions Showing for a Role
Check that:
1. Role exists and `is_active = 1`
2. Permissions are assigned in `role_permissions` table
3. Database connection is working

### Test Script Fails
1. Verify database connection in `config/config.php`
2. Ensure RBAC_SCHEMA_UPDATE.sql was imported successfully
3. Check MySQL error logs for issues

## Next Steps

For complete documentation, see:
- **Full Documentation:** `login6/database/README_RBAC.md`
- **Database Schema:** `login6/database/RBAC_SCHEMA_UPDATE.sql`
- **Test Script:** `login6/tests/test_rbac.php`

## Support

If you encounter issues:
1. Check the troubleshooting section in README_RBAC.md
2. Run the test script to diagnose the problem
3. Verify your database structure matches the schema

---

**Quick Reference:**
- Roles Management: `/roles`
- Create Role: `/roles/create`
- View Permissions: `/roles/{id}/permissions`
- Test Script: `php tests/test_rbac.php`
