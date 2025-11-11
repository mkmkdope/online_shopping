<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chapter One | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Georgia", serif;
        }

        body {
            background-color: #f9f7f2;
            color: #333;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #fff;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
            transition: all 0.3s;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-info {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #ecf0f1;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2c3e50;
            font-size: 2rem;
        }

        .admin-name {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .admin-role {
            color: #bdc3c7;
            font-size: 0.9rem;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-item {
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }

        .menu-item:hover, .menu-item.active {
            background-color: #34495e;
        }

        .menu-item i {
            margin-right: 0.8rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 1.5rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }

        .page-title {
            font-size: 1.8rem;
            color: #2c3e50;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: #e74c3c;
            color: white;
        }

        .btn-secondary {
            background-color: #ecf0f1;
            color: #2c3e50;
        }

        /* Dashboard Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 4px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
        }

        .books-icon {
            background-color: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }

        .sales-icon {
            background-color: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
        }

        .users-icon {
            background-color: rgba(155, 89, 182, 0.2);
            color: #9b59b6;
        }

        .revenue-icon {
            background-color: rgba(241, 196, 15, 0.2);
            color: #f1c40f;
        }

        .stat-info h3 {
            font-size: 1.8rem;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 4px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #ecf0f1;
        }

        .section-title {
            font-size: 1.4rem;
            color: #2c3e50;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .status {
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-active {
            background-color: rgba(46, 204, 113, 0.2);
            color: #27ae60;
        }

        .status-pending {
            background-color: rgba(241, 196, 15, 0.2);
            color: #f39c12;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.3rem 0.6rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .edit-btn {
            background-color: #3498db;
            color: white;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar .logo-text, 
            .sidebar .menu-text,
            .admin-info .admin-name,
            .admin-info .admin-role {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .admin-avatar {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <span class="logo-text">Chapter One</span>
        </div>
        
        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="admin-name">Admin User</div>
            <div class="admin-role">Administrator</div>
        </div>
        
        <div class="sidebar-menu">
            <a href="#" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            <a href="#" class="menu-item">
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
            <a href="#" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Customers</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reports</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
            </a>
        </div>
    </div>

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

        <!-- Add Book Form -->
        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">Add New Book</h2>
            </div>
            <form>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="bookTitle">Book Title</label>
                        <input type="text" id="bookTitle" class="form-control" placeholder="Enter book title">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="bookAuthor">Author</label>
                        <input type="text" id="bookAuthor" class="form-control" placeholder="Enter author name">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="bookPrice">Price</label>
                        <input type="number" id="bookPrice" class="form-control" placeholder="Enter price">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="bookCategory">Category</label>
                        <select id="bookCategory" class="form-control">
                            <option value="">Select category</option>
                            <option value="fiction">Fiction</option>
                            <option value="non-fiction">Non-Fiction</option>
                            <option value="science-fiction">Science Fiction</option>
                            <option value="mystery">Mystery</option>
                            <option value="biography">Biography</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="bookDescription">Description</label>
                    <textarea id="bookDescription" class="form-control" placeholder="Enter book description"></textarea>
                </div>
                <div class="form-group">
                    <label for="bookCover">Cover Image</label>
                    <input type="file" id="bookCover" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Add Book</button>
            </form>
        </div>
    </div>
</body>
</html>