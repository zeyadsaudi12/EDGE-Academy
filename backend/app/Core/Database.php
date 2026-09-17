<?php

namespace App\Core;

use MongoDB\Client;

class Database {
    private static $client = null;
    private static $db = null;

    public static function connect() {
        if (self::$db !== null) {
            return self::$db;
        }

        $uri = getenv('MONGODB_URI') ?: ($_ENV['MONGODB_URI'] ?? ($_SERVER['MONGODB_URI'] ?? ''));
        if (!$uri) {
            if (file_exists(__DIR__ . '/../../../.env')) {
                $lines = file(__DIR__ . '/../../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strpos($line, 'MONGODB_URI=') === 0) {
                        $uri = trim(substr($line, strlen('MONGODB_URI=')));
                        $uri = trim($uri, '"\'');
                        break;
                    }
                }
            }
        }
        if (!$uri) {
            $uri = 'mongodb://localhost:27017';
        }

        // Parse database name from URI if possible, or default
        // e.g. mongodb+srv://username:password@cluster.mongodb.net/dbname
        $dbName = 'test'; // default database name matching the Node.js default
        
        $parsedUrl = parse_url($uri);
        if (isset($parsedUrl['path']) && trim($parsedUrl['path'], '/') !== '') {
            $dbName = trim($parsedUrl['path'], '/');
        }

        try {
            self::$client = new Client($uri);
            self::$db = self::$client->selectDatabase($dbName);
            return self::$db;
        } catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public static function getCollection($name) {
        $db = self::connect();
        return $db->selectCollection($name);
    }

    public static function getDb() {
        return self::connect();
    }
}
