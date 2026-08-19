<?php

namespace App\Utils;

class Settings {
    private static ?string $path = null;

    private static function getPath(): string {
        if (self::$path === null) {
            self::$path = defined('ROOT_PATH') ? ROOT_PATH . '/storage/settings.json' : __DIR__ . '/../../storage/settings.json';
        }
        return self::$path;
    }

    public static function all(): array {
        $path = self::getPath();
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    public static function get(string $key, $default = null) {
        $data = self::all();
        return $data[$key] ?? $default;
    }

    public static function set(string $key, $value): bool {
        $data = self::all();
        $data[$key] = $value;
        return self::save($data);
    }

    public static function save(array $data): bool {
        $path = self::getPath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return (bool) file_put_contents($path, $json);
    }
}
