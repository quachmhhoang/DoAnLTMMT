<?php
$title = htmlspecialchars($product->name) . ' - CellPhone Store';
ob_start();
?>

<div class="row">
    <div class="col-md-6">
        <?php 
        $images = !empty($product->images) ? explode(',', $product->images) : [];
        $firstImage = !empty($images) ? $images[0] : '/assets/images/no-image.jpg';
        ?>
        <img src="<?php echo htmlspecialchars($firstImage); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product->name); ?>">
    </div>
    
    <div class="col-md-6">
        <h1><?php echo htmlspecialchars($product->name); ?></h1>
        <div class="price mb-3"><?php echo number_format($product->price, 0, ',', '.'); ?>đ</div>
        
        <div class="mb-3">
            <strong>Danh mục:</strong> <?php echo htmlspecialchars($product->category_name); ?><br>
            <strong>Thương hiệu:</strong> <?php echo htmlspecialchars($product->brand_name); ?>
        </div>
        
        <div class="mb-4">
            <h5>Mô tả sản phẩm</h5>
            <p><?php echo nl2br(htmlspecialchars($product->description)); ?></p>
        </div>
        
        <?php if (SessionHelper::isCustomer()): ?>
            <form method="POST" action="/add-to-cart">
                <input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="quantity" class="form-label">Số lượng</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="10">
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                </button>
            </form>
        <?php elseif (!SessionHelper::isLoggedIn()): ?>
            <div class="alert alert-info">
                <a href="/login">Đăng nhập</a> để mua sản phẩm này.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
    <hr class="my-5">
    <h3>Sản phẩm liên quan</h3>
    <div class="row">
        <?php foreach ($relatedProducts as $relatedProduct): ?>
            <?php if ($relatedProduct->product_id != $product->product_id): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card">
                        <?php 
                        $relatedImages = !empty($relatedProduct->images) ? explode(',', $relatedProduct->images) : [];
                        $relatedFirstImage = !empty($relatedImages) ? $relatedImages[0] : '/assets/images/no-image.jpg';
                        ?>
                        <img src="<?php echo htmlspecialchars($relatedFirstImage); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($relatedProduct->name); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($relatedProduct->name); ?></h5>
                            <div class="price"><?php echo number_format($relatedProduct->price, 0, ',', '.'); ?>đ</div>
                            <div class="mt-2">
                                <a href="/products/<?php echo $relatedProduct->product_id; ?>" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
