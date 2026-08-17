<?php

const ADMIN_USERNAME_DIGEST = '91f672e6cd62995a0a3b873311e102ee6a4ad0ba389f7980b3f8cad93b0adb8a';
const ADMIN_PASSWORD_HASH = '$2y$12$ezqbxGPCFf1v8gUKVL0H3u7xLINq9OuxQ4i9nsOc45gR02QDjrIb2';
const STATE_VERSION = 1;
const MAX_GROUPS = 50;
const MAX_SITES_PER_GROUP = 200;
const MAX_ACTIVITY_ITEMS = 20;
const RELEASE_LIFETIME = 604800;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Strict');
session_name('INTOSHARPSESSID');
session_set_cookie_params(
    0,
    '/',
    '',
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    true
);
session_start();

function respond($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_body()
{
    if ((isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '') !== 'POST') {
        respond(['ok' => false, 'error' => '허용되지 않은 요청입니다.'], 405);
    }
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : '';
    if (strlen($contentLength) > 0 && (int) $contentLength > 1048576) {
        respond(['ok' => false, 'error' => '요청 크기가 너무 큽니다.'], 413);
    }
    $decoded = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($decoded)) {
        respond(['ok' => false, 'error' => '요청 형식이 올바르지 않습니다.'], 400);
    }
    return $decoded;
}

function data_path()
{
    $directory = __DIR__ . '/.intosharp-data';
    if (!is_dir($directory)) {
        if (!mkdir($directory, 0700, true) && !is_dir($directory)) {
            respond(['ok' => false, 'error' => '저장 공간을 만들 수 없습니다.'], 500);
        }
        @file_put_contents($directory . '/.htaccess', "Require all denied\nDeny from all\n");
        @file_put_contents($directory . '/index.html', '');
    }
    return $directory . '/site-state.json';
}

function clean_text($value, $length)
{
    if (!is_string($value)) {
        return '';
    }
    $cleaned = preg_replace('/\s+/u', ' ', $value);
    $value = trim($cleaned !== null ? $cleaned : '');
    return function_exists('mb_substr') ? mb_substr($value, 0, $length, 'UTF-8') : substr($value, 0, $length);
}

function safe_equals($known, $supplied)
{
    if (function_exists('hash_equals')) {
        return hash_equals($known, $supplied);
    }
    if (!is_string($known) || !is_string($supplied) || strlen($known) !== strlen($supplied)) {
        return false;
    }
    $difference = 0;
    for ($index = 0, $length = strlen($known); $index < $length; $index++) {
        $difference |= ord($known[$index]) ^ ord($supplied[$index]);
    }
    return $difference === 0;
}

function require_same_origin()
{
    if ((isset($_SERVER['HTTP_X_INTOSHARP_REQUEST']) ? $_SERVER['HTTP_X_INTOSHARP_REQUEST'] : '') !== '1') {
        respond(['ok' => false, 'error' => '요청 출처를 확인할 수 없습니다.'], 403);
    }
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $hostValue = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $cleanHost = preg_replace('/:\d+$/', '', $hostValue);
    $host = strtolower($cleanHost !== null ? $cleanHost : '');
    if ($origin !== '') {
        $originHost = strtolower((string) parse_url($origin, PHP_URL_HOST));
        if ($originHost === '' || !safe_equals($host, $originHost)) {
            respond(['ok' => false, 'error' => '다른 사이트에서 보낸 요청은 허용되지 않습니다.'], 403);
        }
    }
}

function clean_url($value)
{
    $value = clean_text($value, 2048);
    if ($value === '' || filter_var($value, FILTER_VALIDATE_URL) === false) {
        return '';
    }
    $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $value : '';
}

function random_token($bytes)
{
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($bytes));
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($bytes));
    }
    return sha1(uniqid('', true) . mt_rand());
}

function clean_id($value, $prefix)
{
    $value = is_string($value) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $value) : '';
    return $value !== '' ? substr($value, 0, 80) : $prefix . random_token(8);
}

function clean_activity($items, $released)
{
    if (!is_array($items)) {
        return [];
    }
    $now = time();
    $clean = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $name = clean_text(isset($item['name']) ? $item['name'] : '', 100);
        $url = clean_url(isset($item['url']) ? $item['url'] : '');
        $timestampKey = $released ? 'deletedAt' : 'addedAt';
        $timestampValue = isset($item[$timestampKey]) ? $item[$timestampKey] : null;
        $timestamp = is_numeric($timestampValue) ? (int) $timestampValue : $now;
        if ($name === '' || $url === '') {
            continue;
        }
        if ($released && ($timestamp <= 0 || $timestamp < $now - RELEASE_LIFETIME)) {
            continue;
        }
        $clean[] = ['name' => $name, 'url' => $url, $timestampKey => $timestamp];
        if (count($clean) >= MAX_ACTIVITY_ITEMS) {
            break;
        }
    }
    return $clean;
}

