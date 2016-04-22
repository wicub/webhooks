<?php

/**
 * sh 文件
 */
$hookfile = '.hooks/webhook-push.sh';

/**
 * log文件
 */
$logfile = '.hooks/webhook-push.log';

/** Can also be an array of refs:
 *
 *  $ref = array('refs/heads/master', 'refs/heads/develop');
 */
$ref = 'refs/heads/master';

/**
 * password,根据不同的git服务商来设定
 */
//$password = '';

/**
 * 验证部分
 */
if (isset($password)) {
    if (empty($_REQUEST['p'])) {
        log_append('Missing hook password');
        die();
    }

    if ($_REQUEST['p'] !== $password) {
        log_append('Invalid hook password');
        die();
    }
}

//获取内容，不同git服务商的输入内容大同小异，均可以通过该方式获取
$input = file_get_contents("php://input");
$json = json_decode($input, true);

if (!is_array($json) || empty($json['ref'])) {
    log_append('Invalid push event data');
    die();
}

if (isset($ref)) {
    $_refs = (array)$ref;

    if ($ref !== '*' && !in_array($json['ref'], $_refs)) {
        log_append('Ignoring ref ' . $json['ref']);
        die();
    }
}

log_append('Launching shell hook script...');
exec_command('sh ' . $hookfile);
log_append('Shell hook script finished');

function log_append($message) {
    global $logfile;

    $date = date('Y-m-d H:i:s');
    $pre = $date . ' (' . $_SERVER['REMOTE_ADDR'] . '): ';

    file_put_contents($logfile, $pre . $message . "\n", FILE_APPEND);
}

function exec_command($command) {
    $output = array();

    exec($command, $output);

    foreach ($output as $line) {
        log_append('SHELL: ' . $line);
    }
}
