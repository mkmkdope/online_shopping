<?php
session_start();
require_once __DIR__ . '/../sb_base.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: /login/login.php');
    exit;
}

$message = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$discountId = $_GET['id'] ?? null;

// Get current admin user's profile information
$currentAdminId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT profile_photo, username FROM users WHERE id = ?");
$stmt->execute([$currentAdminId]);
$adminInfo = $stmt->fetch();
$adminUsername = $adminInfo['username'] ?? 'Admin User';
$adminPhoto = $adminInfo['profile_photo'] ?? '';
$adminPhotoPath = $adminPhoto ? '/page/uploads/profiles/' . $adminPhoto : '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'list';
    
    if ($action === 'create') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discountType = $_POST['discount_type'] ?? 'percentage';
        $discountValue = floatval($_POST['discount_value'] ?? 0);
        $usageLimit = intval($_POST['usage_limit'] ?? 1);
        $validFrom = $_POST['valid_from'] ?? '';
        $validUntil = $_POST['valid_until'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        // Validate data
        $errors = [];
        
        if (empty($code)) {
            $errors[] = 'Discount code cannot be empty';
        }
        
        if (strlen($code) > 50) {
            $errors[] = 'Discount code cannot exceed 50 characters';
        }
        
        if ($discountValue <= 0) {
            $errors[] = 'Discount value must be greater than 0';
        }
        
        if ($discountType === 'percentage' && $discountValue > 100) {
            $errors[] = 'Percentage discount cannot exceed 100%';
        }
        
        if ($usageLimit < 1) {
            $errors[] = 'Usage limit must be greater than 0';
        }
        
        if (empty($validFrom) || empty($validUntil)) {
            $errors[] = 'Valid from and valid until dates cannot be empty';
        } elseif (strtotime($validUntil) <= strtotime($validFrom)) {
            $errors[] = 'Valid until date must be later than valid from date';
        }
        
        if (empty($errors)) {
            try {
                // Check if discount code already exists
                $stmt = $pdo->prepare("SELECT id FROM discount_codes WHERE code = ?");
                $stmt->execute([$code]);
                if ($stmt->fetch()) {
                    $error = 'Discount code already exists';
                } else {
                    // Create discount code
                    $sql = "INSERT INTO discount_codes 
                            (code, discount_type, discount_value, usage_limit, 
                             valid_from, valid_until, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $code,
                        $discountType,
                        $discountValue,
                        $usageLimit,
                        $validFrom,
                        $validUntil,
                        $status
                    ]);
                    $message = 'Discount code created successfully!';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $error = 'Creation failed: ' . $e->getMessage();
            }
        } else {
            $error = implode(', ', $errors);
        }
    } elseif ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discountType = $_POST['discount_type'] ?? 'percentage';
        $discountValue = floatval($_POST['discount_value'] ?? 0);
        $usageLimit = intval($_POST['usage_limit'] ?? 1);
        $validFrom = $_POST['valid_from'] ?? '';
        $validUntil = $_POST['valid_until'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        // Validate data
        $errors = [];
        
        if (empty($code)) {
            $errors[] = 'Discount code cannot be empty';
        }
        
        if (strlen($code) > 50) {
            $errors[] = 'Discount code cannot exceed 50 characters';
        }
        
        if ($discountValue <= 0) {
            $errors[] = 'Discount value must be greater than 0';
        }
        
        if ($discountType === 'percentage' && $discountValue > 100) {
            $errors[] = 'Percentage discount cannot exceed 100%';
        }
        
        if ($usageLimit < 1) {
            $errors[] = 'Usage limit must be greater than 0';
        }
        
        if (empty($validFrom) || empty($validUntil)) {
            $errors[] = 'Valid from and valid until dates cannot be empty';
        } elseif (strtotime($validUntil) <= strtotime($validFrom)) {
            $errors[] = 'Valid until date must be later than valid from date';
        }
        
        if (empty($errors)) {
            try {
                // Check if discount code already exists (excluding current discount)
                $stmt = $pdo->prepare("SELECT id FROM discount_codes WHERE code = ? AND id != ?");
                $stmt->execute([$code, $id]);
                if ($stmt->fetch()) {
                    $error = 'Discount code already exists';
                } else {
                    // Update discount code
                    $sql = "UPDATE discount_codes SET 
                            code = ?, 
                            discount_type = ?, 
                            discount_value = ?, 
                            usage_limit = ?, 
                            valid_from = ?, 
                            valid_until = ?, 
                            status = ? 
                            WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $code,
                        $discountType,
                        $discountValue,
                        $usageLimit,
                        $validFrom,
                        $validUntil,
                        $status,
                        $id
                    ]);
                    $message = 'Discount code updated successfully!';
                    $action = 'list';
                }
            } catch (Exception $e) {
                $error = 'Update failed: ' . $e->getMessage();
            }
        } else {
            $error = implode(', ', $errors);
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                // Check if there are usage records
                $stmt = $pdo->prepare("SELECT times_used FROM discount_codes WHERE id = ?");
                $stmt->execute([$id]);
                $discount = $stmt->fetch();
                
                if ($discount && $discount['times_used'] > 0) {
                    // If there are usage records, deactivate instead of deleting
                    $stmt = $pdo->prepare("UPDATE discount_codes SET status = 'inactive' WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Discount code deactivated (because it has usage records)';
                } else {
                    // No usage records, delete directly
                    $stmt = $pdo->prepare("DELETE FROM discount_codes WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Discount code deleted successfully!';
                }
                $action = 'list';
            } catch (Exception $e) {
                $error = 'Deletion failed: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        $currentStatus = $_POST['current_status'] ?? '';
        
        if ($id > 0 && in_array($currentStatus, ['active', 'inactive'])) {
            try {
                $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
                $stmt = $pdo->prepare("UPDATE discount_codes SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $id]);
                $message = "Discount code " . ($newStatus === 'active' ? 'activated' : 'deactivated') . " successfully";
                $action = 'list';
            } catch (Exception $e) {
                $error = 'Status update failed: ' . $e->getMessage();
            }
        }
    }
}

// Get search filters
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';

// Build query
$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "code LIKE ?";
    $params[] = "%$search%";
}

if (!empty($statusFilter) && in_array($statusFilter, ['active', 'inactive'])) {
    $whereConditions[] = "status = ?";
    $params[] = $statusFilter;
}

if (!empty($typeFilter) && in_array($typeFilter, ['percentage', 'fixed'])) {
    $whereConditions[] = "discount_type = ?";
    $params[] = $typeFilter;
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Get discount list
$discounts = [];
if ($action === 'list') {
    try {
        $sql = "SELECT id, code, discount_type, discount_value, usage_limit, 
                       times_used, valid_from, valid_until, status, created_at 
                FROM discount_codes 
                $whereClause 
                ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $discounts = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = 'Failed to get discount list: ' . $e->getMessage();
    }
}

// Get single discount information (for edit/view)
$discount = null;
if ($action === 'edit' || $action === 'view') {
    if ($discountId) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM discount_codes WHERE id = ?");
            $stmt->execute([$discountId]);
            $discount = $stmt->fetch();
            if (!$discount) {
                $error = 'Discount code does not exist';
                $action = 'list';
            }
        } catch (Exception $e) {
            $error = 'Failed to get discount information: ' . $e->getMessage();
            $action = 'list';
        }
    } else {
        $action = 'list';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SB | Discount Management</title>
<link rel="stylesheet" href="/css/sb_style.css">
<link rel="stylesheet" href="/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Custom Styles */
.discount-value {
    font-weight: bold;
    color: #e74c3c;
}

.discount-type-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.discount-type-badge.percentage {
    background: #e1f5fe;
    color: #0288d1;
}

.discount-type-badge.fixed {
    background: #f3e5f5;
    color: #7b1fa2;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.usage-progress {
    width: 80px;
    height: 8px;
    background: #ecf0f1;
    border-radius: 4px;
    overflow: hidden;
    display: inline-block;
    vertical-align: middle;
    margin: 0 5px;
}

.usage-progress-bar {
    height: 100%;
    background: #3498db;
    border-radius: 4px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.expiry-warning {
    color: #e74c3c;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.filter-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    margin-bottom: 0;
}

.quick-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.quick-action-btn {
    padding: 10px 15px;
    border-radius: 6px;
    background: white;
    border: 1px solid #ddd;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.quick-action-btn:hover {
    background: #f8f9fa;
    border-color: #3498db;
}

.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 5px;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

@media (max-width: 992px) {
    .stat-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filter-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stat-row {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        flex-wrap: wrap;
    }
}
</style>
</head>
<body>
<div style="display: flex; min-height: 100vh;">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <span class="logo-text">Chapter One</span>
        </div>
        
        <div class="admin-info">
            <div class="admin-avatar">
                <?php if ($adminPhotoPath): ?>
                    <img src="<?= htmlspecialchars($adminPhotoPath) ?>" alt="Profile Photo">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="admin-name"><?= htmlspecialchars($adminUsername) ?></div>
            <div class="admin-role">Administrator</div>
        </div>
        
        <div class="sidebar-menu">
            <a href="adminPanel.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            <a href="product_panel.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span class="menu-text">Books</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categories</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-shopping-cart"></i>
                <span class="menu-text">Orders</span>
            </a>
            <a href="admin_user.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Customers</span>
            </a>
            <a href="discount_panel.php" class="menu-item active">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Discounts</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reports</span>
            </a>
            <a href="admin_profile.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
            </a>
            <a href="/login/logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Discount Management</h1>
            <p class="subtitle">Manage discount codes and promotions</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message message-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message message-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="?action=create" class="quick-action-btn" style="background: #28a745; color: white; border-color: #28a745;">
                    <i class="fas fa-plus"></i> Add New Discount
                </a>
                <a href="?status=active" class="quick-action-btn">
                    <i class="fas fa-check-circle"></i> Active Discounts
                </a>
                <a href="?status=inactive" class="quick-action-btn">
                    <i class="fas fa-times-circle"></i> Inactive Discounts
                </a>
            </div>

            <!-- Statistics -->
            <?php
            // Get statistics
            try {
                $statsSql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN valid_until < NOW() AND status = 'active' THEN 1 ELSE 0 END) as expired,
                    SUM(times_used) as total_used,
                    SUM(CASE WHEN discount_type = 'percentage' THEN 1 ELSE 0 END) as percentage_count,
                    SUM(CASE WHEN discount_type = 'fixed' THEN 1 ELSE 0 END) as fixed_count
                FROM discount_codes";
                $statsStmt = $pdo->query($statsSql);
                $stats = $statsStmt->fetch();
            } catch (Exception $e) {
                $stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'expired' => 0, 'total_used' => 0];
            }
            ?>
            
            <div class="stat-row">
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                    <div class="stat-label">Total Discounts</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['active'] ?? 0 ?></div>
                    <div class="stat-label">Active</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['inactive'] ?? 0 ?></div>
                    <div class="stat-label">Inactive</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['total_used'] ?? 0 ?></div>
                    <div class="stat-label">Total Uses</div>
                </div>
            </div>

            <!-- Filter and Search -->
            <div class="filter-section">
                <form method="GET" class="filter-row">
                    <input type="hidden" name="action" value="list">
                    
                    <div class="filter-group">
                        <label>Search Code</label>
                        <input type="text" name="search" placeholder="Search discount code..." 
                               value="<?= htmlspecialchars($search) ?>" class="form-control">
                    </div>
                    
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Discount Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="percentage" <?= $typeFilter === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                            <option value="fixed" <?= $typeFilter === 'fixed' ? 'selected' : '' ?>>Fixed Amount</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <?php if ($search || $statusFilter || $typeFilter): ?>
                            <a href="?" class="btn btn-secondary" style="margin-left: 10px;">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Discount List -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Discount Codes</h2>
                    <div class="section-subtitle">Total: <?= count($discounts) ?> discount(s)</div>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Usage</th>
                                <th>Validity</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($discounts)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 2rem;">
                                        <i class="fas fa-tag" style="font-size: 2rem; color: #ddd; margin-bottom: 1rem;"></i>
                                        <div>No discount codes found</div>
                                        <?php if ($search || $statusFilter || $typeFilter): ?>
                                            <a href="?" class="btn btn-sm btn-primary" style="margin-top: 1rem;">
                                                Clear Filters
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($discounts as $d): ?>
                                    <?php
                                    // Check status
                                    $isExpiring = strtotime($d['valid_until']) <= strtotime('+7 days') && strtotime($d['valid_until']) > time();
                                    $isExpired = strtotime($d['valid_until']) < time();
                                    $usagePercent = $d['usage_limit'] > 0 ? ($d['times_used'] / $d['usage_limit'] * 100) : 0;
                                    $isFullyUsed = $d['times_used'] >= $d['usage_limit'] && $d['usage_limit'] > 0;
                                    
                                    // Determine status label
                                    $statusClass = 'status-' . $d['status'];
                                    $statusText = ucfirst($d['status']);
                                    
                                    if ($isExpired && $d['status'] === 'active') {
                                        $statusClass = 'status-inactive';
                                        $statusText = 'Expired';
                                    } elseif ($isFullyUsed && $d['status'] === 'active') {
                                        $statusClass = 'status-inactive';
                                        $statusText = 'Used Up';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong style="font-family: 'Courier New', monospace;"><?= htmlspecialchars($d['code']) ?></strong>
                                            <?php if ($isExpired && $d['status'] === 'active'): ?>
                                                <div class="expiry-warning"><i class="fas fa-exclamation-triangle"></i> Expired</div>
                                            <?php elseif ($isExpiring && !$isExpired): ?>
                                                <div class="expiry-warning" style="color: #ff9800;"><i class="fas fa-clock"></i> Expires soon</div>
                                            <?php elseif ($isFullyUsed): ?>
                                                <div class="expiry-warning" style="color: #9c27b0;"><i class="fas fa-trophy"></i> Fully Used</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="discount-type-badge <?= $d['discount_type'] ?>">
                                                <?= $d['discount_type'] === 'percentage' ? '%' : 'RM' ?>
                                            </span>
                                        </td>
                                        <td class="discount-value">
                                            <?= $d['discount_type'] === 'percentage' ? 
                                                htmlspecialchars($d['discount_value']) . '%' : 
                                                'RM' . number_format($d['discount_value'], 2) ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span><?= $d['times_used'] ?> / <?= $d['usage_limit'] ?></span>
                                                <div class="usage-progress">
                                                    <div class="usage-progress-bar" 
                                                         style="width: <?= min($usagePercent, 100) ?>%; 
                                                                background: <?= $usagePercent >= 100 ? '#e74c3c' : '#3498db' ?>;">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div title="Valid from <?= date('Y-m-d H:i', strtotime($d['valid_from'])) ?>">
                                                <?= date('Y-m-d', strtotime($d['valid_from'])) ?>
                                            </div>
                                            <div style="font-size: 0.9em; color: #7f8c8d;">
                                                to <?= date('Y-m-d', strtotime($d['valid_until'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $statusClass ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= date('Y-m-d', strtotime($d['created_at'])) ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons" style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                <a href="?action=view&id=<?= $d['id'] ?>" class="btn btn-info btn-sm" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?action=edit&id=<?= $d['id'] ?>" class="btn btn-secondary btn-sm"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline-block; margin: 0; padding: 0;" 
                                                      onsubmit="return confirm('Toggle status?')">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                                    <input type="hidden" name="current_status" value="<?= $d['status'] ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm"
                                                            title="<?= $d['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                                        <i class="fas fa-toggle-<?= $d['status'] === 'active' ? 'on' : 'off' ?>"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline-block; margin: 0; padding: 0;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this discount?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($action === 'view' && $discount): ?>
            <!-- View Discount Details -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Discount Details</h2>
                </div>
                
                <div class="user-detail" style="max-width: 600px;">
                    <div class="user-info">
                        <h3 style="font-family: 'Courier New', monospace;"><?= htmlspecialchars($discount['code']) ?></h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                            <div>
                                <h4>Discount Information</h4>
                                <p><strong>Type:</strong> 
                                    <span class="discount-type-badge <?= $discount['discount_type'] ?>">
                                        <?= $discount['discount_type'] === 'percentage' ? 'Percentage' : 'Fixed Amount' ?>
                                    </span>
                                </p>
                                <p><strong>Value:</strong> 
                                    <span class="discount-value">
                                        <?= $discount['discount_type'] === 'percentage' ? 
                                            htmlspecialchars($discount['discount_value']) . '%' : 
                                            'RM' . number_format($discount['discount_value'], 2) ?>
                                    </span>
                                </p>
                                <p><strong>Usage:</strong> 
                                    <?= $discount['times_used'] ?> / <?= $discount['usage_limit'] ?> times
                                    <?php if ($discount['usage_limit'] > 0): ?>
                                        <span class="badge <?= $discount['times_used'] >= $discount['usage_limit'] ? 'badge-danger' : 'badge-success' ?>">
                                            <?= number_format(($discount['times_used'] / $discount['usage_limit'] * 100), 1) ?>%
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div>
                                <h4>Validity</h4>
                                <p><strong>From:</strong> <?= date('Y-m-d H:i:s', strtotime($discount['valid_from'])) ?></p>
                                <p><strong>Until:</strong> <?= date('Y-m-d H:i:s', strtotime($discount['valid_until'])) ?></p>
                                
                                <?php
                                $now = time();
                                $validUntil = strtotime($discount['valid_until']);
                                $daysLeft = floor(($validUntil - $now) / (60 * 60 * 24));
                                ?>
                                
                                <?php if ($validUntil < $now): ?>
                                    <p><strong>Status:</strong> <span class="badge badge-danger">Expired</span></p>
                                <?php elseif ($daysLeft <= 7): ?>
                                    <p><strong>Days Left:</strong> <span class="badge badge-warning"><?= $daysLeft ?> days</span></p>
                                <?php else: ?>
                                    <p><strong>Days Left:</strong> <span class="badge badge-success"><?= $daysLeft ?> days</span></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem;">
                            <h4>Status & Timeline</h4>
                            <p><strong>Status:</strong> 
                                <span class="status-badge status-<?= $discount['status'] ?>">
                                    <?= ucfirst($discount['status']) ?>
                                </span>
                            </p>
                            <p><strong>Created:</strong> <?= date('Y-m-d H:i:s', strtotime($discount['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons" style="margin-top: 2rem;">
                    <a href="?action=edit&id=<?= $discount['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Edit Discount
                    </a>
                    <form method="POST" style="display: inline-block;" 
                          onsubmit="return confirm('Toggle status?')">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="id" value="<?= $discount['id'] ?>">
                        <input type="hidden" name="current_status" value="<?= $discount['status'] ?>">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-toggle-<?= $discount['status'] === 'active' ? 'on' : 'off' ?>"></i> 
                            <?= $discount['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                    <a href="?" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

        <?php elseif ($action === 'create' || ($action === 'edit' && $discount)): ?>
            <!-- Create/Edit Discount Form -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <?= $action === 'create' ? 'Create New Discount Code' : 'Edit Discount Code' ?>
                    </h2>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $action === 'edit' ? 'update' : $action ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $discount['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label for="code">Discount Code *</label>
                            <input type="text" id="code" name="code" class="form-control" 
                                   value="<?= htmlspecialchars($discount['code'] ?? '') ?>" 
                                   placeholder="e.g., SAVE10, SPRINGSALE" required
                                   oninput="this.value = this.value.toUpperCase()"
                                   maxlength="50">
                            <small style="color: #7f8c8d;">
                                Maximum 50 characters. Code will be automatically converted to uppercase.
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label for="discount_type">Discount Type *</label>
                            <select id="discount_type" name="discount_type" class="form-control" required>
                                <option value="percentage" <?= ($discount['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                <option value="fixed" <?= ($discount['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed Amount (RM)</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="flex:1;">
                            <label for="discount_value">
                                Discount Value * 
                                <span id="value_label"></span>
                            </label>
                            <input type="number" id="discount_value" name="discount_value" class="form-control" 
                                   step="<?= ($discount['discount_type'] ?? 'percentage') === 'percentage' ? '1' : '0.01' ?>" 
                                   min="<?= ($discount['discount_type'] ?? 'percentage') === 'percentage' ? '1' : '0.01' ?>" 
                                   max="<?= ($discount['discount_type'] ?? 'percentage') === 'percentage' ? '100' : '' ?>" 
                                   required
                                   value="<?= htmlspecialchars($discount['discount_value'] ?? '10') ?>">
                            <small id="value_hint" style="color: #7f8c8d;"></small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label for="usage_limit">Usage Limit *</label>
                            <input type="number" id="usage_limit" name="usage_limit" class="form-control" 
                                   min="1" max="999999" required
                                   value="<?= htmlspecialchars($discount['usage_limit'] ?? '100') ?>">
                            <small style="color: #7f8c8d;">Maximum number of times this discount can be used. Set to 0 for unlimited.</small>
                        </div>
                        
                        <div class="form-group" style="flex:1;">
                            <label for="status">Status *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" <?= ($discount['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($discount['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label for="valid_from">Valid From *</label>
                            <input type="datetime-local" id="valid_from" name="valid_from" class="form-control" 
                                   value="<?= $discount ? date('Y-m-d\TH:i', strtotime($discount['valid_from'])) : 
                                                          date('Y-m-d\TH:i') ?>" required>
                        </div>
                        
                        <div class="form-group" style="flex:1;">
                            <label for="valid_until">Valid Until *</label>
                            <input type="datetime-local" id="valid_until" name="valid_until" class="form-control" 
                                   value="<?= $discount ? date('Y-m-d\TH:i', strtotime($discount['valid_until'])) : 
                                                          date('Y-m-d\TH:i', strtotime('+30 days')) ?>" required>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?= $action === 'create' ? 'Create Discount' : 'Update Discount' ?>
                        </button>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Update discount value labels dynamically
function updateDiscountValueLabels() {
    const type = document.getElementById('discount_type').value;
    const valueLabel = document.getElementById('value_label');
    const valueHint = document.getElementById('value_hint');
    const valueInput = document.getElementById('discount_value');
    
    if (type === 'percentage') {
        valueLabel.textContent = '(%)';
        valueHint.textContent = 'Enter percentage from 1% to 100%';
        valueInput.step = '1';
        valueInput.min = '1';
        valueInput.max = '100';
        valueInput.placeholder = 'e.g., 10 for 10%';
    } else {
        valueLabel.textContent = '(RM)';
        valueHint.textContent = 'Enter fixed amount in RM';
        valueInput.step = '0.01';
        valueInput.min = '0.01';
        valueInput.max = '';
        valueInput.placeholder = 'e.g., 20.00 for RM20';
    }
}

// Initialize labels
updateDiscountValueLabels();

// Listen for discount type changes
document.getElementById('discount_type').addEventListener('change', updateDiscountValueLabels);

// Form validation
document.querySelector('form')?.addEventListener('submit', function(e) {
    const discountType = document.getElementById('discount_type').value;
    const discountValue = parseFloat(document.getElementById('discount_value').value);
    const validFrom = document.getElementById('valid_from').value;
    const validUntil = document.getElementById('valid_until').value;
    const code = document.getElementById('code').value;
    
    if (discountType === 'percentage' && (discountValue < 1 || discountValue > 100)) {
        e.preventDefault();
        alert('Percentage discount must be between 1% and 100%');
        return false;
    }
    
    if (discountType === 'fixed' && discountValue < 0.01) {
        e.preventDefault();
        alert('Fixed amount discount must be at least RM0.01');
        return false;
    }
    
    if (code.length > 50) {
        e.preventDefault();
        alert('Discount code cannot exceed 50 characters');
        return false;
    }
    
    if (new Date(validUntil) <= new Date(validFrom)) {
        e.preventDefault();
        alert('Valid until date must be later than valid from date');
        return false;
    }
    
    return true;
});

// Set default dates automatically
window.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const oneMonthLater = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000);
    
    // Format date time
    function formatDateTime(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    
    // If it's the create page, set default dates
    if (window.location.href.includes('action=create')) {
        const validFromInput = document.getElementById('valid_from');
        const validUntilInput = document.getElementById('valid_until');
        
        if (validFromInput && !validFromInput.value) {
            validFromInput.value = formatDateTime(now);
        }
        
        if (validUntilInput && !validUntilInput.value) {
            validUntilInput.value = formatDateTime(oneMonthLater);
        }
    }
});
</script>
</body>
</html>
