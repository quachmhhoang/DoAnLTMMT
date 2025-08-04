<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Sản phẩm - CellPhone Store';
ob_start();
?>

<!-- Products Page Header -->
<div class="container mb-4">
    <div class="section-header">
        <h2 class="section-title">Sản phẩm</h2>
        <div class="section-divider"></div>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Modern Sidebar Filter -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="filter-sidebar">
                <div class="filter-card">
                    <div class="filter-header">
                        <h5><i class="fas fa-filter"></i> Bộ lọc</h5>
                    </div>
                    <div class="filter-body">
                        <!-- Search -->
                        <div class="filter-section">
                            <form method="GET" action="/products" class="search-form">
                                <div class="search-input-group">
                                    <input type="text" class="search-input" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                    <button class="search-btn" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Categories -->
                        <?php if (!empty($categories)): ?>
                            <div class="filter-section">
                                <h6 class="filter-title"><i class="fas fa-list"></i> Danh mục</h6>
                                <div class="category-filter-list">
                                    <a href="/products" class="category-filter-item <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                                        <span class="category-name">Tất cả</span>
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                    <?php foreach ($categories as $cat): ?>
                                        <a href="/products?category=<?php echo $cat->category_id; ?>" 
                                           class="category-filter-item <?php echo (isset($_GET['category']) && $_GET['category'] == $cat->category_id) ? 'active' : ''; ?>">
                                            <span class="category-name"><?php echo htmlspecialchars($cat->name); ?></span>
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Price Range -->
                        <div class="filter-section">
                            <h6 class="filter-title"><i class="fas fa-dollar-sign"></i> Khoảng giá</h6>
                            <div class="price-filter-list">
                                <a href="/products<?php echo isset($_GET['category']) ? '?category=' . $_GET['category'] : ''; ?>" class="price-filter-item">
                                    Tất cả
                                </a>
                                <a href="/products?price_range=under5" class="price-filter-item">
                                    Dưới 5 triệu
                                </a>
                                <a href="/products?price_range=5to10" class="price-filter-item">
                                    5 - 10 triệu
                                </a>
                                <a href="/products?price_range=10to20" class="price-filter-item">
                                    10 - 20 triệu
                                </a>
                                <a href="/products?price_range=over20" class="price-filter-item">
                                    Trên 20 triệu
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9 col-md-8">
            <div class="products-header">
                <div class="products-info">
                    <h3 class="products-count"><?php echo count($products); ?> sản phẩm</h3>
                    <p class="products-subtitle">Tìm thấy sản phẩm phù hợp với bạn</p>
                </div>
                <div class="products-sort">
                    <select class="sort-select">
                        <option value="newest">Mới nhất</option>
                        <option value="price_asc">Giá thấp đến cao</option>
                        <option value="price_desc">Giá cao đến thấp</option>
                        <option value="popular">Phổ biến</option>
                    </select>
                </div>
            </div>
            
            <?php if (!empty($products)): ?>
                <div class="product-grid-modern">
                    <?php foreach ($products as $product): ?>
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
                
                <!-- Pagination -->
                <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <nav aria-label="Product pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="/products?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-products-found">
                    <div class="no-products-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Không tìm thấy sản phẩm</h3>
                    <p>Hãy thử tìm kiếm với từ khóa khác hoặc xem tất cả sản phẩm</p>
                    <a href="/products" class="btn btn-primary">Xem tất cả sản phẩm</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>