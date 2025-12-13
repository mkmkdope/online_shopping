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
$action = $_GET['action'] ?? 'rules';
$id = $_GET['id'] ?? null;

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
    if (isset($_POST['action'])) {
        $postAction = $_POST['action'];
        
        if ($postAction === 'create_rule') {
            $ruleName = trim($_POST['rule_name'] ?? '');
            $pointsPerAmount = floatval($_POST['points_per_amount'] ?? 0);
            $minSpend = floatval($_POST['min_spend'] ?? 0);
            $maxPointsPerOrder = intval($_POST['max_points_per_order'] ?? 0);
            $priority = intval($_POST['priority'] ?? 1);
            $userRoles = isset($_POST['user_roles']) && is_array($_POST['user_roles']) ? 
                         json_encode($_POST['user_roles']) : json_encode(['all']);
            $ruleDescription = trim($_POST['description'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate data
            $errors = [];
            
            if (empty($ruleName)) {
                $errors[] = 'Rule name cannot be empty';
            }
            
            if (strlen($ruleName) > 100) {
                $errors[] = 'Rule name cannot exceed 100 characters';
            }
            
            if ($pointsPerAmount <= 0) {
                $errors[] = 'Points per amount must be greater than 0';
            }
            
            if ($minSpend < 0) {
                $errors[] = 'Minimum spend cannot be negative';
            }
            
            if ($maxPointsPerOrder < 0) {
                $errors[] = 'Maximum points per order cannot be negative';
            }
            
            if ($priority < 1 || $priority > 100) {
                $errors[] = 'Priority must be between 1 and 100';
            }
            
            if (empty($errors)) {
                try {
                    // Create reward rule
                    $sql = "INSERT INTO reward_rules 
                    (rule_name, points_per_amount, min_spend, max_points_per_order, priority, 
                     user_roles, description, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $ruleName,
                        $pointsPerAmount,
                        $minSpend,
                        $maxPointsPerOrder,
                        $priority,
                        $userRoles,
                        $ruleDescription,
                        $isActive
                    ]);
                    $message = 'Reward rule created successfully!';
                    $action = 'rules';
                } catch (Exception $e) {
                    $error = 'Creation failed: ' . $e->getMessage();
                }
            } else {
                $error = implode(', ', $errors);
            }
        } elseif ($postAction === 'update_rule') {
            $ruleId = intval($_POST['id'] ?? 0);
            $ruleName = trim($_POST['rule_name'] ?? '');
            $pointsPerAmount = floatval($_POST['points_per_amount'] ?? 0);
            $minSpend = floatval($_POST['min_spend'] ?? 0);
            $maxPointsPerOrder = intval($_POST['max_points_per_order'] ?? 0);
            $priority = intval($_POST['priority'] ?? 1);
            $userRoles = isset($_POST['user_roles']) && is_array($_POST['user_roles']) ? 
                         json_encode($_POST['user_roles']) : json_encode(['all']);
            $ruleDescription = trim($_POST['description'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate data
            $errors = [];
            
            if (empty($ruleName)) {
                $errors[] = 'Rule name cannot be empty';
            }
            
            if (strlen($ruleName) > 100) {
                $errors[] = 'Rule name cannot exceed 100 characters';
            }
            
            if ($pointsPerAmount <= 0) {
                $errors[] = 'Points per amount must be greater than 0';
            }
            
            if ($minSpend < 0) {
                $errors[] = 'Minimum spend cannot be negative';
            }
            
            if ($maxPointsPerOrder < 0) {
                $errors[] = 'Maximum points per order cannot be negative';
            }
            
            if ($priority < 1 || $priority > 100) {
                $errors[] = 'Priority must be between 1 and 100';
            }
            
            if (empty($errors)) {
                try {
                    // Update reward rule
                    $sql = "UPDATE reward_rules SET 
                            rule_name = ?, 
                            points_per_amount = ?, 
                            min_spend = ?, 
                            max_points_per_order = ?,
                            priority = ?,
                            user_roles = ?,
                            description = ?,
                            is_active = ? 
                            WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $ruleName,
                        $pointsPerAmount,
                        $minSpend,
                        $maxPointsPerOrder,
                        $priority,
                        $userRoles,
                        $ruleDescription,
                        $isActive,
                        $ruleId
                    ]);
                    $message = 'Reward rule updated successfully!';
                    $action = 'rules';
                } catch (Exception $e) {
                    $error = 'Update failed: ' . $e->getMessage();
                }
            } else {
                $error = implode(', ', $errors);
            }
        } elseif ($postAction === 'delete_rule') {
            $ruleId = intval($_POST['id'] ?? 0);
            if ($ruleId) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM reward_rules WHERE id = ?");
                    $stmt->execute([$ruleId]);
                    $message = 'Reward rule deleted successfully!';
                    $action = 'rules';
                } catch (Exception $e) {
                    $error = 'Deletion failed: ' . $e->getMessage();
                }
            } else {
                $error = 'Invalid rule ID';
            }
        } elseif ($postAction === 'toggle_rule_status') {
            $ruleId = intval($_POST['id'] ?? 0);
            $currentStatus = intval($_POST['current_status'] ?? 0);
            $newStatus = $currentStatus ? 0 : 1;
            
            if ($ruleId) {
                try {
                    $stmt = $pdo->prepare("UPDATE reward_rules SET is_active = ? WHERE id = ?");
                    $stmt->execute([$newStatus, $ruleId]);
                    $message = 'Reward rule status updated successfully!';
                    $action = 'rules';
                } catch (Exception $e) {
                    $error = 'Status update failed: ' . $e->getMessage();
                }
            } else {
                $error = 'Invalid rule ID';
            }
        } elseif ($postAction === 'adjust_points') {
            $userId = intval($_POST['user_id'] ?? 0);
            $points = intval($_POST['points'] ?? 0);
            $adjustmentType = $_POST['adjustment_type'] ?? 'add';
            $description = trim($_POST['description'] ?? '');
            
            // Validate data
            $errors = [];
            
            if ($userId <= 0) {
                $errors[] = 'Please select a valid user';
            }
            
            if ($points <= 0) {
                $errors[] = 'Points must be greater than 0';
            }
            
            if (empty($description)) {
                $errors[] = 'Description cannot be empty';
            }
            
            if (empty($errors)) {
                try {
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    // Get current points
                    $stmt = $pdo->prepare("SELECT total_points FROM reward_points WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $currentPoints = $stmt->fetch();
                    
                    // Calculate new points
                    if ($adjustmentType === 'add') {
                        $newPoints = ($currentPoints['total_points'] ?? 0) + $points;
                        $transactionType = 'adjust_add';
                    } else {
                        $newPoints = ($currentPoints['total_points'] ?? 0) - $points;
                        $transactionType = 'adjust_subtract';
                        
                        // Check if user has enough points
                        if ($newPoints < 0) {
                            throw new Exception('User does not have enough points');
                        }
                    }
                    
                    // Update or insert reward points
                    if ($currentPoints) {
                        $stmt = $pdo->prepare("UPDATE reward_points SET total_points = ?, updated_at = NOW() WHERE user_id = ?");
                        $stmt->execute([$newPoints, $userId]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO reward_points (user_id, total_points) VALUES (?, ?)");
                        $stmt->execute([$userId, $newPoints]);
                    }
                    
                    // Create transaction record
                    $stmt = $pdo->prepare("INSERT INTO reward_point_transactions 
                                            (user_id, points, transaction_type, description, created_at) 
                                            VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        $userId,
                        $points,
                        $transactionType,
                        'Manual adjustment: ' . $description
                    ]);
                    
                    $pdo->commit();
                    $message = 'Points adjusted successfully!';
                    
                    // 重新加载页面显示更新后的数据
                    header("Location: ?action=points&message=" . urlencode($message));
                    exit;
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Adjustment failed: ' . $e->getMessage();
                    // 保持当前页面并显示错误
                    $action = 'points';
                }
            } else {
                $error = implode(', ', $errors);
                $action = 'points';
            }
        }
    }
}

