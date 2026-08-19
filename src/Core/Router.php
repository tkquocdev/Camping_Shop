<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $fullPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // ============================================
        // PATH EXTRACTION FOR SUBDIRECTORY DEPLOYMENT
        // ============================================
        // Hỗ trợ cả Apache (.htaccess) và Nginx (try_files)
        // - Apache: REQUEST_URI = /Camping_Shop/public/auth/google_callback
        // - Nginx:  REQUEST_URI = /auth/google_callback (tùy config)
        
        $path = $fullPath;
        
        // Method 1: Detect if REQUEST_URI contains subdirectory path
        if (strpos($fullPath, '/Camping_Shop/public/') !== false) {
            $path = substr($fullPath, strpos($fullPath, '/Camping_Shop/public/') + strlen('/Camping_Shop/public'));
            if (empty($path)) $path = '/';
        } 
        // Method 2: Try to strip SCRIPT_NAME directory (fallback)
        else {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath !== '/' && $basePath !== '\\' && strpos($fullPath, $basePath) === 0) {
                $path = substr($fullPath, strlen($basePath));
            }
        }
        
        // Ensure path starts with /
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $params = [];
            if ($this->matchRoute($route, $path, $params)) {
                if (is_array($handler)) {
                    $controllerClass = $handler[0];
                    $methodName = $handler[1];
                    $controller = new $controllerClass();
                    call_user_func_array([$controller, $methodName], $params);
                } else {
                    call_user_func_array($handler, $params);
                }
                return;
            }
        }

        // 404 error
        http_response_code(404);
        echo "404 Not Found\n";
        echo "Path: " . htmlspecialchars($path) . " | Method: " . htmlspecialchars($method);
    }

    private function matchRoute($route, $path, &$params) {
        $routeParts = explode('/', trim($route, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (count($routeParts) !== count($pathParts)) {
            return false;
        }

        $params = [];
        for ($i = 0; $i < count($routeParts); $i++) {
            if (preg_match('/^\{(.+)\}$/', $routeParts[$i], $matches)) {
                $params[] = $pathParts[$i];
            } elseif ($routeParts[$i] !== $pathParts[$i]) {
                return false;
            }
        }

        return true;
    }
}