<?php 
$page_title = 'Manage Products';
include '../includes/header.php';

requireAdmin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $post_action = $_POST['action'];
        
        if ($post_action == 'add' || $post_action == 'edit') {
            $category_id = intval($_POST['category_id']);
            $name = sanitizeInput($_POST['name']);
            $description = sanitizeInput($_POST['description']);
            $price = floatval($_POST['price']);
            $stock = intval($_POST['stock']);
            $featured = isset($_POST['featured']) ? 1 : 0;
            
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $upload_result = uploadImage($_FILES['image']);
                if ($upload_result['success']) {
                    $image = $upload_result['filename'];
                } else {
                    $error = $upload_result['message'];
                }
            }
            
            if (!$error) {
                global $db;
                
                if ($post_action == 'add') {
                    $stmt = $db->prepare("INSERT INTO products (category_id, name, description, price, stock, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issdiisi", $category_id, $name, $description, $price, $stock, $image, $featured);
                    
                    if ($stmt->execute()) {
                        $success = 'Product added successfully!';
                        $action = 'list';
                    } else {
                        $error = 'Failed to add product.';
                    }
                } else {
                    $edit_id = intval($_POST['id']);
                    
                    if ($image) {
                        $stmt = $db->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image=?, featured=? WHERE id=?");
                        $stmt->bind_param("issdiisi", $category_id, $name, $description, $price, $stock, $image, $featured, $edit_id);
                    } else {
                        $stmt = $db->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, featured=? WHERE id=?");
                        $stmt->bind_param("issdiii", $category_id, $name, $description, $price, $stock, $featured, $edit_id);
                    }
                    
                    if ($stmt->execute()) {
                        $success = 'Product updated successfully!';
                        $action = 'list';
                    } else {
                        $error = 'Failed to update product.';
                    }
                }
            }
        } elseif ($post_action == 'delete') {
            $delete_id = intval($_POST['id']);
            global $db;
            $stmt = $db->prepare("DELETE FROM products WHERE id=?");
            $stmt->bind_param("i", $delete_id);
            
            if ($stmt->execute()) {
                $success = 'Product deleted successfully!';
            } else {
                $error = 'Failed to delete product.';
            }
        }
    }
}

$products = getProducts();
$categories = getCategories();

if ($action == 'edit' && $product_id) {
    $product = getProductById($product_id);
}
?>

<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-box"></i> Manage Products</h1>
        <?php if ($action == 'list'): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        <?php else: ?>
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        <?php endif; ?>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($action == 'list'): ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <img src="<?php echo $product['image'] ? '../uploads/' . $product['image'] : 'https://via.placeholder.com/50'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td>
                                        <?php if ($product['stock'] > 10): ?>
                                            <span class="badge bg-success"><?php echo $product['stock']; ?></span>
                                        <?php elseif ($product['stock'] > 0): ?>
                                            <span class="badge bg-warning"><?php echo $product['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['featured']): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-muted"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="" method="POST" class="d-inline" onsubmit="return confirmDelete()">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?php echo $action == 'add' ? 'Add New Product' : 'Edit Product'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo $action == 'edit' ? htmlspecialchars($product['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($action == 'edit' && $product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo $action == 'edit' ? htmlspecialchars($product['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                                   value="<?php echo $action == 'edit' ? $product['price'] : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0"
                                   value="<?php echo $action == 'edit' ? $product['stock'] : ''; ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <?php if ($action == 'edit' && $product['image']): ?>
                                <small class="text-muted">Current: <?php echo htmlspecialchars($product['image']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="featured" name="featured" 
                               <?php echo ($action == 'edit' && $product['featured']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="featured">
                            <i class="fas fa-star text-warning"></i> Featured Product
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $action == 'add' ? 'Add Product' : 'Update Product'; ?>
                    </button>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
