# RBAC System Documentation - login6

## Overview

The login6 module implements a fully functional **Role-Based Access Control (RBAC)** system with support for **Attribute-Based Access Control (ABAC)** through user-specific permission overrides.

## Database Schema

### Tables

#### 1. `roles`
Defines both system roles and custom roles.

**Columns:**
- `id` - Primary key
- `name` - Unique internal name (e.g., 'admin', 'manager')
- `display_name` - Human-readable name
- `description` - Role description
- `is_system_role` - 1 for system roles (root, admin, personal, user), 0 for custom
- `is_active` - Role status
- `created_at`, `updated_at` - Timestamps

**System Roles (Untouchable):**
- `root` (ID: 4) - Superadministrator with all permissions
- `admin` (ID: 3) - Administrator with elevated permissions
- `personal` (ID: 2) - Staff/internal personnel
- `user` (ID: 1) - Standard user

**Protection Rules:**
- System roles CANNOT be deleted
- System roles CANNOT have their `name` field changed
- System roles CAN have permissions viewed (read-only in UI)
- System roles CAN have permissions modified via direct database for configuration

#### 2. `permissions`
Defines granular permissions for the system.

**Columns:**
- `id` - Primary key
- `name` - Unique internal name (e.g., 'manage_users')
- `display_name` - Human-readable name
- `description` - Permission description
- `category` - Grouping category (users, roles, posts, system, sessions, security)
- `created_at`, `updated_at` - Timestamps

**Permission Categories:**
- `users` - User management permissions
- `roles` - Role/permission management
- `posts` - Content management (example)
- `system` - System-level permissions
- `sessions` - Session management
- `security` - Security features (2FA, WAF, etc.)

#### 3. `role_permissions`
Maps permissions to roles (many-to-many relationship).

**Columns:**
- `id` - Primary key
- `role_id` - Foreign key to roles
- `permission_id` - Foreign key to permissions
- `granted_by` - Optional: ID of user who granted the permission

**Unique Constraint:** `(role_id, permission_id)`

#### 4. `user_permissions`
Optional user-specific permission overrides (ABAC layer).

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `permission_id` - Foreign key to permissions
- `is_granted` - 1 to grant, 0 to revoke (relative to role permissions)
- `created_at` - Timestamp

**Unique Constraint:** `(user_id, permission_id)`

**Purpose:** Allows granting specific permissions to users that their role doesn't have, or revoking permissions that their role does have.

#### 5. `users`
User table with role assignment.

**Relevant Columns:**
- `role` - ENUM('user', 'personal', 'admin', 'root') - Legacy system role
- `role_id` - INT - Foreign key to roles table for custom roles

**Role Resolution:**
1. If `role_id` is set, use that role's permissions
2. Otherwise, use the system role specified in `role` field
3. Root users (`role = 'root'`) always have all permissions regardless of role_id

## Installation

### Step 1: Run the Base Schema
Run `INSTALAR_LOGIN6_DB.sql` to create the base database structure if starting fresh.

### Step 2: Run the RBAC Update
Run `RBAC_SCHEMA_UPDATE.sql` to add/update RBAC tables and seed data:

```bash
mysql -u your_user -p login6_db < database/RBAC_SCHEMA_UPDATE.sql
```

Or via phpMyAdmin: Import the `RBAC_SCHEMA_UPDATE.sql` file.

**What it does:**
- Creates `roles`, `permissions`, `role_permissions`, `user_permissions` tables if they don't exist
- Adds `role_id` column to `users` table if missing
- Seeds 4 system roles
- Seeds comprehensive set of permissions across all categories
- Assigns default permissions to each system role

### Step 3: Verify Installation
Check the summary output from the script or run:

```sql
SELECT 
    (SELECT COUNT(*) FROM roles) as Total_Roles,
    (SELECT COUNT(*) FROM permissions) as Total_Permissions,
    (SELECT COUNT(*) FROM role_permissions) as Total_Role_Permissions;
```

Expected results:
- **Total_Roles:** 4 (or more if you have custom roles)
- **Total_Permissions:** 33 (comprehensive set)
- **Total_Role_Permissions:** ~110 (all roles configured)

## Usage

### PHP Models

#### Check User Permission
```php
require_once app_path('app/Models/Permission.php');
$permissionModel = new Permission();

if ($permissionModel->userHasPermission($userId, 'manage_users')) {
    // User has permission
}
```

#### Get All User Permissions
```php
$permissions = $permissionModel->getUserPermissions($userId);
// Returns array of permission objects including role + user-specific overrides
```

#### Check Multiple Permissions
```php
// User has ANY of these permissions
if ($permissionModel->userHasAnyPermission($userId, ['edit_users', 'delete_users'])) {
    // ...
}

// User has ALL of these permissions
if ($permissionModel->userHasAllPermissions($userId, ['view_users', 'edit_users'])) {
    // ...
}
```

### Middleware

#### Protect Routes
```php
require_once app_path('app/Middleware/PermissionMiddleware.php');

// Require specific permission
PermissionMiddleware::requirePermission('manage_roles');

// Require any of these permissions
PermissionMiddleware::requireAnyPermission(['manage_users', 'view_users']);

// Require all of these permissions
PermissionMiddleware::requireAllPermissions(['manage_users', 'delete_users']);

// Check permission without blocking (returns bool)
if (PermissionMiddleware::hasPermission('manage_roles')) {
    // Show admin UI
}
```

### Role Management

