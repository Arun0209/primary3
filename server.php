<?php
/**
 * Router for PHP's built-in development server (mirrors .htaccess):
 *   php -S 127.0.0.1:8080 server.php
 * Not used on Apache/cPanel.
 */
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}
$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$file = __DIR__ . $uri;

// Block private folders and dotfiles, exactly like .htaccess.
if (preg_match('#^/(config|includes|components|layouts|pages|database|storage|admin/_inc)(/|$)#', $uri)
    || preg_match('#/\.#', $uri) || preg_match('#^/api/.*\.php$#', $uri)
    || preg_match('#\.(sql|log|lock|md|sqlite|env|example)$#i', $uri)
    || (str_starts_with($uri, '/uploads/') && preg_match('#\.(php\d?|phtml|phar|html?|svg|js)$#i', $uri))) {
    $_SERVER['REQUEST_URI'] = '/__forbidden__/';
    require __DIR__ . '/index.php';
    return true;
}
if ($uri !== '/' && is_file($file)) {
    if (str_ends_with($file, '.php')) {       // admin/*.php, install.php
        $_SERVER['SCRIPT_NAME'] = $uri;
        chdir(dirname($file));
        require $file;
        return true;
    }
    return false;                              // static asset
}
if ($uri !== '/' && is_dir($file) && is_file($file . '/index.php')) {
    if (!str_ends_with($uri, '/')) {
        header('Location: ' . $uri . '/', true, 301);
        return true;
    }
    chdir($file);
    require $file . '/index.php';
    return true;
}
require __DIR__ . '/index.php';
return true;
