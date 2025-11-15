<?php
include '../sb_head.php';
// Database configuration
$host = 'localhost';
$dbname = 'sbonline';
$username = 'root';
$password = '';

// Initialize variables
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$message = '';
$error = '';
$product = [
    'id' => '',
    'title' => '',
    'author' => '',
    'price' => '',
    'description' => '',
    'publisher' => '',
    'publication_date' => '',
    'pages' => '',
    'cover_image' => '',
    'stock_quantity' => ''
];

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle CRUD operations
    switch ($action) {
case 'create':
    $product = $_POST;
    if (validateProduct($product)) {
        $sql = "INSERT INTO products (title, author, price, description, publisher, publication_date, pages, cover_image, stock_quantity) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $product['title'],
            $product['author'],
            $product['price'],
            $product['description'],
            $product['publisher'],
            $product['publication_date'],
            $product['pages'] ?: null,
            $product['cover_image'], // This will now be the selected filename
            $product['stock_quantity']
        ]);
        
        $productId = $pdo->lastInsertId();
        
        // Handle multiple image uploads
        $uploadedFiles = handleMultipleFileUploads($productId);
        
        // Save additional images to product_images table (if you create it)
        if (!empty($uploadedFiles)) {
            foreach ($uploadedFiles as $filename) {
                $sql = "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$productId, $filename]);
            }
        }
        
        $message = "Product created successfully!" . 
                  (count($uploadedFiles) ? " Uploaded " . count($uploadedFiles) . " images." : "");
    }
    break;

        case 'update':
            $product = $_POST;
            if (validateProduct($product)) {
                $sql = "UPDATE products SET 
                        title = ?, author = ?, price = ?, description = ?, 
                        publisher = ?, publication_date = ?, pages = ?, 
                        cover_image = ?, stock_quantity = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $product['title'],
                    $product['author'],
                    $product['price'],
                    $product['description'],
                    $product['publisher'],
                    $product['publication_date'],
                    $product['pages'] ?: null,
                    $product['cover_image'],
                    $product['stock_quantity'],
                    $product['id']
                ]);
                $message = "Product updated successfully!";
            }
            break;

        case 'delete':
            $id = $_GET['id'] ?? '';
            if ($id) {
                $sql = "DELETE FROM products WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                $message = "Product deleted successfully!";
            }
            break;

        case 'edit':
            $id = $_GET['id'] ?? '';
            if ($id) {
                $sql = "SELECT * FROM products WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$product) {
                    $error = "Product not found!";
                }
            }
            break;
    }

    // Fetch all products for listing
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Add this after your existing database connection code
function handleMultipleFileUploads($productId) {
    $uploadedFiles = [];
    
    if (!empty($_FILES['product_images']['name'][0])) {
        // Create uploads directory if it doesn't exist
        $uploadDir = 'uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Process each uploaded file
        foreach ($_FILES['product_images']['name'] as $key => $name) {
            if ($_FILES['product_images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileTmpName = $_FILES['product_images']['tmp_name'][$key];
                $fileSize = $_FILES['product_images']['size'][$key];
                $fileType = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                
                // Validate file
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB
                
                if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
                    // Create unique filename: productID_random_filename.jpg
                    $safeName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $name);
                    $fileName = $productId . '_' . uniqid() . '_' . $safeName;
                    $uploadFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($fileTmpName, $uploadFile)) {
                        $uploadedFiles[] = $fileName;
                    }
                }
            }
        }
    }
    return $uploadedFiles;
}

// Validation function
function validateProduct(&$product) {
    global $error;
    
    $errors = [];
    
    if (empty(trim($product['title']))) {
        $errors[] = "Title is required";
    }
    
    if (empty(trim($product['author']))) {
        $errors[] = "Author is required";
    }
    
    if (empty($product['price']) || !is_numeric($product['price']) || $product['price'] < 0) {
        $errors[] = "Valid price is required";
    }
    
    if (empty($product['stock_quantity']) || !is_numeric($product['stock_quantity']) || $product['stock_quantity'] < 0) {
        $errors[] = "Valid stock quantity is required";
    }
    
    if (!empty($errors)) {
        $error = implode(", ", $errors);
        return false;
    }
    
    // Sanitize data
    $product['title'] = htmlspecialchars(trim($product['title']));
    $product['author'] = htmlspecialchars(trim($product['author']));
    $product['description'] = htmlspecialchars(trim($product['description']));
    $product['publisher'] = htmlspecialchars(trim($product['publisher']));
    $product['price'] = floatval($product['price']);
    $product['stock_quantity'] = intval($product['stock_quantity']);
    $product['pages'] = $product['pages'] ? intval($product['pages']) : null;
    
    return true;
}

