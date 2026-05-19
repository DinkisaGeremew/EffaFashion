<?php
$page_title = 'Manage Products';
require_once __DIR__ . '/includes/admin_header.php';

$error = $success = '';

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid         = (int)$_POST['product_id'];
    $name        = sanitize($_POST['name']        ?? '');
    $category_id = (int)($_POST['category_id']    ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price       = (float)($_POST['price']        ?? 0);
    $sale_price  = $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : 'NULL';
    $stock       = (int)($_POST['stock']          ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active   = isset($_POST['is_active'])   ? 1 : 0;
    $sizes_arr   = array_filter(array_map('trim', explode(',', $_POST['sizes']  ?? '')));
    $colors_arr  = array_filter(array_map('trim', explode(',', $_POST['colors'] ?? '')));

    $n    = $conn->real_escape_string($name);
    $desc = $conn->real_escape_string($description);
    $sz   = $conn->real_escape_string(json_encode(array_values($sizes_arr)));
    $cl   = $conn->real_escape_string(json_encode(array_values($colors_arr)));
    $sp   = is_numeric($sale_price) ? $sale_price : 'NULL';

    // Handle new image
    $img_update = '';
    if (!empty($_FILES['image']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $allowed)) {
            $img_name = uniqid('prod_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . $img_name)) {
                $img_update = ", image='" . $conn->real_escape_string($img_name) . "'";
            }
        }
    }

    $conn->query("UPDATE products SET name='$n', category_id=$category_id, description='$desc', price=$price, sale_price=$sp, stock=$stock, sizes='$sz', colors='$cl', is_featured=$is_featured, is_active=$is_active $img_update WHERE id=$pid");
    setFlash('success', 'Product updated successfully!');
    header('Location: ' . SITE_URL . '/admin/edit-product.php');
    exit;
}

// Fetch product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id      = (int)$_GET['edit'];
    $edit_result  = $conn->query("SELECT * FROM products WHERE id=$edit_id LIMIT 1");
    $edit_product = $edit_result ? $edit_result->fetch_assoc() : null;
}

// Fetch all products
$search  = sanitize($_GET['search'] ?? '');
$where   = 'WHERE 1=1';
if ($search) $where .= " AND (p.name LIKE '%" . $conn->real_escape_string($search) . "%')";
$products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id $where ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$categories = getCategories(false);
?>

<div class="admin-page-header">
    <div>
        <h1><?= $edit_product ? 'Edit Product' : 'Manage Products' ?></h1>
        <div class="admin-breadcrumb"><a href="<?= SITE_URL ?>/admin/dashboard.php">Dashboard</a> / Products</div>
    </div>
    <a href="<?= SITE_URL ?>/admin/add-product.php" class="btn btn-gold btn-sm">
        <i class="fas fa-plus"></i> Add New
    </a>
</div>

<?php if ($edit_product): ?>
<!-- Edit Form -->
<div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header">
        <h3>Editing: <?= htmlspecialchars($edit_product['name']) ?></h3>
        <a href="<?= SITE_URL ?>/admin/edit-product.php" class="btn btn-outline btn-sm">Cancel</a>
    </div>
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($edit_product['name']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $edit_product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Price (ETB)</label>
                    <input type="number" name="price" class="form-control" step="0.01" required value="<?= $edit_product['price'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Sale Price (ETB)</label>
                    <input type="number" name="sale_price" class="form-control" step="0.01" value="<?= $edit_product['sale_price'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" min="0" value="<?= $edit_product['stock'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Sizes (comma separated)</label>
                    <input type="text" name="sizes" class="form-control" value="<?= htmlspecialchars(implode(', ', json_decode($edit_product['sizes'] ?? '[]', true) ?: [])) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Colors (comma separated)</label>
                    <input type="text" name="colors" class="form-control" value="<?= htmlspecialchars(implode(', ', json_decode($edit_product['colors'] ?? '[]', true) ?: [])) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">New Image (optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($edit_product['description']) ?></textarea>
            </div>
            <div style="display:flex;gap:20px;margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                    <input type="checkbox" name="is_featured" value="1" style="accent-color:#D4AF37;" <?= $edit_product['is_featured'] ? 'checked' : '' ?>> Featured
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                    <input type="checkbox" name="is_active" value="1" style="accent-color:#28a745;" <?= $edit_product['is_active'] ? 'checked' : '' ?>> Active
                </label>
            </div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Save Changes</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Products Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3>All Products (<?= count($products) ?>)</h3>
        <form method="GET" style="display:flex;gap:8px;">
            <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" style="width:220px;">
            <button type="submit" class="btn btn-dark btn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="admin-card-body" style="padding:0;overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><img src="<?= getProductImage($p['image']) ?>" class="product-thumb" alt=""></td>
                <td>
                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                    <?php if ($p['is_featured']): ?><span class="badge badge-gold" style="margin-left:6px;">Featured</span><?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['cat_name']) ?></td>
                <td>
                    <?= formatPrice($p['price']) ?>
                    <?php if ($p['sale_price']): ?><br><small style="color:#dc3545;"><?= formatPrice($p['sale_price']) ?></small><?php endif; ?>
                </td>
                <td>
                    <span class="badge <?= $p['stock'] > 5 ? 'badge-success' : ($p['stock'] > 0 ? 'badge-warning' : 'badge-danger') ?>">
                        <?= $p['stock'] ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                        <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <div class="table-actions">
                        <a href="<?= SITE_URL ?>/product-details.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="btn-view" title="View"><i class="fas fa-eye"></i></a>
                        <a href="?edit=<?= $p['id'] ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="<?= SITE_URL ?>/admin/delete-product.php?id=<?= $p['id'] ?>" class="btn-delete confirm-delete" title="Delete" data-msg="Delete this product permanently?"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#999;">No products found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
