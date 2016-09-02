<?php

define('DEVICE_TOKEN', '79e8f6a1e13b06ee6b09f60626e6edcd2c58a5b49de154382fd6832cb89cc0c8'); /* for iphone 6 */
define('PEM_FILE_PATH', 'apns-prod.pem');
define('PEM_PASSPHRASE', 'fdapps');
define('APPLE_PUSH_HOST', 'gateway.push.apple.com'); // gateway.sandbox.push.apple.com  /* FOR DEV */
define('APPLE_PUSH_PORT', 2195);

$alertMessage = 'Notification message from \'' . gethostname() . '\'';
$badgeCount = 1;

$body = array();
$body['aps'] = array();
$body['aps']['alert'] = $alertMessage;
$body['aps']['badge'] = $badgeCount;
$body['aps']['sound'] = 'default';

$context = stream_context_create();

if($context) {

    $socket = null;

    try {

        if(stream_context_set_option($context, 'ssl', 'local_cert', PEM_FILE_PATH) &&
            stream_context_set_option($context, 'ssl', 'passphrase', PEM_PASSPHRASE)) {

            set_error_handler(function() {
                return true;
            }, E_ALL);

            $tryCount = 1;

            $url = sprintf('ssl://%s:%s', APPLE_PUSH_HOST, APPLE_PUSH_PORT);

            while(!($socket = stream_socket_client($url, $err, $errstr, ini_get("default_socket_timeout"), STREAM_CLIENT_CONNECT, $context))) {

                echo 'Failed to connect : (error code ' . $err . '). Try again...' . PHP_EOL;
                sleep(5.0);

                $tryCount++;
            }

            restore_error_handler();

            if($tryCount > 1) {

                echo 'Connection established after '. $tryCount . 'attempts' . PHP_EOL;
            }

            $payload = json_encode($body);

            $message = chr(0);
            $message .= pack('n', 32) ;
            $message .= pack('H*', DEVICE_TOKEN);
            $message .= pack('n', strlen($payload));
            $message .= $payload;

            $status = fwrite($socket, $message, strlen($message));

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

            $isClosed = fclose($socket);

            echo $isClosed ? 'Socket closed.' . PHP_EOL : 'Issue appears while closing socket.' . PHP_EOL;
        }
    }
} else {

    die('Failed to create context for stream.' . PHP_EOL);
}