// Handle file upload (if you want image upload functionality)
function handleFileUpload() {
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['cover_image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        // Check file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        
        if (in_array($fileExtension, $allowedTypes)) {
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadFile)) {
                return $fileName;
            }
        }
    }
    return $_POST['existing_image'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; }
        .actions { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Product Management</h1>

        <!-- Display messages -->
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Product Form -->
        <div class="form-section">
            <h2><?php echo empty($product['id']) ? 'Add New Product' : 'Edit Product'; ?></h2>
            <form method="POST" action="adminProduct.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo empty($product['id']) ? 'create' : 'update'; ?>">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($product['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="author">Author *</label>
                    <input type="text" id="author" name="author" required 
                           value="<?php echo htmlspecialchars($product['author']); ?>">
                </div>

                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required 
                           value="<?php echo htmlspecialchars($product['price']); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="publisher">Publisher</label>
                    <input type="text" id="publisher" name="publisher" 
                           value="<?php echo htmlspecialchars($product['publisher']); ?>">
                </div>

                <div class="form-group">
                    <label for="publication_date">Publication Date</label>
                    <input type="date" id="publication_date" name="publication_date" 
                           value="<?php echo htmlspecialchars($product['publication_date']); ?>">
                </div>

                <div class="form-group">
                    <label for="pages">Pages</label>
                    <input type="number" id="pages" name="pages" min="1" 
                           value="<?php echo htmlspecialchars($product['pages']); ?>">
                </div>

<div class="form-group">
    <label for="cover_image">Cover Image (Select from uploads below)</label>
    <select id="cover_image" name="cover_image">
        <option value="">-- Select Main Cover Image --</option>
        <!-- This will be populated with uploaded images -->
    </select>
</div>

<div class="form-group">
    <label for="product_images">Upload Product Images from Your Computer</label>
    <input type="file" id="product_images" name="product_images[]" 
           multiple accept="image/jpeg, image/png, image/gif, image/webp">
    <small>Click to select cat.jpg, dog.jpg, etc. from your computer (select multiple with Ctrl+Click)</small>
</div>

<!-- Preview area for selected images -->
<div id="image-preview" style="margin-top: 10px; display: none;">
    <h4>Selected Images:</h4>
    <div id="preview-container"></div>
</div>
                <!-- Uncomment for file upload -->
                <!--
                <div class="form-group">
                    <label for="cover_image_file">Cover Image Upload</label>
                    <input type="file" id="cover_image_file" name="cover_image_file">
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($product['cover_image']); ?>">
                </div>
                -->

                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity *</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0" required 
                           value="<?php echo htmlspecialchars($product['stock_quantity']); ?>">
                </div>

                <button type="submit" class="btn-primary">
                    <?php echo empty($product['id']) ? 'Create Product' : 'Update Product'; ?>
                </button>
                
                <?php if (!empty($product['id'])): ?>
                    <a href="adminProduct.php" class="btn-warning">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Products List -->
        <div class="list-section">
            <h2>Products List</h2>
            
            <?php if (empty($products)): ?>
                <p>No products found.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['id']); ?></td>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo htmlspecialchars($p['author']); ?></td>
                                <td>$<?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($p['stock_quantity']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($p['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="adminProduct.php?action=edit&id=<?php echo $p['id']; ?>" 
                                       class="btn-warning">Edit</a>
                                    <a href="adminProduct.php?action=delete&id=<?php echo $p['id']; ?>" 
                                       class="btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>

     
document.getElementById('product_images').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const coverSelect = document.getElementById('cover_image');
    
    previewContainer.innerHTML = '';
    coverSelect.innerHTML = '<option value="">-- Select Main Cover Image --</option>';
    
    if (this.files.length > 0) {
        imagePreview.style.display = 'block';
        
        for (let i = 0; i < this.files.length; i++) {
            const file = this.files[i];
            
            // Add to cover image dropdown
            const option = document.createElement('option');
            option.value = file.name;
            option.textContent = file.name;
            coverSelect.appendChild(option);
            
            // Create preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.style.display = 'inline-block';
                previewDiv.style.margin = '5px';
                previewDiv.style.textAlign = 'center';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                img.style.border = '1px solid #ddd';
                img.style.margin = '5px';
                
                const fileName = document.createElement('div');
                fileName.textContent = file.name;
                fileName.style.fontSize = '12px';
                
                previewDiv.appendChild(img);
                previewDiv.appendChild(fileName);
                previewContainer.appendChild(previewDiv);
            }
            reader.readAsDataURL(file);
        }
    } else {
        imagePreview.style.display = 'none';
    }
});
</script>

</body>
</html>
<?php
include '../sb_foot.php';