<?php
/**
 * env/env.php
 * -----------
 * Lightweight .env file loader.
 * Call Env::load() once (ideally in db.php or a bootstrap file)
 * then retrieve values with Env::get('KEY') or Env::get('KEY', 'default').
 */

class Env
{
    private static array $vars = [];
    private static bool  $loaded = false;

    /**
     * Parse the .env file and populate the internal store.
     *
     * @param string $filePath  Absolute path to the .env file.
     */
    public static function load(string $filePath): void
    {
        if (self::$loaded) {
            return;
        }

        if (!file_exists($filePath)) {
            throw new RuntimeException("Environment file not found: {$filePath}");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Split on the first '=' only
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            [$key, $value] = $parts;
            $key   = trim($key);
            $value = trim($value);

            // Strip optional surrounding quotes (" or ')
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            self::$vars[$key] = $value;

            // Also push into $_ENV / putenv so native PHP functions work
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        self::$loaded = true;
    }

    /**
     * Retrieve an environment variable.
     *
     * @param string     $key      The variable name.
     * @param mixed|null $default  Fallback when the key is not present.
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$vars[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Check whether a key exists in the loaded env.
     */
    public static function has(string $key): bool
    {
        return isset(self::$vars[$key]);
    }
}
