<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Brand.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';

class HomeController {
    
    // Trang chủ
    public function index() {
        $productModel = new Product();
        $categoryModel = new Category();
        
        // Lấy sản phẩm mới nhất
        $featuredProducts = $productModel->getAllProducts(8);
        
        // Lấy danh mục
        $categories = $categoryModel->getAllCategories();
        
        include __DIR__ . '/../views/home/index.php';
    }
    
    // Trang sản phẩm
    public function products() {
        $productModel = new Product();
        $categoryModel = new Category();
        $brandModel = new Brand();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        $category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        if (!empty($search)) {
            $products = $productModel->searchProducts($search);
            $total_products = count($products);
        } elseif ($category_id) {
            $products = $productModel->getProductsByCategory($category_id);
            $total_products = count($products);
        } else {
            $products = $productModel->getAllProducts($limit, $offset);
            $total_products = $productModel->countProducts();
        }
        
        $total_pages = ceil($total_products / $limit);
        $categories = $categoryModel->getAllCategories();
        $brands = $brandModel->getAllBrands();
        
        include __DIR__ . '/../views/home/products.php';
    }
    
    // Chi tiết sản phẩm
    public function productDetail() {
        $product_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$product_id) {
            header('Location: /products');
            exit();
        }
        
        $productModel = new Product();
        $product = $productModel->getProductById($product_id);
        
        if (!$product) {
            header('Location: /products');
            exit();
        }
        
        // Lấy sản phẩm liên quan
        $relatedProducts = $productModel->getProductsByCategory($product->category_id, 4);
        
        include __DIR__ . '/../views/home/product_detail.php';
    }
    
    // Thêm vào giỏ hàng
    public function addToCart() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($product_id && $quantity > 0) {
                $cart = new Cart();
                $user = SessionHelper::getCurrentUser();
                
                if ($cart->addToCart($user->user_id, $product_id, $quantity)) {
                    SessionHelper::setFlash('success', 'Đã thêm sản phẩm vào giỏ hàng!');
                } else {
                    SessionHelper::setFlash('error', 'Có lỗi xảy ra khi thêm sản phẩm!');
                }
            }
        }
        
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/products'));
        exit();
    }
    
    // Giỏ hàng
    public function cart() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        
        $cart = new Cart();
        $user = SessionHelper::getCurrentUser();
        
        $cartItems = $cart->getCartItems($user->user_id);
        $cartTotal = $cart->getCartTotal($user->user_id);
        
        include __DIR__ . '/../views/home/cart.php';
    }
    
    // Cập nhật giỏ hàng
    public function updateCart() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cart = new Cart();
            
            if (isset($_POST['update_quantity'])) {
                $cart_detail_id = (int)$_POST['cart_detail_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity > 0) {
                    $cart->updateQuantity($cart_detail_id, $quantity);
                } else {
                    $cart->removeFromCart($cart_detail_id);
                }
            } elseif (isset($_POST['remove_item'])) {
                $cart_detail_id = (int)$_POST['cart_detail_id'];
                $cart->removeFromCart($cart_detail_id);
            }
        }
        
        header('Location: /cart');
        exit();
    }
}
?>