// Get search filters
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$sort = $_GET['sort'] ?? '';

// Get data based on action
if ($action === 'rules' || $action === 'create_rule' || $action === 'edit_rule' || $action === 'view_rule') {
    // Get active status filter
    $activeFilter = $_GET['active'] ?? '';
    
    // Build query for rules
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "rule_name LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($activeFilter !== '' && in_array($activeFilter, ['0', '1'])) {
        $whereConditions[] = "is_active = ?";
        $params[] = $activeFilter;
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Get reward rules list
    $rules = [];
    try {
        $sql = "SELECT id, rule_name, points_per_amount, min_spend, max_points_per_order, 
                       priority, user_roles, description, is_active, created_at 
                FROM reward_rules 
                $whereClause 
                ORDER BY priority ASC, created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rules = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = 'Failed to get reward rules: ' . $e->getMessage();
    }
} elseif ($action === 'points') {
    // Get user points with filters
    $userFilter = $_GET['user'] ?? '';
    
    // Build query for user points
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(u.username LIKE ? OR u.email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($userFilter)) {
        $whereConditions[] = "u.id = ?";
        $params[] = $userFilter;
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Determine sort order
    $orderBy = "rp.total_points DESC"; // Default: highest points first
    if ($sort === 'low') {
        $orderBy = "rp.total_points ASC";
    } elseif ($sort === 'high') {
        $orderBy = "rp.total_points DESC";
    }
    
    // Get user points list
    $userPoints = [];
    try {
        $sql = "SELECT 
                    rp.user_id,
                    rp.total_points,
                    rp.updated_at,
                    u.username,
                    u.email,
                    COALESCE(u.username, 'Unknown') as username,
                    COALESCE(u.email, 'N/A') as email,
                    (SELECT COUNT(*) FROM reward_point_transactions WHERE user_id = rp.user_id) as transaction_count
                FROM reward_points rp
                LEFT JOIN users u ON rp.user_id = u.id
                $whereClause 
                ORDER BY $orderBy";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $userPoints = $stmt->fetchAll();
        
        // 如果没有找到数据，显示空数组
        if (empty($userPoints)) {
            $userPoints = [];
        }
    } catch (Exception $e) {
        $error = 'Failed to get user points: ' . $e->getMessage();
    }
    
    // Get all users for dropdown
    $allUsers = [];
    try {
        $stmt = $pdo->query("SELECT id, username, email FROM users WHERE user_role IN ('user', 'member') ORDER BY username");
        $allUsers = $stmt->fetchAll();
    } catch (Exception $e) {
        $error .= ' Failed to get users: ' . $e->getMessage();
    }
} elseif ($action === 'transactions') {
    // Get transactions with filters
    $userIdFilter = $_GET['user_id'] ?? '';
    $typeFilter = $_GET['type'] ?? '';
    
    // Build query for transactions
    $whereConditions = [];
    $params = [];
    
    if (!empty($userIdFilter)) {
        $whereConditions[] = "rpt.user_id = ?";
        $params[] = $userIdFilter;
    }
    
    if (!empty($typeFilter) && in_array($typeFilter, ['earn', 'redeem', 'adjust'])) {
        $whereConditions[] = "rpt.transaction_type = ?";
        $params[] = $typeFilter;
    }
    
    if (!empty($search)) {
        $whereConditions[] = "(rpt.description LIKE ? OR u.username LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Get transactions list
    $transactions = [];
    try {
        $sql = "SELECT rpt.*, u.username, u.email, rp.total_points
                FROM reward_point_transactions rpt
                JOIN users u ON rpt.user_id = u.id
                LEFT JOIN reward_points rp ON rpt.user_id = rp.user_id
                $whereClause 
                ORDER BY rpt.created_at DESC
                LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = 'Failed to get transactions: ' . $e->getMessage();
    }
} elseif ($action === 'redemptions') {
    // Get redemptions with filters
    $userIdFilter = $_GET['user_id'] ?? '';
    $statusFilter = $_GET['status'] ?? '';
    
    // Build query for redemptions
    $whereConditions = [];
    $params = [];
    
    if (!empty($userIdFilter)) {
        $whereConditions[] = "rr.user_id = ?";
        $params[] = $userIdFilter;
    }
    
    if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'completed', 'cancelled'])) {
        $whereConditions[] = "rr.status = ?";
        $params[] = $statusFilter;
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Get redemptions list
    $redemptions = [];
    try {
        $sql = "SELECT rr.*, u.username, u.email, rp.total_points
                FROM reward_redemptions rr
                JOIN users u ON rr.user_id = u.id
                LEFT JOIN reward_points rp ON rr.user_id = rp.user_id
                $whereClause 
                ORDER BY rr.created_at DESC
                LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $redemptions = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = 'Failed to get redemptions: ' . $e->getMessage();
    }
}