#### Get All Roles
```php
require_once app_path('app/Models/Role.php');
$roleModel = new Role();

// Get all active roles including system roles
$roles = $roleModel->getAllRoles(true);

// Get only custom roles (exclude system roles)
$customRoles = $roleModel->getAllRoles(false);
```

#### Create Custom Role
```php
$roleId = $roleModel->createRole([
    'name' => 'manager',
    'display_name' => 'Manager',
    'description' => 'Team manager role'
]);
```

#### Assign Permissions to Role
```php
$permissionIds = [1, 2, 3, 5]; // Array of permission IDs
$roleModel->assignPermissions($roleId, $permissionIds, $grantedByUserId);
```

**Note:** System roles will reject permission modifications (returns false).

#### Check Role Permission
```php
if ($roleModel->hasPermission($roleId, 'manage_users')) {
    // Role has this permission
}
```

## Permission Hierarchy

### Permission Resolution Order
1. **Root Check:** If user has `role = 'root'`, return TRUE for all permissions
2. **User Override Check:** Check `user_permissions` table for explicit grants/revokes
3. **Role Permission Check:** Check role permissions via `role_id` or system role

### Example Scenarios

#### Scenario 1: Standard User
- User: `role = 'user'`, `role_id = NULL`
- Permissions: Those assigned to system role 'user' (ID: 1)

#### Scenario 2: Custom Role
- User: `role = 'user'`, `role_id = 5` (custom 'manager' role)
- Permissions: Those assigned to role ID 5

#### Scenario 3: User with Override
- User: `role = 'user'`, `role_id = NULL`
- User Permissions: `user_permissions` has grant for 'manage_users'
- Result: Has all 'user' role permissions PLUS 'manage_users'

#### Scenario 4: Root User
- User: `role = 'root'`
- Permissions: ALL permissions regardless of database configuration

## Web Interface

### Access Role Management
Navigate to: `/roles`

**Requirements:**
- Must be authenticated
- Must have `manage_roles` permission

### Available Actions

#### List Roles
- Shows all system and custom roles
- Displays user count per role
- Shows active/inactive status

#### Create Custom Role
- Click "Crear Nuevo Rol"
- Fill in name, display name, description
- Redirects to permissions screen after creation

#### View Permissions (System Roles)
- Click "👁️ Ver Permisos" on system role card
- Shows all assigned permissions (read-only)
- Cannot modify system role permissions via UI

#### Manage Permissions (Custom Roles)
- Click "🔑 Permisos" on custom role card
- Select/deselect permissions
- Click "💾 Guardar Permisos" to save

#### Edit Custom Role
- Click "✏️ Editar" on custom role card
- Update display name, description, active status
- Cannot change internal `name` field

#### Delete Custom Role
- Click "🗑️ Eliminar" on custom role card (only if no users assigned)
- Requires confirmation
- Only available for custom roles with 0 users

## Security Considerations

### System Role Protection
- System roles are protected at the model level
- `is_system_role = 1` prevents deletion and name changes
- UI hides delete/edit buttons for system roles
- Backend rejects modification attempts

### Root Privilege
- Root users bypass all permission checks
- This is hardcoded in `Permission::userHasPermission()`
- Cannot be revoked via database

### Permission Checking
- Always check permissions in controllers/middleware
- Never rely solely on UI hiding for security
- Use `PermissionMiddleware::requirePermission()` for route protection

### Database Constraints
- Foreign keys ensure referential integrity
- Unique constraints prevent duplicate assignments
- CASCADE deletes clean up related records

## Extending the System

### Adding New Permissions
```sql
INSERT INTO permissions (name, display_name, description, category) VALUES
('new_permission', 'New Permission', 'Description here', 'category_name');
```

### Adding New Categories
Just use a new category name when creating permissions. The system will automatically group them.

### Custom Permission Logic
Extend the `Permission` model to add custom permission checking logic:

```php
public function canManageUser($userId, $targetUserId) {
    // Custom logic: admins can manage users, but not other admins/root
    if (!$this->userHasPermission($userId, 'manage_users')) {
        return false;
    }
    
    // Get target user role
    $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $targetUser = $stmt->fetch(PDO::FETCH_OBJ);
    
    // Prevent managing root/admin unless you're root
    // ... custom logic here ...
    
    return true;
}
```

## Troubleshooting

### Issue: "Unknown column 'role_id'"
**Solution:** Run `RBAC_SCHEMA_UPDATE.sql` which adds the column if missing.

### Issue: "Unknown column 'granted_by'"
**Solution:** This column is optional. The PHP code handles it gracefully if missing.

### Issue: No permissions showing for user
**Possible causes:**
1. User has no role assigned (`role_id = NULL` and `role` is NULL)
2. Role has no permissions assigned
3. Role is inactive (`is_active = 0`)

**Check:**
```sql
SELECT u.id, u.email, u.role, u.role_id, r.name as role_name, r.is_active
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
WHERE u.id = YOUR_USER_ID;
```

### Issue: Cannot access /roles page
**Possible causes:**
1. User doesn't have `manage_roles` permission
2. Not authenticated

**Grant permission manually:**
```sql
-- For user ID 2, grant manage_roles permission
INSERT INTO user_permissions (user_id, permission_id, is_granted)
SELECT 2, id, 1 FROM permissions WHERE name = 'manage_roles';
```

## Support

For issues, questions, or feature requests related to the RBAC system, please refer to the main project documentation or contact the development team.

---

**Last Updated:** 2025-11-19
**Version:** 1.0
**Compatible with:** login6_db schema v2.0+
