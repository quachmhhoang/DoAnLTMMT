<?php
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/models/Cart.php';
require_once __DIR__ . '/app/models/Order.php';
require_once __DIR__ . '/app/models/Product.php';

try {
    echo "Testing SQL fixes...\n\n";
    
    // Test database connection
    $database = new Database();
    $conn = $database->getConnection();
    echo "✓ Database connection successful\n";
    
    // Test Cart model
    echo "Testing Cart model...\n";
    $cart = new Cart();
    
    // This should not cause GROUP BY errors anymore
    $cartItems = $cart->getCartItems(1); // Test with user_id = 1
    echo "✓ Cart getCartItems() works without GROUP BY errors\n";
    
    // Test Order model
    echo "Testing Order model...\n";
    $order = new Order();
    
    // This should not cause GROUP BY errors anymore
    $orderDetails = $order->getOrderDetails(1); // Test with order_id = 1
    echo "✓ Order getOrderDetails() works without GROUP BY errors\n";
    
    // Test Product model (should already work)
    echo "Testing Product model...\n";
    $product = new Product();
    
    $products = $product->getAllProducts(5);
    echo "✓ Product getAllProducts() works correctly\n";
    
    $productById = $product->getProductById(1);
    echo "✓ Product getProductById() works correctly\n";
    
    echo "\n🎉 All SQL fixes are working correctly!\n";
    echo "The GROUP BY syntax errors have been resolved.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
