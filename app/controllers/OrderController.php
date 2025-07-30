<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';

class OrderController {
    
    // Trang thanh toán
    public function checkout() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        
        $cart = new Cart();
        $user = SessionHelper::getCurrentUser();
        
        $cartItems = $cart->getCartItems($user->user_id);
        $cartTotal = $cart->getCartTotal($user->user_id);
        
        if (empty($cartItems)) {
            SessionHelper::setFlash('error', 'Giỏ hàng của bạn đang trống!');
            header('Location: /cart');
            exit();
        }
        
        include __DIR__ . '/../views/order/checkout.php';
    }
    
    // Xử lý đặt hàng
    public function placeOrder() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cart = new Cart();
            $order = new Order();
            $user = SessionHelper::getCurrentUser();
            
            $cartItems = $cart->getCartItems($user->user_id);
            
            if (empty($cartItems)) {
                SessionHelper::setFlash('error', 'Giỏ hàng của bạn đang trống!');
                header('Location: /cart');
                exit();
            }
            
            // Tạo đơn hàng
            $order_id = $order->create($user->user_id, $cartItems);
            
            if ($order_id) {
                // Xóa giỏ hàng sau khi đặt hàng thành công
                $cart->clearCart($user->user_id);
                
                SessionHelper::setFlash('success', 'Đặt hàng thành công! Mã đơn hàng: #' . $order_id);
                header('Location: /orders/' . $order_id);
                exit();
            } else {
                SessionHelper::setFlash('error', 'Có lỗi xảy ra khi đặt hàng! Vui lòng thử lại.');
                header('Location: /checkout');
                exit();
            }
        }
        
        header('Location: /checkout');
        exit();
    }
    
    // Danh sách đơn hàng của user
    public function myOrders() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        
        $order = new Order();
        $user = SessionHelper::getCurrentUser();
        
        $orders = $order->getUserOrders($user->user_id);
        
        include __DIR__ . '/../views/order/my_orders.php';
    }
    
    // Chi tiết đơn hàng
    public function orderDetail() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        
        $order_id = isset($_GET['params'][0]) ? (int)$_GET['params'][0] : 0;
        
        if (!$order_id) {
            header('Location: /orders');
            exit();
        }
        
        $order = new Order();
        $user = SessionHelper::getCurrentUser();
        
        $orderInfo = $order->getOrderById($order_id);
        
        // Kiểm tra quyền xem đơn hàng
        if (!$orderInfo || ($orderInfo->user_id != $user->user_id && !SessionHelper::isAdmin())) {
            header('Location: /orders');
            exit();
        }
        
        $orderDetails = $order->getOrderDetails($order_id);
        
        include __DIR__ . '/../views/order/order_detail.php';
    }
}
?>
