<?php
$title = 'Trang chủ - CellPhone Store';
ob_start();
?>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="bg-primary text-white p-5 rounded">
            <div class="text-center">
                <h1 class="display-4">Chào mừng đến CellPhone Store</h1>
                <p class="lead">Cửa hàng điện thoại uy tín với giá tốt nhất thị trường</p>
                <a href="/products" class="btn btn-light btn-lg">Xem sản phẩm</a>
            </div>
        </div>
    </div>
</div>

<!-- Categories -->
<?php if (!empty($categories)): ?>
<div class="row mb-5">
    <div class="col-12">
        <h2 class="text-center mb-4">Danh mục sản phẩm</h2>
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                            <h5 class="card-title"><?php echo htmlspecialchars($category->name); ?></h5>
                            <a href="/products?category=<?php echo $category->category_id; ?>" class="btn btn-outline-primary">Xem sản phẩm</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<div class="row">
    <div class="col-12">
        <h2 class="text-center mb-4">Sản phẩm nổi bật</h2>
        <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card">
                        <?php 
                        $images = !empty($product->images) ? explode(',', $product->images) : [];
                        $firstImage = !empty($images) ? $images[0] : '/assets/images/no-image.jpg';
                        ?>
                        <img src="<?php echo htmlspecialchars($firstImage); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product->name); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product->name); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($product->description), 0, 100) . '...'; ?></p>
                            <div class="price"><?php echo number_format($product->price, 0, ',', '.'); ?>đ</div>
                            <div class="mt-2">
                                <a href="/products/<?php echo $product->product_id; ?>" class="btn btn-primary btn-sm">Xem chi tiết</a>
                                <?php if (SessionHelper::isCustomer()): ?>
                                    <form method="POST" action="/add-to-cart" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center">
            <a href="/products" class="btn btn-primary">Xem tất cả sản phẩm</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