function clean_state($state)
{
    if (!is_array($state)) {
        respond(['ok' => false, 'error' => '저장할 데이터가 올바르지 않습니다.'], 422);
    }
    $groups = [];
    $sourceGroups = isset($state['groups']) && is_array($state['groups']) ? $state['groups'] : [];
    foreach (array_slice($sourceGroups, 0, MAX_GROUPS) as $group) {
        if (!is_array($group)) {
            continue;
        }
        $title = clean_text(isset($group['title']) ? $group['title'] : '', 60);
        if ($title === '') {
            continue;
        }
        $sites = [];
        $sourceSites = isset($group['sites']) && is_array($group['sites']) ? $group['sites'] : [];
        foreach (array_slice($sourceSites, 0, MAX_SITES_PER_GROUP) as $site) {
            if (!is_array($site)) {
                continue;
            }
            $name = clean_text(isset($site['name']) ? $site['name'] : '', 100);
            $url = clean_url(isset($site['url']) ? $site['url'] : '');
            if ($name === '' || $url === '') {
                continue;
            }
            $sites[] = [
                'id' => clean_id(isset($site['id']) ? $site['id'] : '', 'site-'),
                'name' => $name,
                'url' => $url,
                'description' => clean_text(isset($site['description']) ? $site['description'] : '', 120),
                'welcome' => !empty($site['welcome']),
            ];
        }
        $groups[] = [
            'id' => clean_id(isset($group['id']) ? $group['id'] : '', 'group-'),
            'title' => $title,
            'tone' => clean_text(isset($group['tone']) ? $group['tone'] : '', 20),
            'sites' => $sites,
        ];
    }
    return [
        'version' => STATE_VERSION,
        'groups' => $groups,
        'recent' => clean_activity(isset($state['recent']) ? $state['recent'] : [], false),
        'released' => clean_activity(isset($state['released']) ? $state['released'] : [], true),
        'updatedAt' => time(),
    ];
}

function read_state()
{
    $path = data_path();
    if (!is_file($path)) {
        return null;
    }
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        respond(['ok' => false, 'error' => '저장 데이터를 읽을 수 없습니다.'], 500);
    }
    flock($handle, LOCK_SH);
    $raw = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    $decoded = json_decode((string) $raw, true);
    return is_array($decoded) ? clean_state($decoded) : null;
}

function write_state($state)
{
    $path = data_path();
    $temporary = $path . '.tmp-' . random_token(6);
    $json = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false || file_put_contents($temporary, $json, LOCK_EX) === false || !rename($temporary, $path)) {
        @unlink($temporary);
        respond(['ok' => false, 'error' => '변경 내용을 저장하지 못했습니다.'], 500);
    }
    @chmod($path, 0600);
}

$body = request_body();
$action = clean_text(isset($body['action']) ? $body['action'] : '', 20);
$authenticated = !empty($_SESSION['intosharp_admin']);

if ($action === 'status') {
    respond(['ok' => true, 'authenticated' => $authenticated]);
}

if ($action === 'load') {
    $state = read_state();
    respond(['ok' => true, 'state' => $state, 'authenticated' => $authenticated]);
}

if ($action === 'login') {
    require_same_origin();
    $attempts = isset($_SESSION['login_attempts']) && is_array($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : [];
    $attempts = array_values(array_filter($attempts, function ($time) {
        return is_int($time) && $time >= time() - 300;
    }));
    if (count($attempts) >= 5) {
        respond(['ok' => false, 'error' => '잠시 후 다시 시도해 주세요.'], 429);
    }
    $username = clean_text(isset($body['username']) ? $body['username'] : '', 64);
    $password = isset($body['password']) && is_string($body['password']) ? $body['password'] : '';
    $usernameMatches = safe_equals(ADMIN_USERNAME_DIGEST, hash('sha256', $username));
    $passwordMatches = strlen($password) <= 256 && password_verify($password, ADMIN_PASSWORD_HASH);
    if (!$usernameMatches || !$passwordMatches) {
        $attempts[] = time();
        $_SESSION['login_attempts'] = $attempts;
        respond(['ok' => false, 'error' => '관리자 인증에 실패했습니다.'], 401);
    }
    session_regenerate_id(true);
    unset($_SESSION['login_attempts']);
    $_SESSION['intosharp_admin'] = true;
    respond(['ok' => true, 'authenticated' => true]);
}

if ($action === 'logout') {
    require_same_origin();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], isset($params['domain']) ? $params['domain'] : '', $params['secure'], $params['httponly']);
    }
    session_destroy();
    respond(['ok' => true, 'authenticated' => false]);
}

if ($action === 'save') {
    require_same_origin();
    if (!$authenticated) {
        respond(['ok' => false, 'error' => '관리자 로그인이 필요합니다.'], 401);
    }
    $state = clean_state(isset($body['state']) ? $body['state'] : null);
    write_state($state);
    respond(['ok' => true, 'state' => $state]);
}

respond(['ok' => false, 'error' => '알 수 없는 요청입니다.'], 400);
