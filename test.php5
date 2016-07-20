<?php

define('DEVICE_TOKEN', '351e186497b20325eeda0762420dd522a1f2ab9dcd495e4fbd2aec673f86b2f0');
define('PEM_FILE_PATH', 'apns-prod.pem');
define('APPLE_PUSH_URL', 'ssl://gateway.sandbox.push.apple.com:2195');

$alertMessage = 'Notification message from \'' . gethostname() . '\'';
$badgeCount = 1;

$body = array();
$body['aps'] = array();
$body['aps']['alert'] = $alertMessage;
$body['aps']['badge'] = $badgeCount;

$context = stream_context_create();

if($context) {

    $socket = null;

    try {

        if(stream_context_set_option($context, 'ssl', 'local_cert', PEM_FILE_PATH)) {

            set_error_handler(function() {
                return true;
            }, E_ALL);

            $socket = stream_socket_client(APPLE_PUSH_URL, $err, $errstr, ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT, $context);

            restore_error_handler();

            if(!$socket) {

                throw  new Exception('Failed to connect : error code' . $err);
            }

            $payload = json_encode($body);

            $message = chr(0);
            $message .= pack('n', 32) ;
            $message .= pack('H*', DEVICE_TOKEN);
            $message .= pack('n', strlen($payload));
            $message .= $payload;

            $status = fwrite($socket, $message);

            if ($status === false) {

                throw  new Exception('Failed to send message.');

            } else {

                echo 'Message sent.' . PHP_EOL;
            }
        }

    } catch (Exception $ex) {

        die($ex->getMessage() . PHP_EOL);

    } finally {

        if($socket) {

            fclose($socket);
        }
    }
} else {

    die('Failed to create context for stream.' . PHP_EOL);
}
