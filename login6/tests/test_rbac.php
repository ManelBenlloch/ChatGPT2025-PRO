#!/usr/bin/env php
<?php
/**
 * Simple test script to verify RBAC functionality
 * Run from login6 directory: php tests/test_rbac.php
 */

// Include bootstrap
require_once __DIR__ . '/../app_boot.php';
require_once app_path('app/Models/Role.php');
require_once app_path('app/Models/Permission.php');

echo "\n=================================\n";
echo "RBAC System Test\n";
echo "=================================\n\n";

$roleModel = new Role();
$permissionModel = new Permission();

// Test 1: Check roles exist
echo "Test 1: Checking system roles...\n";
$systemRoles = ['root', 'admin', 'personal', 'user'];
$allPass = true;

foreach ($systemRoles as $roleName) {
    $role = $roleModel->getRoleByName($roleName);
    if ($role && $role->is_system_role == 1) {
        echo "  ✓ Role '$roleName' exists and is_system_role = 1\n";
    } else {
        echo "  ✗ Role '$roleName' missing or not a system role\n";
        $allPass = false;
    }
}

if ($allPass) {
    echo "  PASSED: All system roles exist\n\n";
} else {
    echo "  FAILED: Some system roles are missing\n\n";
    exit(1);
}

// Test 2: Check permissions exist
echo "Test 2: Checking key permissions...\n";
$keyPermissions = ['manage_users', 'manage_roles', 'view_dashboard', 'manage_2fa'];
$allPass = true;

foreach ($keyPermissions as $permName) {
    $perm = $permissionModel->getByName($permName);
    if ($perm) {
        echo "  ✓ Permission '$permName' exists\n";
    } else {
        echo "  ✗ Permission '$permName' missing\n";
        $allPass = false;
    }
}

if ($allPass) {
    echo "  PASSED: Key permissions exist\n\n";
} else {
    echo "  FAILED: Some permissions are missing\n\n";
    exit(1);
}

// Test 3: Check role has permissions
echo "Test 3: Checking admin role has permissions...\n";
$adminRole = $roleModel->getRoleByName('admin');
if ($adminRole) {
    $permissions = $roleModel->getPermissions($adminRole->id);
    $count = count($permissions);
    if ($count > 0) {
        echo "  ✓ Admin role has $count permissions assigned\n";
        echo "  PASSED: Role-permission mapping works\n\n";
    } else {
        echo "  ✗ Admin role has no permissions assigned\n";
        echo "  FAILED: Role-permission mapping issue\n\n";
        exit(1);
    }
} else {
    echo "  ✗ Admin role not found\n";
    echo "  FAILED\n\n";
    exit(1);
}

// Test 4: Test permission checking (if we have a root user)
echo "Test 4: Testing permission checking...\n";
try {
    $stmt = $GLOBALS['pdo']->prepare("SELECT id FROM users WHERE role = 'root' LIMIT 1");
    $stmt->execute();
    $rootUser = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($rootUser) {
        // Root should have ALL permissions
        if ($permissionModel->userHasPermission($rootUser->id, 'manage_users')) {
            echo "  ✓ Root user has manage_users permission\n";
        } else {
            echo "  ✗ Root user missing manage_users permission (should always have it)\n";
            $allPass = false;
        }
        
        if ($permissionModel->userHasPermission($rootUser->id, 'nonexistent_permission')) {
            echo "  ✓ Root user has all permissions (even nonexistent ones)\n";
            echo "  PASSED: Root permission check works\n\n";
        } else {
            echo "  ✗ Root user doesn't have all permissions\n";
            echo "  FAILED: Root permission check failed\n\n";
            exit(1);
        }
    } else {
        echo "  ⚠ No root user found to test, skipping...\n";
        echo "  SKIPPED\n\n";
    }
} catch (Exception $e) {
    echo "  ✗ Error testing permissions: " . $e->getMessage() . "\n";
    echo "  FAILED\n\n";
    exit(1);
}

// Test 5: Test system role protection
echo "Test 5: Testing system role protection...\n";
$rootRole = $roleModel->getRoleByName('root');
if ($rootRole) {
    // Try to delete root role (should fail)
    $deleted = $roleModel->deleteRole($rootRole->id);
    if (!$deleted) {
        echo "  ✓ Cannot delete system role (root)\n";
        echo "  PASSED: System role protection works\n\n";
    } else {
        echo "  ✗ System role was deleted (should be protected)\n";
        echo "  FAILED: System role protection broken\n\n";
        exit(1);
    }
} else {
    echo "  ✗ Root role not found\n";
    echo "  FAILED\n\n";
    exit(1);
}

// Summary
echo "=================================\n";
echo "ALL TESTS PASSED ✓\n";
echo "=================================\n\n";
echo "RBAC system is working correctly!\n\n";

exit(0);
