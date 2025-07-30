<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
$title = 'Sản phẩm - CellPhone Store';
ob_start();
?>

<div class="row">
    <!-- Sidebar Filter -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5>Bộ lọc</h5>
            </div>
            <div class="card-body">
                <!-- Search -->
                <form method="GET" action="/products" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Categories -->
                <?php if (!empty($categories)): ?>
                    <h6>Danh mục</h6>
                    <div class="list-group mb-3">
                        <a href="/products" class="list-group-item list-group-item-action <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                            Tất cả
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="/products?category=<?php echo $cat->category_id; ?>" 
                               class="list-group-item list-group-item-action <?php echo (isset($_GET['category']) && $_GET['category'] == $cat->category_id) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Products -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sản phẩm</h2>
            <span class="text-muted"><?php echo count($products); ?> sản phẩm</span>
        </div>
        
        <?php if (!empty($products)): ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card h-100">
                            <?php 
                            $images = !empty($product->images) ? explode(',', $product->images) : [];
                            $firstImage = !empty($images) ? $images[0] : '/assets/images/no-image.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($firstImage); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product->name); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($product->name); ?></h5>
                                <p class="card-text flex-grow-1"><?php echo substr(htmlspecialchars($product->description), 0, 100) . '...'; ?></p>
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
            
            <!-- Pagination -->
            <?php if (isset($total_pages) && $total_pages > 1): ?>
                <nav aria-label="Product pagination">
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
            <div class="text-center">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>Không tìm thấy sản phẩm nào</h4>
                <p class="text-muted">Thử tìm kiếm với từ khóa khác</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout/app.php';
?>
