<?php
/**
 * 通过API获取跳转链接
 * @param int $link_id 链接ID
 * @return string 返回redirect_url，如果失败返回空字符串
 */
function getRedirectUrl($link_id) {
    // 自动检测协议（http 或 https）
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $api_url = $protocol . $_SERVER['HTTP_HOST'] . '/api_redirect.php?link_id=' . $link_id;

    // 使用curl调用API
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['success']) && $data['success'] && !empty($data['data']['redirect_url'])) {
                return $data['data']['redirect_url'];
            }
        }
    }

    return '';
}
?>
