<?php
$title = htmlspecialchars($product->name) . ' - CellPhone Store';
ob_start();
?>

<div class="row">
    <div class="col-md-6">
        <?php
        $images = !empty($product->images) ? explode(',', $product->images) : [];
        $firstImage = !empty($images) ? $images[0] : '/assets/images/no-image.svg';
        ?>

        <!-- Main Product Image -->
        <div class="product-image-gallery">
            <div class="main-image mb-3">
                <img id="main-product-image"
                     src="<?php echo htmlspecialchars($firstImage); ?>"
                     class="img-fluid rounded shadow"
                     alt="<?php echo htmlspecialchars($product->name); ?>"
                     style="width: 100%; height: 400px; object-fit: cover;">
            </div>

            <!-- Thumbnail Images -->
            <?php if (count($images) > 1): ?>
            <div class="thumbnail-images">
                <div class="row">
                    <?php foreach ($images as $index => $image): ?>
                    <div class="col-3 mb-2">
                        <img src="<?php echo htmlspecialchars($image); ?>"
                             class="img-thumbnail thumbnail-image <?php echo $index === 0 ? 'active' : ''; ?>"
                             alt="<?php echo htmlspecialchars($product->name); ?>"
                             style="width: 100%; height: 80px; object-fit: cover; cursor: pointer;"
                             onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
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
                        $relatedFirstImage = !empty($relatedImages) ? $relatedImages[0] : '/assets/images/no-image.svg';
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

<style>
.thumbnail-image {
    border: 2px solid transparent;
    transition: border-color 0.3s ease;
}

.thumbnail-image.active {
    border-color: #007bff;
}

.thumbnail-image:hover {
    border-color: #0056b3;
}

.main-image img {
    transition: transform 0.3s ease;
}

.main-image img:hover {
    transform: scale(1.05);
}
</style>

<script>
function changeMainImage(imageSrc, thumbnailElement) {
    // Update main image
    const mainImage = document.getElementById('main-product-image');
    mainImage.src = imageSrc;

    // Update active thumbnail
    document.querySelectorAll('.thumbnail-image').forEach(img => {
        img.classList.remove('active');
    });
    thumbnailElement.classList.add('active');
}

// Add zoom functionality on click
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('main-product-image');
    if (mainImage) {
        mainImage.addEventListener('click', function() {
            // Create modal for image zoom
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Xem ảnh chi tiết</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${this.src}" class="img-fluid" alt="Product Image">
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // Remove modal from DOM when hidden
            modal.addEventListener('hidden.bs.modal', function() {
                modal.remove();
            });
        });
    }
});
</script>
