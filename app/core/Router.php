<?php
/**
 * Class Router - Xử lý routing cho ứng dụng
 * Quản lý các routes, middleware và dispatch request đến controller tương ứng
 */
class Router {
    // Mảng chứa tất cả các routes đã đăng ký
    private $routes = [];
    // Mảng chứa các middleware (hiện tại chưa sử dụng)
    private $middlewares = [];

    /**
     * Constructor - Khởi tạo router và session
     */
    public function __construct() {
        // Import SessionHelper và khởi tạo session
        require_once __DIR__ . '/../helpers/SessionHelper.php';
        SessionHelper::start();
    }

    /**
     * Thêm route vào danh sách routes
     * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
     * @param string $path Đường dẫn URL pattern
     * @param string $controller Tên controller
     * @param string $action Tên method trong controller
     * @param string|null $middleware Middleware cần kiểm tra (auth, admin, customer, guest)
     */
    public function add($method, $path, $controller, $action, $middleware = null) {
        $this->routes[] = [
            'method' => strtoupper($method),    // Chuyển method về uppercase
            'path' => $path,                    // URL pattern
            'controller' => $controller,        // Tên controller
            'action' => $action,                // Tên method
            'middleware' => $middleware         // Middleware (nếu có)
        ];
    }

    /**
     * Đăng ký GET route
     * @param string $path Đường dẫn URL
     * @param string $controller Tên controller
     * @param string $action Tên method
     * @param string|null $middleware Middleware
     */
    public function get($path, $controller, $action, $middleware = null) {
        $this->add('GET', $path, $controller, $action, $middleware);
    }

    /**
     * Đăng ký POST route
     * @param string $path Đường dẫn URL
     * @param string $controller Tên controller
     * @param string $action Tên method
     * @param string|null $middleware Middleware
     */
    public function post($path, $controller, $action, $middleware = null) {
        $this->add('POST', $path, $controller, $action, $middleware);
    }

    /**
     * Xử lý request hiện tại và dispatch đến controller tương ứng
     */
    public function dispatch() {
        // Lấy HTTP method từ request
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        // Lấy URI path (loại bỏ query string)
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Loại bỏ trailing slash trừ root path
        if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
            $requestUri = rtrim($requestUri, '/');
        }

        // Duyệt qua tất cả routes để tìm route phù hợp
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $requestMethod, $requestUri)) {
                // Kiểm tra middleware nếu có
                if ($route['middleware'] && !$this->checkMiddleware($route['middleware'])) {
                    return;  // Middleware failed, dừng xử lý
                }

                // Load controller và thực thi action
                $this->executeController($route['controller'], $route['action']);
                return;  // Route đã được xử lý, thoát
            }
        }

        // Không tìm thấy route phù hợp - hiển thị 404
        $this->show404();
    }
    
    // Kiểm tra route có khớp không
    private function matchRoute($route, $method, $uri) {
        if ($route['method'] !== $method) {
            return false;
        }
        
        // Chuyển đổi route pattern thành regex
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route['path']);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        if (preg_match($pattern, $uri, $matches)) {
            // Lưu parameters
            array_shift($matches); // Loại bỏ full match
            $_GET['params'] = $matches;
            return true;
        }
        
        return false;
    }
    
    // Kiểm tra middleware
    private function checkMiddleware($middleware) {
        switch ($middleware) {
            case 'auth':
                if (!SessionHelper::isLoggedIn()) {
                    header('Location: /login');
                    exit();
                }
                break;
                
            case 'admin':
                if (!SessionHelper::isAdmin()) {
                    header('Location: /login');
                    exit();
                }
                break;
                
            case 'customer':
                if (!SessionHelper::isCustomer()) {
                    header('Location: /login');
                    exit();
                }
                break;
                
            case 'guest':
                if (SessionHelper::isLoggedIn()) {
                    if (SessionHelper::isAdmin()) {
                        header('Location: /admin');
                    } else {
                        header('Location: /');
                    }
                    exit();
                }
                break;
        }
        
        return true;
    }
    
    // Thực thi controller
    private function executeController($controllerName, $action) {
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            $this->show404();
            return;
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            $this->show404();
            return;
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $action)) {
            $this->show404();
            return;
        }
        
        $controller->$action();
    }
    
    // Hiển thị trang 404
    private function show404() {
        http_response_code(404);
        include __DIR__ . '/../views/errors/404.php';
    }
    
    // Helper method để tạo URL
    public static function url($path = '') {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . $host . '/' . ltrim($path, '/');
    }
}
?>
