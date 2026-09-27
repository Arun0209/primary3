<?php
/**
 * Front controller — every public request is routed through here
 * (see .htaccess). Pages are server-rendered PHP views wrapped in a layout.
 */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if (!config('installed')) {
    redirect('/install.php');
}

$path = current_path();

/* ---------- Static-like endpoints ---------- */
if ($path === '/robots.txt') {
    require __DIR__ . '/pages/robots.php';
    exit;
}
if ($path === '/sitemap.xml') {
    require __DIR__ . '/pages/sitemap.php';
    exit;
}

/* ---------- JSON / fragment API ---------- */
if (preg_match('#^/api/(contact|case-studies)/?$#', $path, $m)) {
    define('HT_API', true);
    send_security_headers();
    require __DIR__ . '/api/' . $m[1] . '.php';
    exit;
}

/* ---------- Enforce trailing slash on page URLs ---------- */
if ($path !== '/' && !str_ends_with($path, '/') && !preg_match('/\.[a-z0-9]{2,5}$/i', $path)) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    redirect($path . '/' . ($qs !== '' ? '?' . $qs : ''), 301);
}

/* ---------- Routes ---------- */
$routes = [
    '/'                  => 'home',
    '/ai-solutions/'     => 'ai-solutions',
    '/case-studies/'     => 'case-studies',
    '/about/'            => 'about',
    '/contact/'          => 'contact',
    '/privacy-policy/'   => 'legal',
    '/terms/'            => 'legal',
];
$aliases = ['/get-started/' => '/contact/'];

if (isset($aliases[$path])) {
    redirect($aliases[$path], 301);
}

$params = [];
if (isset($routes[$path])) {
    $view = $routes[$path];
} elseif (preg_match('#^/case-studies/([a-z0-9-]+)/$#', $path, $m)) {
    $view = 'case-study';
    $params['slug'] = $m[1];
} else {
    $view = '404';
}

render_page($view, $params);

/**
 * Render a page view inside the main layout.
 * A view sets $page (SEO + layout options) and echoes its body.
 */
function render_page(string $view, array $params = []): void
{
    send_security_headers();
    $page = ['key' => $view, 'body_class' => 'page-' . $view];
    ob_start();
    $status = (static function (string $__view, array $params, array &$page) {
        return include root_path('pages/' . $__view . '.php');
    })($view, $params, $page);
    $content = (string)ob_get_clean();

    if ($status === 404) {           // a view can signal "not found"
        render_page('404');
        return;
    }
    if ($view === '404') {
        http_response_code(404);
    }
    require root_path('layouts/main.php');
}
