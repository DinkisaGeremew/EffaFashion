<?php
$page_title = 'Add Product';
require_once __DIR__ . '/includes/admin_header.php';

$categories = getCategories(false);
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = sanitize($_POST['name']        ?? '');
    $category_id = (int)($_POST['category_id']    ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $price       = (float)($_POST['price']        ?? 0);
    $sale_price  = $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null;
    $stock       = (int)($_POST['stock']          ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $sizes_arr   = array_filter(array_map('trim', explode(',', $_POST['sizes']  ?? '')));
    $colors_arr  = array_filter(array_map('trim', explode(',', $_POST['colors'] ?? '')));
    $slug        = createSlug($name);

    if (empty($name) || !$category_id || $price <= 0) {
        $error = 'Name, category and price are required.';
    } else {
        // Handle image upload
        $image_name = '';
        if (!empty($_FILES['image']['name'])) {
            $ext        = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed    = ['jpg','jpeg','png','webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Invalid image format. Use JPG, PNG or WebP.';
            } else {
                $image_name = uniqid('prod_') . '.' . $ext;
                $dest       = UPLOAD_PATH . $image_name;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $error = 'Failed to upload image.';
                    $image_name = '';
                }
            }
        }

        if (!$error) {
            // Ensure unique slug
            $slug_base = $slug;
            $i = 1;
            while ($conn->query("SELECT id FROM products WHERE slug='" . $conn->real_escape_string($slug) . "' LIMIT 1")->num_rows > 0) {
                $slug = $slug_base . '-' . $i++;
            }

            $n    = $conn->real_escape_string($name);
            $sl   = $conn->real_escape_string($slug);
            $desc = $conn->real_escape_string($description);
            $img  = $conn->real_escape_string($image_name);
            $sp   = $sale_price !== null ? $sale_price : 'NULL';
            $sz   = $conn->real_escape_string(json_encode(array_values($sizes_arr)));
            $cl   = $conn->real_escape_string(json_encode(array_values($colors_arr)));

            $conn->query("INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, image, sizes, colors, is_featured)
                          VALUES ($category_id, '$n', '$sl', '$desc', $price, $sp, $stock, '$img', '$sz', '$cl', $is_featured)");

            if ($conn->affected_rows > 0) {
                setFlash('success', "Product \"$name\" added successfully!");
                header('Location: ' . SITE_URL . '/admin/edit-product.php');
                exit;
            } else {
                $error = 'Failed to add product: ' . $conn->error;
            }
        }
    }
}
?>

<div class="admin-page-header">
    <div>
        <h1>Add New Product</h1>
        <div class="admin-breadcrumb"><a href="<?= SITE_URL ?>/admin/dashboard.php">Dashboard</a> / Add Product</div>
    </div>
    <a href="<?= SITE_URL ?>/admin/edit-product.php" class="btn btn-outline btn-sm">
        <i class="fas fa-list"></i> All Products
    </a>
</div>

<?php if ($error):   ?><div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

        <!-- Main Info -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header"><h3>Product Information</h3></div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label">Product Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter product name" required
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Product description..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    <div class="admin-form-grid">
                        <div class="form-group">
                            <label class="form-label">Price (ETB) <span class="required">*</span></label>
                            <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" min="0" required
                                   value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sale Price (ETB) <small style="color:#999;">(optional)</small></label>
                            <input type="number" name="sale_price" class="form-control" placeholder="Leave empty if no sale" step="0.01" min="0"
                                   value="<?= htmlspecialchars($_POST['sale_price'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="admin-form-grid">
                        <div class="form-group">
                            <label class="form-label">Stock Quantity <span class="required">*</span></label>
                            <input type="number" name="stock" class="form-control" placeholder="0" min="0" required
                                   value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category <span class="required">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="admin-form-grid">
                        <div class="form-group">
                            <label class="form-label">Sizes <small style="color:#999;">(comma separated)</small></label>
                            <input type="text" name="sizes" class="form-control" placeholder="XS, S, M, L, XL"
                                   value="<?= htmlspecialchars($_POST['sizes'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Colors <small style="color:#999;">(comma separated)</small></label>
                            <input type="text" name="colors" class="form-control" placeholder="Black, White, Gold"
                                   value="<?= htmlspecialchars($_POST['colors'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="admin-card" style="margin-bottom:20px;">
                <div class="admin-card-header"><h3>Product Image</h3></div>
                <div class="admin-card-body">
                    <div class="image-upload-box" onclick="document.getElementById('imageInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload image</p>
                        <small style="color:#bbb;">JPG, PNG, WebP — Max 5MB</small>
                    </div>
                    <input type="file" id="imageInput" name="image" accept="image/*" style="display:none;"
                           onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview"></div>
                </div>
            </div>

            <div class="admin-card" style="margin-bottom:20px;">
                <div class="admin-card-header"><h3>Options</h3></div>
                <div class="admin-card-body">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
                        <input type="checkbox" name="is_featured" value="1" style="accent-color:#D4AF37;width:16px;height:16px;"
                               <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                        Mark as Featured / New Arrival
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-gold btn-block btn-lg">
                <i class="fas fa-plus-circle"></i> Add Product
            </button>
        </div>
    </div>
</form>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
