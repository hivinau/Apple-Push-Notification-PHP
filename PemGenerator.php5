<?php

//ToDo:
//Need 2 files :
//- certificate .p12
//- private key .p12
//Please, take a look at https://www.raywenderlich.com/123862/push-notifications-tutorial
//to create these files before run this script...
//run on terminal : 
// > php PemGenerator.php5

function ReadStdin($prompt) {

    while(!isset($input) || !file_exists($input) || (isset($info) && $info->getExtension() != 'p12')) {

        echo $prompt;

        $input = trim(fgets(STDIN));

        if(isset($input)) {

            if(!file_exists($input)) {

                echo 'File \'' . $input . '\' does not exist.' . PHP_EOL;

            } else {

                $info = new SplFileInfo($input);

                if($info->getExtension() != 'p12') {

                    echo 'File \'' . $input . '\' is not valid a .p12 file.' . PHP_EOL;
                }
            }
        }
    }

    return array('filename' => $info->getFilename(), 'extension' => $info->getExtension());
}

$publicCertificate = ReadStdin('Type .p12 file for certificate : ');

exec('openssl pkcs12 -clcerts -nokeys -out ' . $publicCertificate['extension'] . ' -in '. $publicCertificate['filename'], $output, $result);

if($result !== 0) {

    die('Error occurs, retry!' . PHP_EOL);
}

$privateKey = ReadStdin('Type .p12 file for private key : ');

exec('openssl pkcs12 -nocerts -out ' . $privateKey['extension'] . ' -in '. $privateKey['filename'], $output, $result);

if($result !== 0) {

    die('Error occurs, retry!' . PHP_EOL);
}

echo 'Type filename for final PEM : ';

$apnsPEM = trim(fgets(STDIN)) . '.pem';

exec('cat ' . $publicCertificate['extension'] . ' ' . $privateKey['extension'] . '> ' . $apnsPEM, $output, $result);

if($result !== 0) {

    die('Error occurs, retry!' . PHP_EOL);
}

$info = new SplFileInfo($apnsPEM);

$path = strlen($info->getPath()) > 0 ? $info->getPath() : '.';

echo '\'' . $apnsPEM . '\' is registered at path : \'' . $path . '\'' . PHP_EOL;