// Get single rule information (for edit/view)
$rule = null;
if (($action === 'edit_rule' || $action === 'view_rule') && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM reward_rules WHERE id = ?");
        $stmt->execute([$id]);
        $rule = $stmt->fetch();
        if (!$rule) {
            $error = 'Reward rule does not exist';
            $action = 'rules';
        }
    } catch (Exception $e) {
        $error = 'Failed to get reward rule information: ' . $e->getMessage();
        $action = 'rules';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SB | Reward Points Management</title>
<link rel="stylesheet" href="/css/sb_style.css">
<link rel="stylesheet" href="/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Custom Styles */
.points-value {
    font-weight: bold;
    color: #e74c3c;
}

.points-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    background: #e1f5fe;
    color: #0288d1;
}

.transaction-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.transaction-earn {
    background: #d4edda;
    color: #155724;
}

.transaction-redeem {
    background: #f8d7da;
    color: #721c24;
}

.transaction-adjust {
    background: #fff3cd;
    color: #856404;
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

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
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

.filter-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    align-items: end;
}

.filter-group {
    margin-bottom: 0;
}

.tabs {
    display: flex;
    background: white;
    border-radius: 8px 8px 0 0;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 0;
}

.tab {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border: none;
    border-right: 1px solid #eee;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    text-decoration: none;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab:hover {
    background: #e9ecef;
}

.tab.active {
    background: white;
    border-bottom: 3px solid #3498db;
    font-weight: 600;
}

.tab-content {
    display: none;
    background: white;
    padding: 1.5rem;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.tab-content.active {
    display: block;
}

.quick-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
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
    text-decoration: none;
    color: #333;
}

.quick-action-btn:hover {
    background: #f8f9fa;
    border-color: #3498db;
}

.quick-action-btn.active {
    background: #3498db;
    color: white;
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

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 10px;
}

.user-info-cell {
    display: flex;
    align-items: center;
}

/* 模态框样式 */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 90%;
    max-width: 500px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.modal-header {
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #333;
}

.modal-header .close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s;
}

.modal-header .close:hover {
    color: #333;
}

.modal-body {
    padding: 20px;
}

/* 按钮悬停效果 */
.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

