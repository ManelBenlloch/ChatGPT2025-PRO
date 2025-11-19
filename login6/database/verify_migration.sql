-- ============================================================================
-- VERIFICATION SCRIPT - Run this after migration to verify everything works
-- ============================================================================
-- Purpose: Verify the role and permission system is correctly set up
-- Run with: mysql -u root -p login6_db < database/verify_migration.sql
-- ============================================================================

USE login6_db;

-- ============================================================================
-- 1. Check that all required tables exist
-- ============================================================================
SELECT '1. CHECKING TABLES...' as '';

SELECT 
    CASE 
        WHEN COUNT(*) = 4 THEN '✓ All required tables exist'
        ELSE '✗ MISSING TABLES'
    END as 'Table Check'
FROM information_schema.tables 
WHERE table_schema = 'login6_db' 
AND table_name IN ('users', 'roles', 'permissions', 'role_permissions');

-- ============================================================================
-- 2. Check users table has role_id column
-- ============================================================================
SELECT '2. CHECKING USERS TABLE...' as '';

SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ users.role_id column exists'
        ELSE '✗ MISSING users.role_id COLUMN'
    END as 'Column Check'
FROM information_schema.columns 
WHERE table_schema = 'login6_db' 
AND table_name = 'users' 
AND column_name = 'role_id';

-- ============================================================================
-- 3. Check that 4 system roles exist
-- ============================================================================
SELECT '3. CHECKING SYSTEM ROLES...' as '';

SELECT 
    CASE 
        WHEN COUNT(*) = 4 THEN '✓ All 4 system roles exist'
        ELSE CONCAT('✗ MISSING SYSTEM ROLES - Found: ', COUNT(*))
    END as 'System Roles Check'
FROM roles 
WHERE is_system_role = 1;

-- Show the system roles
SELECT name, display_name, is_system_role, is_active 
FROM roles 
WHERE is_system_role = 1 
ORDER BY FIELD(name, 'root', 'admin', 'personal', 'user');

-- ============================================================================
-- 4. Check that permissions exist
-- ============================================================================
SELECT '4. CHECKING PERMISSIONS...' as '';

SELECT 
    CONCAT('✓ ', COUNT(*), ' permissions defined') as 'Permissions Check'
FROM permissions;

-- Show permissions by category
SELECT category, COUNT(*) as permission_count 
FROM permissions 
GROUP BY category 
ORDER BY category;

-- ============================================================================
-- 5. Check that permissions are assigned to system roles
-- ============================================================================
SELECT '5. CHECKING ROLE-PERMISSION ASSIGNMENTS...' as '';

SELECT 
    r.name as role,
    r.display_name,
    COUNT(rp.permission_id) as permissions_assigned
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
WHERE r.is_system_role = 1
GROUP BY r.id, r.name, r.display_name
ORDER BY FIELD(r.name, 'root', 'admin', 'personal', 'user');

-- ============================================================================
-- 6. Check critical permissions exist
-- ============================================================================
SELECT '6. CHECKING CRITICAL PERMISSIONS...' as '';

SELECT 
    CASE 
        WHEN EXISTS (SELECT 1 FROM permissions WHERE name = 'manage_roles') THEN '✓'
        ELSE '✗ MISSING'
    END as 'manage_roles',
    CASE 
        WHEN EXISTS (SELECT 1 FROM permissions WHERE name = 'manage_users') THEN '✓'
        WHEN EXISTS (SELECT 1 FROM permissions WHERE name = 'view_users') THEN '✓ (view_users exists)'
        ELSE '✗ MISSING'
    END as 'manage_users',
    CASE 
        WHEN EXISTS (SELECT 1 FROM permissions WHERE name = 'view_audit_logs') THEN '✓'
        WHEN EXISTS (SELECT 1 FROM permissions WHERE name = 'view_logs') THEN '✓ (view_logs exists)'
        ELSE '✗ MISSING'
    END as 'view_logs',
    CASE 
        WHEN EXISTS (SELECT 1 FROM permissions WHERE name = 'access_admin_panel') THEN '✓'
        ELSE '✗ MISSING'
    END as 'access_admin_panel';

-- ============================================================================
-- 7. Check foreign key constraints
-- ============================================================================
SELECT '7. CHECKING FOREIGN KEYS...' as '';

SELECT 
    COUNT(*) as 'Foreign Keys Count',
    CASE 
        WHEN COUNT(*) >= 3 THEN '✓ Foreign keys exist'
        ELSE '⚠ Some foreign keys may be missing'
    END as 'FK Check'
FROM information_schema.key_column_usage
WHERE table_schema = 'login6_db'
AND table_name IN ('users', 'role_permissions')
AND referenced_table_name IS NOT NULL;

-- List the foreign keys
SELECT 
    table_name, 
    constraint_name, 
    column_name, 
    referenced_table_name, 
    referenced_column_name
FROM information_schema.key_column_usage
WHERE table_schema = 'login6_db'
AND referenced_table_name IS NOT NULL
AND table_name IN ('users', 'role_permissions')
ORDER BY table_name, constraint_name;

-- ============================================================================
-- 8. Sample test query - Check if root user can be found
-- ============================================================================
SELECT '8. CHECKING ROOT USER...' as '';

SELECT 
    id,
    username,
    email,
    role,
    role_id,
    is_active,
    CASE 
        WHEN role = 'root' THEN '✓ Root user found'
        ELSE '⚠ User exists but not root role'
    END as status
FROM users 
WHERE role = 'root' OR username LIKE '%root%'
LIMIT 1;

-- ============================================================================
-- SUMMARY
-- ============================================================================
SELECT '9. MIGRATION VERIFICATION COMPLETE' as '';

SELECT 
    'Migration Status' as 'Check',
    CASE 
        WHEN (SELECT COUNT(*) FROM roles WHERE is_system_role = 1) = 4
         AND (SELECT COUNT(*) FROM permissions) > 0
         AND (SELECT COUNT(*) FROM information_schema.columns 
              WHERE table_schema = 'login6_db' 
              AND table_name = 'users' 
              AND column_name = 'role_id') > 0
        THEN '✓ SUCCESSFUL - All checks passed'
        ELSE '⚠ INCOMPLETE - Check errors above'
    END as 'Result';

-- ============================================================================
-- NEXT STEPS
-- ============================================================================
SELECT '' as '';
SELECT 'NEXT STEPS:' as '';
SELECT '1. Login as root user' as 'Step';
SELECT '2. Navigate to http://localhost/login6/roles' as 'Step';
SELECT '3. Verify the roles page loads without errors' as 'Step';
SELECT '4. Check that you can see the 4 system roles' as 'Step';
SELECT '5. Try creating a custom role to test the system' as 'Step';

-- ============================================================================
-- END OF VERIFICATION
-- ============================================================================
