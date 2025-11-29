<?php
include 'admin_sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SB Online | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 class="page-title">Dashboard</h1>
            <div class="header-actions">
                <button class="btn btn-secondary">
                    <i class="fas fa-bell"></i>
                    Notifications
                </button>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Book
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon books-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <h3>1,248</h3>
                    <p>Total Books</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon sales-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3>324</h3>
                    <p>Orders Today</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon users-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>5,682</h3>
                    <p>Registered Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon revenue-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>$12,458</h3>
                    <p>Monthly Revenue</p>
                </div>
            </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">Recent Orders</h2>
                <a href="#">View All</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#ORD-7842</td>
                            <td>John Smith</td>
                            <td>Nov 12, 2023</td>
                            <td>$42.50</td>
                            <td><span class="status status-active">Completed</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">View</button>
                                <button class="action-btn delete-btn">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-7841</td>
                            <td>Sarah Johnson</td>
                            <td>Nov 12, 2023</td>
                            <td>$63.75</td>
                            <td><span class="status status-pending">Processing</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">View</button>
                                <button class="action-btn delete-btn">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-7840</td>
                            <td>Michael Brown</td>
                            <td>Nov 11, 2023</td>
                            <td>$28.99</td>
                            <td><span class="status status-active">Completed</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">View</button>
                                <button class="action-btn delete-btn">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#ORD-7839</td>
                            <td>Emily Davis</td>
                            <td>Nov 11, 2023</td>
                            <td>$95.20</td>
                            <td><span class="status status-active">Completed</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">View</button>
                                <button class="action-btn delete-btn">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>