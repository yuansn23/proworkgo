<?php
// API方式 - 根据link_id获取redirect_url
// 使用方式：
// 1. GET: api_redirect.php?link_id=1
// 2. POST: {link_id: 1}
// 3. 自动跳转: api_redirect.php?link_id=1&auto=1

header('Content-Type: application/json; charset=utf-8');

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'duanlian');
define('DB_USER', 'duanlian');
define('DB_PASS', 'baGMCSL6PEztxmrR');

// 获取link_id参数
$link_id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['link_id'])) {
    $link_id = intval($_GET['link_id']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (isset($data['link_id'])) {
        $link_id = intval($data['link_id']);
    }
}

// 检查是否自动跳转
$auto_redirect = isset($_GET['auto']) && $_GET['auto'] == '1';

if (empty($link_id) || $link_id <= 0) {
    if ($auto_redirect) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>错误</title></head><body><h1>错误：缺少link_id参数或参数无效</h1></body></html>';
    } else {
        echo json_encode([
            'success' => false,
            'message' => '缺少link_id参数或参数无效'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

try {
    // 创建数据库连接
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // 查询对应的redirect_url
    $stmt = $conn->prepare("SELECT redirect_url, alias, link_id, landing_domain FROM links WHERE link_id = ? AND status = 1 LIMIT 1");
    $stmt->execute([$link_id]);
    $result = $stmt->fetch();

    if ($result) {
        // 如果设置了自动跳转，直接跳转
        if ($auto_redirect) {
            header('Location: ' . $result['redirect_url']);
            exit;
        }

        // 否则返回JSON
        echo json_encode([
            'success' => true,
            'data' => [
                'redirect_url' => $result['redirect_url'],
                'alias' => $result['alias'],
                'link_id' => $result['link_id'],
                'landing_domain' => $result['landing_domain']
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '未找到该link_id对应的跳转链接，或链接已被禁用'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => '数据库错误：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
