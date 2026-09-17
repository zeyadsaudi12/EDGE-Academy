<?php

namespace App\Core;

class Router {
    private $routes = [];

    // Add a route
    public function add($method, $path, $controller, $action) {
        // Convert routes like /api/users/:id to regex
        // e.g., /api/users/([^/]+)
        $regex = preg_replace('/:[a-zA-Z0-9_]+/', '([^/]+)', $path);
        $regex = '#^' . $regex . '$#';
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'regex' => $regex,
            'controller' => $controller,
            'action' => $action
        ];
    }

    // Resolve and dispatch the request
    public function dispatch() {
        // Handle CORS Preflight request
        $this->handleCors();

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Strip query parameters
        $uri = explode('?', $uri)[0];

        // Strip any script/subfolder prefix before /api
        // This handles both local (localhost:3000) and shared hosting (Hostinger)
        // e.g. /MASAR/api/login  =>  /api/login
        //      /backend/public/index.php?api/login  =>  /api/login
        $apiPos = strpos($uri, '/api');
        if ($apiPos !== false) {
            $uri = substr($uri, $apiPos);
        } else {
            // Check if the PATH_INFO or QUERY_STRING has the api path (rewritten by .htaccess)
            // RewriteRule ^api/(.*)$ backend/public/index.php passes original path via REDIRECT_URL
            $redirectUrl = $_SERVER['REDIRECT_URL'] ?? '';
            if ($redirectUrl && strpos($redirectUrl, '/api') !== false) {
                $apiPos2 = strpos($redirectUrl, '/api');
                $uri = substr($redirectUrl, $apiPos2);
            } else {
                // Fallback: reconstruct from PATH_INFO or QUERY_STRING
                $pathInfo = $_SERVER['PATH_INFO'] ?? '';
                if ($pathInfo) {
                    $uri = $pathInfo;
                }
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['regex'], $uri, $matches)) {
                array_shift($matches); // Remove full match

                // Load JSON body if applicable
                $input = [];
                if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    $input = json_decode($json, true) ?? [];
                } else {
                    $input = $_POST;
                }

                // Call the controller action
                $controllerClass = "App\\Controllers\\" . $route['controller'];
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    $action = $route['action'];
                    if (method_exists($controller, $action)) {
                        // Pass URL path matches, request input parameters
                        // Merge params: URL matches first, then request input
                        call_user_func_array([$controller, $action], array_merge($matches, [$input]));
                        return;
                    }
                }
            }
        }

        // Route not found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'مسار غير موجود (Route not found)'
        ]);
    }

    private function handleCors() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