@media (max-width: 992px) {
    .stat-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filter-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .tabs {
        flex-wrap: wrap;
    }
    
    .tab {
        flex: 1;
        min-width: 120px;
        text-align: center;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .stat-row {
        grid-template-columns: 1fr;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        flex-direction: column;
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
            <a href="discount_panel.php" class="menu-item">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Discounts</span>
            </a>
            <a href="reward_panel.php" class="menu-item active">
                <i class="fas fa-star"></i>
                <span class="menu-text">Reward Points</span>
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
            <h1>Reward Points Management</h1>
            <p class="subtitle">Manage reward points system, rules, and user points</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message message-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message message-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <a href="?action=rules" class="tab <?= $action === 'rules' || $action === 'create_rule' || $action === 'edit_rule' || $action === 'view_rule' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Rules
            </a>
            <a href="?action=points" class="tab <?= $action === 'points' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> User Points
            </a>
            <a href="?action=transactions" class="tab <?= $action === 'transactions' ? 'active' : '' ?>">
                <i class="fas fa-exchange-alt"></i> Transactions
            </a>
            <a href="?action=redemptions" class="tab <?= $action === 'redemptions' ? 'active' : '' ?>">
                <i class="fas fa-gift"></i> Redemptions
            </a>
        </div>

        <?php if ($action === 'rules'): ?>
            <!-- Rules Management -->
            <div class="tab-content active">
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="?action=create_rule" class="quick-action-btn" style="background: #28a745; color: white; border-color: #28a745;">
                        <i class="fas fa-plus"></i> Add New Rule
                    </a>
                    <a href="?action=rules&active=1" class="quick-action-btn <?= $activeFilter === '1' ? 'active' : '' ?>">
                        <i class="fas fa-check-circle"></i> Active Rules
                    </a>
                    <a href="?action=rules&active=0" class="quick-action-btn <?= $activeFilter === '0' ? 'active' : '' ?>">
                        <i class="fas fa-times-circle"></i> Inactive Rules
                    </a>
                </div>

                <!-- Statistics -->
                <?php
                // Get statistics for rules
                try {
                    $statsSql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                    AVG(points_per_amount) as avg_points,
                    AVG(priority) as avg_priority,
                    COUNT(DISTINCT JSON_EXTRACT(user_roles, '$')) as unique_role_sets
                FROM reward_rules";
                    $statsStmt = $pdo->query($statsSql);
                    $stats = $statsStmt->fetch();
                } catch (Exception $e) {
                    $stats = ['total' => 0, 'active' => 0, 'inactive' => 0];
                }
                ?>

                <div class="stat-row">
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                        <div class="stat-label">Total Rules</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['active'] ?? 0 ?></div>
                        <div class="stat-label">Active Rules</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($stats['avg_points'] ?? 0, 2) ?></div>
                        <div class="stat-label">Avg Points/RM</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($stats['avg_priority'] ?? 0, 1) ?></div>
                        <div class="stat-label">Avg Priority</div>
                    </div>
                </div>

                <!-- Filter and Search -->
                <div class="filter-section">
                    <form method="GET" class="filter-row">
                        <input type="hidden" name="action" value="rules">
                        
                        <div class="filter-group">
                            <label>Search Rule</label>
                            <input type="text" name="search" placeholder="Search rule name..." 
                                   value="<?= htmlspecialchars($search) ?>" class="form-control">
                        </div>
                        
                        <div class="filter-group">
                            <label>Status</label>
                            <select name="active" class="form-control">
                                <option value="">All Status</option>
                                <option value="1" <?= $activeFilter === '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $activeFilter === '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <?php if ($search || $activeFilter !== ''): ?>
                                <a href="?action=rules" class="btn btn-secondary" style="margin-left: 10px;">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Rules List -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Reward Rules</h2>
                        <div class="section-subtitle">Total: <?= count($rules) ?> rule(s)</div>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Priority</th>
                                    <th>Rule Name</th>
                                    <th>Points/RM</th>
                                    <th>Min Spend</th>
                                    <th>Max Points</th>
                                    <th>User Roles</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rules)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-cog" style="font-size: 2rem; color: #ddd; margin-bottom: 1rem;"></i>
                                            <div>No reward rules found</div>
                                            <?php if ($search || $activeFilter !== ''): ?>
                                                <a href="?action=rules" class="btn btn-sm btn-primary" style="margin-top: 1rem;">
                                                    Clear Filters
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rules as $r): 
                                        $userRoles = json_decode($r['user_roles'] ?? '[]', true);
                                        if (!is_array($userRoles)) $userRoles = [];
                                    ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="badge <?= $r['priority'] == 1 ? 'badge-success' : 'badge-info' ?>">
                                                    <?= $r['priority'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($r['rule_name']) ?></strong>
                                                <?php if (!empty($r['description'])): ?>
                                                    <br><small style="color: #7f8c8d;"><?= htmlspecialchars($r['description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="points-value">
                                                <?= number_format($r['points_per_amount'], 2) ?> points
                                            </td>
                                            <td>
                                                <?= $r['min_spend'] > 0 ? 'RM' . number_format($r['min_spend'], 2) : 'No minimum' ?>
                                            </td>
                                            <td>
                                                <?= $r['max_points_per_order'] > 0 ? number_format($r['max_points_per_order']) . ' points' : 'Unlimited' ?>
                                            </td>
                                            <td>
                                                <?php
                                                $roleLabels = [];
                                                foreach ($userRoles as $role) {
                                                    $roleLabels[] = $role === 'all' ? 'All' : ucfirst($role);
                                                }
                                                echo implode(', ', $roleLabels) ?: 'All';
                                                ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?= $r['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                                    <?= $r['is_active'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons" style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                    <a href="?action=edit_rule&id=<?= $r['id'] ?>" class="btn btn-secondary btn-sm"
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" style="display: inline-block; margin: 0; padding: 0;" 
                                                          onsubmit="return confirm('Toggle rule status?')">
                                                        <input type="hidden" name="action" value="toggle_rule_status">
                                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                        <input type="hidden" name="current_status" value="<?= $r['is_active'] ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm"
                                                                title="<?= $r['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                            <i class="fas fa-toggle-<?= $r['is_active'] ? 'on' : 'off' ?>"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline-block; margin: 0; padding: 0;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this rule?')">
                                                        <input type="hidden" name="action" value="delete_rule">
                                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
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

        <?php elseif ($action === 'points'): ?>
            <!-- User Points Management -->
            <div class="tab-content active">
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <button type="button" onclick="showAdjustModal()" class="quick-action-btn" style="background: #28a745; color: white; border-color: #28a745;">
                        <i class="fas fa-plus"></i> Adjust Points
                    </button>
                    <a href="?action=points&sort=high<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($userFilter) ? '&user=' . $userFilter : '' ?>" 
                       class="quick-action-btn <?= $sort === 'high' ? 'active' : '' ?>">
                        <i class="fas fa-sort-amount-down"></i> Highest Points
                    </a>
                    <a href="?action=points&sort=low<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($userFilter) ? '&user=' . $userFilter : '' ?>" 
                       class="quick-action-btn <?= $sort === 'low' ? 'active' : '' ?>">
                        <i class="fas fa-sort-amount-up"></i> Lowest Points
                    </a>
                    <?php if ($sort !== ''): ?>
                        <a href="?action=points<?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($userFilter) ? '&user=' . $userFilter : '' ?>" 
                           class="quick-action-btn">
                            <i class="fas fa-times"></i> Clear Sort
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Statistics -->
                <?php
                // Get statistics for points
                try {
                    $statsSql = "SELECT 
                        COUNT(DISTINCT rp.user_id) as total_users,
                        COUNT(DISTINCT u.id) as total_customers,
                        SUM(rp.total_points) as total_points,
                        AVG(rp.total_points) as avg_points,
                        MAX(rp.total_points) as max_points,
                        MIN(rp.total_points) as min_points
                    FROM reward_points rp
                    RIGHT JOIN users u ON rp.user_id = u.id AND u.user_role IN ('user', 'member')";
                    $statsStmt = $pdo->query($statsSql);
                    $stats = $statsStmt->fetch();
                } catch (Exception $e) {
                    $stats = ['total_users' => 0, 'total_points' => 0];
                }
                ?>
                
                <div class="stat-row">
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['total_customers'] ?? 0 ?></div>
                        <div class="stat-label">Total Customers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['total_users'] ?? 0 ?></div>
                        <div class="stat-label">Users with Points</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($stats['total_points'] ?? 0) ?></div>
                        <div class="stat-label">Total Points</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($stats['avg_points'] ?? 0, 0) ?></div>
                        <div class="stat-label">Avg Points/User</div>
                    </div>
                </div>

                <!-- Filter and Search -->
                <div class="filter-section">
                    <form method="GET" class="filter-row">
                        <input type="hidden" name="action" value="points">
                        <?php if ($sort !== ''): ?>
                            <input type="hidden" name="sort" value="<?= $sort ?>">
                        <?php endif; ?>
                        
                        <div class="filter-group">
                            <label>Search User</label>
                            <input type="text" name="search" placeholder="Search username or email..." 
                                   value="<?= htmlspecialchars($search) ?>" class="form-control">
                        </div>
                        
                        <div class="filter-group">
                            <label>Select User</label>
                            <select name="user" class="form-control">
                                <option value="">All Users</option>
                                <?php foreach ($allUsers as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $userFilter == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <?php if ($search || $userFilter || $sort !== ''): ?>
                                <a href="?action=points" class="btn btn-secondary" style="margin-left: 10px;">Clear All</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- User Points List -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">User Points Balance</h2>
                        <div class="section-subtitle">
                            Total: <?= count($userPoints) ?> user(s)
                            <?php if ($sort === 'high'): ?>
                                <span class="badge badge-info">Sorted: Highest to Lowest</span>
                            <?php elseif ($sort === 'low'): ?>
                                <span class="badge badge-info">Sorted: Lowest to Highest</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Points Balance</th>
                                    <th>Transactions</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($userPoints)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-users" style="font-size: 2rem; color: #ddd; margin-bottom: 1rem;"></i>
                                            <div>No users with points found</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($userPoints as $up): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info-cell">
                                                    <div class="user-avatar">
                                                        <?= isset($up['username']) && !empty($up['username']) ? strtoupper(substr($up['username'], 0, 1)) : '?' ?>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($up['username'] ?? 'Unknown User') ?></strong><br>
                                                        <small style="color: #7f8c8d;"><?= htmlspecialchars($up['email'] ?? 'N/A') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="points-value">
                                                <span class="points-badge"><?= number_format($up['total_points'] ?? 0) ?> points</span>
                                            </td>
                                            <td>
                                                <?= $up['transaction_count'] ?? 0 ?> transactions
                                            </td>
                                            <td>
                                                <?php
                                                $updatedAt = $up['updated_at'] ?? null;
                                                if ($updatedAt) {
                                                    echo date('Y-m-d H:i', strtotime($updatedAt));
                                                } else {
                                                    echo 'Never';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons" style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                    <a href="?action=transactions&user_id=<?= $up['user_id'] ?? 0 ?>" class="btn btn-info btn-sm"
                                                       title="View Transactions">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                    <button type="button" onclick="showAdjustModalForUser(<?= $up['user_id'] ?? 0 ?>, '<?= htmlspecialchars(addslashes($up['username'] ?? 'User')) ?>')" 
                                                            class="btn btn-secondary btn-sm" title="Adjust Points">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?action=redemptions&user_id=<?= $up['user_id'] ?? 0 ?>" class="btn btn-warning btn-sm"
                                                       title="View Redemptions">
                                                        <i class="fas fa-gift"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'transactions'): ?>
            <!-- Transactions Management -->
            <div class="tab-content active">
                <!-- Statistics -->
                <?php
                // Get statistics for transactions
                try {
                    $statsSql = "SELECT 
                        COUNT(*) as total_transactions,
                        SUM(CASE WHEN transaction_type = 'earn' THEN points ELSE 0 END) as total_earned,
                        SUM(CASE WHEN transaction_type = 'redeem' THEN points ELSE 0 END) as total_redeemed,
                        SUM(CASE WHEN transaction_type = 'adjust' THEN points ELSE 0 END) as total_adjusted,
                        COUNT(DISTINCT user_id) as unique_users
                    FROM reward_point_transactions
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    $statsStmt = $pdo->query($statsSql);
                    $stats = $statsStmt->fetch();
                } catch (Exception $e) {
                    $stats = ['total_transactions' => 0, 'total_earned' => 0, 'total_redeemed' => 0];
                }
                ?>
                
                <div class="stat-row">
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['total_transactions'] ?? 0 ?></div>
                        <div class="stat-label">Last 30 Days</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">+<?= number_format($stats['total_earned'] ?? 0) ?></div>
                        <div class="stat-label">Points Earned</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">-<?= number_format($stats['total_redeemed'] ?? 0) ?></div>
                        <div class="stat-label">Points Redeemed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['unique_users'] ?? 0 ?></div>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>

                <!-- Filter and Search -->
                <div class="filter-section">
                    <form method="GET" class="filter-row">
                        <input type="hidden" name="action" value="transactions">
                        
                        <div class="filter-group">
                            <label>Search</label>
                            <input type="text" name="search" placeholder="Search description or username..." 
                                   value="<?= htmlspecialchars($search) ?>" class="form-control">
                        </div>
                        
                        <div class="filter-group">
                            <label>Transaction Type</label>
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="earn" <?= $typeFilter === 'earn' ? 'selected' : '' ?>>Earn</option>
                                <option value="redeem" <?= $typeFilter === 'redeem' ? 'selected' : '' ?>>Redeem</option>
                                <option value="adjust" <?= $typeFilter === 'adjust' ? 'selected' : '' ?>>Adjust</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>User ID</label>
                            <input type="number" name="user_id" placeholder="User ID" 
                                   value="<?= htmlspecialchars($userIdFilter) ?>" class="form-control">
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <?php if ($search || $typeFilter || $userIdFilter): ?>
                                <a href="?action=transactions" class="btn btn-secondary" style="margin-left: 10px;">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Transactions List -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Point Transactions</h2>
                        <div class="section-subtitle">Showing last 100 transactions</div>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Points</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Balance After</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-exchange-alt" style="font-size: 2rem; color: #ddd; margin-bottom: 1rem;"></i>
                                            <div>No transactions found</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $t): ?>
                                        <?php
                                        // Calculate balance after transaction
                                        $balanceAfter = $t['total_points'] ?? 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="user-info-cell">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($t['username'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($t['username']) ?></strong><br>
                                                        <small style="color: #7f8c8d;">ID: <?= $t['user_id'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="transaction-badge transaction-<?= $t['transaction_type'] ?>">
                                                    <?= ucfirst($t['transaction_type']) ?>
                                                </span>
                                            </td>
                                            <td class="points-value" style="color: <?= $t['transaction_type'] === 'earn' ? '#28a745' : ($t['transaction_type'] === 'redeem' ? '#e74c3c' : '#ff9800') ?>;">
                                                <?= $t['transaction_type'] === 'earn' ? '+' : '-' ?><?= number_format($t['points']) ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($t['description']) ?>
                                            </td>
                                            <td>
                                                <?= date('Y-m-d H:i', strtotime($t['created_at'])) ?>
                                            </td>
                                            <td>
                                                <?= number_format($balanceAfter) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'redemptions'): ?>
            <!-- Redemptions Management -->
            <div class="tab-content active">
                <!-- Statistics -->
                <?php
                // Get statistics for redemptions
                try {
                    $statsSql = "SELECT 
                        COUNT(*) as total_redemptions,
                        SUM(points_used) as total_points_used,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        COUNT(DISTINCT user_id) as unique_users
                    FROM reward_redemptions
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    $statsStmt = $pdo->query($statsSql);
                    $stats = $statsStmt->fetch();
                } catch (Exception $e) {
                    $stats = ['total_redemptions' => 0, 'total_points_used' => 0];
                }
                ?>
                
                <div class="stat-row">
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['total_redemptions'] ?? 0 ?></div>
                        <div class="stat-label">Last 30 Days</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($stats['total_points_used'] ?? 0) ?></div>
                        <div class="stat-label">Points Used</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['completed'] ?? 0 ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['unique_users'] ?? 0 ?></div>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>

                <!-- Filter and Search -->
                <div class="filter-section">
                    <form method="GET" class="filter-row">
                        <input type="hidden" name="action" value="redemptions">
                        
                        <div class="filter-group">
                            <label>Search</label>
                            <input type="text" name="search" placeholder="Search reward name..." 
                                   value="<?= htmlspecialchars($search) ?>" class="form-control">
                        </div>
                        
                        <div class="filter-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>User ID</label>
                            <input type="number" name="user_id" placeholder="User ID" 
                                   value="<?= htmlspecialchars($userIdFilter) ?>" class="form-control">
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="btn btn-primary" style="margin-bottom: 0;">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <?php if ($search || $statusFilter || $userIdFilter): ?>
                                <a href="?action=redemptions" class="btn btn-secondary" style="margin-left: 10px;">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Redemptions List -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Reward Redemptions</h2>
                        <div class="section-subtitle">Showing last 100 redemptions</div>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Reward</th>
                                    <th>Points Used</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($redemptions)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 2rem;">
                                            <i class="fas fa-gift" style="font-size: 2rem; color: #ddd; margin-bottom: 1rem;"></i>
                                            <div>No redemptions found</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($redemptions as $r): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info-cell">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($r['username'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($r['username']) ?></strong><br>
                                                        <small style="color: #7f8c8d;"><?= htmlspecialchars($r['email']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($r['reward_name']) ?></strong>
                                            </td>
                                            <td class="points-value">
                                                <?= number_format($r['points_used']) ?> points
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $r['status'] ?>">
                                                    <?= ucfirst($r['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= date('Y-m-d H:i', strtotime($r['created_at'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'create_rule' || $action === 'edit_rule'): ?>
            <!-- Create/Edit Rule Form -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <?= $action === 'create_rule' ? 'Create New Reward Rule' : 'Edit Reward Rule' ?>
                    </h2>
                    <a href="?action=rules" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Rules
                    </a>
                </div>
                
                <form method="POST" style="max-width: 800px; margin: 0 auto;">
                    <input type="hidden" name="action" value="<?= $action === 'edit_rule' ? 'update_rule' : 'create_rule' ?>">
                    <?php if ($action === 'edit_rule'): ?>
                        <input type="hidden" name="id" value="<?= $rule['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="rule_name">Rule Name *</label>
                        <input type="text" id="rule_name" name="rule_name" class="form-control" 
                               value="<?= htmlspecialchars($rule['rule_name'] ?? '') ?>" 
                               placeholder="e.g., Member Bonus, First Purchase Bonus, VIP Reward" required
                               maxlength="100">
                        <small style="color: #7f8c8d;">
                            A descriptive name for this reward rule.
                        </small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label for="points_per_amount">Points per RM *</label>
                            <input type="number" id="points_per_amount" name="points_per_amount" class="form-control" 
                                   step="0.01" min="0.01" max="100" required
                                   value="<?= htmlspecialchars($rule['points_per_amount'] ?? '1') ?>">
                            <small style="color: #7f8c8d;">
                                Points earned for every RM1 spent. Example: 1.5 means 1.5 points per RM1.
                            </small>
                        </div>
                        
                        <div class="form-group" style="flex:1;">
                            <label for="min_spend">Minimum Spend (RM) *</label>
                            <input type="number" id="min_spend" name="min_spend" class="form-control" 
                                   step="0.01" min="0" required
                                   value="<?= htmlspecialchars($rule['min_spend'] ?? '0') ?>">
                            <small style="color: #7f8c8d;">
                                Minimum purchase amount required. Set 0 for no minimum.
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="flex:1;">
                            <label for="max_points_per_order">Max Points per Order</label>
                            <input type="number" id="max_points_per_order" name="max_points_per_order" class="form-control" 
                                   step="1" min="0"
                                   value="<?= htmlspecialchars($rule['max_points_per_order'] ?? '0') ?>">
                            <small style="color: #7f8c8d;">
                                Maximum points awarded per order. Set 0 for unlimited.
                            </small>
                        </div>
                        
                        <div class="form-group" style="flex:1;">
                            <label for="priority">Priority *</label>
                            <input type="number" id="priority" name="priority" class="form-control" 
                                   step="1" min="1" max="100" required
                                   value="<?= htmlspecialchars($rule['priority'] ?? '1') ?>">
                            <small style="color: #7f8c8d;">
                                Lower number = higher priority. Rules are checked in priority order.
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_roles">Applicable User Roles *</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-top: 10px;">
                            <?php
                            // Define available user roles
                            $availableRoles = ['member', 'user', 'all'];
                            $selectedRoles = isset($rule['user_roles']) ? json_decode($rule['user_roles'], true) : ['all'];
                            if (!is_array($selectedRoles)) $selectedRoles = ['all'];
                            
                            foreach ($availableRoles as $role): 
                                $isChecked = in_array($role, $selectedRoles) ? 'checked' : '';
                                $roleLabel = ucfirst($role === 'all' ? 'All Users' : $role);
                            ?>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="user_roles[]" value="<?= $role ?>" <?= $isChecked ?>>
                                <span><?= $roleLabel ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                            Select which user roles this rule applies to. "All Users" will apply to everyone.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="rule_description">Description</label>
                        <textarea id="description" name="description" class="form-control" 
                              rows="3" placeholder="Optional description of this rule..."><?= htmlspecialchars($rule['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" 
                                   <?= ($rule['is_active'] ?? 1) ? 'checked' : '' ?>>
                            Active Rule
                        </label>
                        <small style="display: block; color: #7f8c8d; margin-top: 5px;">
                            Only active rules are applied to transactions.
                        </small>
                    </div>
                    
                    <div class="action-buttons" style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?= $action === 'create_rule' ? 'Create Rule' : 'Update Rule' ?>
                        </button>
                        <a href="?action=rules" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

        <?php endif; ?>
    </div>
</div>

<!-- Adjust Points Modal -->
<div id="adjustModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Adjust User Points</h3>
            <span class="close" onclick="closeAdjustModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="adjustForm" method="POST">
                <input type="hidden" name="action" value="adjust_points">
                
                <div class="form-group">
                    <label for="user_id">Select User *</label>
                    <select id="user_id" name="user_id" class="form-control" required>
                        <option value="">-- Select User --</option>
                        <?php foreach ($allUsers as $user): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label for="adjustment_type">Adjustment Type *</label>
                        <select id="adjustment_type" name="adjustment_type" class="form-control" required>
                            <option value="add">Add Points</option>
                            <option value="subtract">Subtract Points</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="flex:1;">
                        <label for="points">Points *</label>
                        <input type="number" id="points" name="points" class="form-control" 
                               min="1" required value="10">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" class="form-control" 
                              rows="3" placeholder="Reason for adjustment..." required></textarea>
                    <small style="color: #7f8c8d;">
                        Example: "Manual adjustment for customer service", "Bonus points for promotion"
                    </small>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Apply Adjustment
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeAdjustModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 确保DOM完全加载后再执行
document.addEventListener('DOMContentLoaded', function() {
    console.log('Reward Points Management loaded');
    
    // 重新定义全局函数
    window.showAdjustModal = function() {
        console.log('showAdjustModal called');
        const modal = document.getElementById('adjustModal');
        if (modal) {
            modal.style.display = 'block';
            console.log('Adjust points modal opened');
        }
    };
    
    window.showAdjustModalForUser = function(userId, username) {
        console.log('showAdjustModalForUser called with:', userId, username);
        const modal = document.getElementById('adjustModal');
        const userSelect = document.getElementById('user_id');
        
        if (modal && userSelect) {
            userSelect.value = userId;
            modal.style.display = 'block';
            
            // 自动填充描述
            const description = document.getElementById('description');
            if (description) {
                description.value = 'Adjustment for ' + username + ' (ID: ' + userId + ')';
            }
        }
    };
    
    window.closeAdjustModal = function() {
        console.log('closeAdjustModal called');
        const modal = document.getElementById('adjustModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };
    
    // 为关闭按钮添加事件监听器
    const closeBtn = document.querySelector('.close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeAdjustModal);
    }
    
    // 点击模态框外部关闭
    window.onclick = function(event) {
        const modal = document.getElementById('adjustModal');
        if (event.target == modal) {
            closeAdjustModal();
        }
    };
    
    // 按ESC键关闭模态框
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('adjustModal');
            if (modal && modal.style.display === 'block') {
                closeAdjustModal();
            }
        }
    });
    
    // 表单验证
    const adjustForm = document.getElementById('adjustForm');
    if (adjustForm) {
        adjustForm.addEventListener('submit', function(e) {
            const userId = document.getElementById('user_id').value;
            const points = parseInt(document.getElementById('points').value);
            const description = document.getElementById('description').value.trim();
            
            if (!userId) {
                e.preventDefault();
                alert('Please select a user');
                return false;
            }
            
            if (points < 1) {
                e.preventDefault();
                alert('Points must be at least 1');
                return false;
            }
            
            if (description.length < 5) {
                e.preventDefault();
                alert('Please provide a meaningful description');
                return false;
            }
            
            // 显示加载提示
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
            }
            
            return true;
        });
    }
    
    // 如果URL中有message参数，显示提示
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message) {
        setTimeout(() => {
            alert(decodeURIComponent(message));
            // 移除URL中的message参数
            const newUrl = window.location.pathname + '?action=points';
            window.history.replaceState({}, document.title, newUrl);
        }, 500);
    }
});
</script>

</body>
</html>
