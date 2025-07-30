<?php
class Router {
    private $routes = [];
    private $middlewares = [];
    
    public function __construct() {
        require_once __DIR__ . '/../helpers/SessionHelper.php';
        SessionHelper::start();
    }
    
    // Thêm route
    public function add($method, $path, $controller, $action, $middleware = null) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware
        ];
    }
    
    // GET route
    public function get($path, $controller, $action, $middleware = null) {
        $this->add('GET', $path, $controller, $action, $middleware);
    }
    
    // POST route
    public function post($path, $controller, $action, $middleware = null) {
        $this->add('POST', $path, $controller, $action, $middleware);
    }
    
    // Xử lý request
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Loại bỏ trailing slash trừ root
        if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
            $requestUri = rtrim($requestUri, '/');
        }
        
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $requestMethod, $requestUri)) {
                // Kiểm tra middleware
                if ($route['middleware'] && !$this->checkMiddleware($route['middleware'])) {
                    return;
                }
                
                // Load controller và thực thi action
                $this->executeController($route['controller'], $route['action']);
                return;
            }
        }
        
        // 404 Not Found
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
