<?php
$title = 'Trang chủ - CellPhone Store';
ob_start();
?>

<!-- Auto-sliding Banner -->
<div class="container-fluid px-0 mb-4">
    <div class="banner-slider">
        <div class="banner-container">
            <div class="banner-slide active">
                <img src="/assets/banner/Sliding-iphone-v3.webp" alt="iPhone Banner">
            </div>
            <div class="banner-slide">
                <img src="/assets/banner/oppo-reno14-f-Sliding-0825.png" alt="OPPO Reno14 Banner">
            </div>
            <div class="banner-slide">
                <img src="/assets/banner/galaxy-z-7-home-0825.webp" alt="Galaxy Z Banner">
            </div>
            <div class="banner-slide">
                <img src="/assets/banner/home-xiaomi-miband10.webp" alt="Xiaomi Mi Band Banner">
            </div>
        </div>
        
        <!-- Banner Navigation Dots -->
        <div class="banner-dots">
            <span class="dot active" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span>
            <span class="dot" onclick="currentSlide(4)"></span>
        </div>
        
        <!-- Banner Navigation Arrows -->
        <button class="banner-nav prev" onclick="plusSlides(-1)">&#10094;</button>
        <button class="banner-nav next" onclick="plusSlides(1)">&#10095;</button>
    </div>
</div>

<!-- Quick Categories Section -->
<div class="container mb-5">
    <div class="quick-categories">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="quick-category-item">
                    <div class="category-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <span>Điện thoại</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="quick-category-item">
                    <div class="category-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <span>Laptop</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="quick-category-item">
                    <div class="category-icon">
                        <i class="fas fa-tablet-alt"></i>
                    </div>
                    <span>Tablet</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="quick-category-item">
                    <div class="category-icon">
                        <i class="fas fa-headphones"></i>
                    </div>
                    <span>Phụ kiện</span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Featured Products Section -->
<?php if (!empty($featuredProducts)): ?>
<div class="container mb-5">
    <div class="section-header">
        <h2 class="section-title">Sản phẩm nổi bật</h2>
        <div class="section-divider"></div>
    </div>
    <div class="product-grid-modern">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card-modern">
                <?php 
                $images = !empty($product->images) ? explode(',', $product->images) : [];
                $firstImage = !empty($images) ? $images[0] : '/assets/images/no-image.jpg';
                ?>
                <div class="product-image-container">
                    <img src="<?php echo htmlspecialchars($firstImage); ?>" class="product-image-modern" alt="<?php echo htmlspecialchars($product->name); ?>">
                    <div class="product-overlay">
                        <a href="/products/<?php echo $product->product_id; ?>" class="btn btn-quick-view">
                            <i class="fas fa-eye"></i> Xem nhanh
                        </a>
                    </div>
                </div>
                <div class="product-info">
                    <h5 class="product-name"><?php echo htmlspecialchars($product->name); ?></h5>
                    <p class="product-description"><?php echo substr(htmlspecialchars($product->description), 0, 80) . '...'; ?></p>
                    <div class="product-price">
                        <span class="current-price"><?php echo number_format($product->price, 0, ',', '.'); ?>₫</span>
                        <span class="original-price"><?php echo number_format($product->price * 1.2, 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="product-rating">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="rating-text">(4.8)</span>
                    </div>
                    <div class="product-actions">
                        <a href="/products/<?php echo $product->product_id; ?>" class="btn btn-view-detail">
                            Xem chi tiết
                        </a>
                        <?php if (SessionHelper::isCustomer()): ?>
                            <form method="POST" action="/add-to-cart" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-add-cart">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="/products" class="btn btn-view-all">
            Xem tất cả sản phẩm
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
