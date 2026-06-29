<?php
declare(strict_types=1);

ini_set('display_errors', '1');
require '../vendor/autoload.php';

$averba = new \Paseto\ATMAverbaRest();
$averba
    ->setUser('SEU_USUARIO')
    ->setPassword('SUA_SENHA')
    ->setCodigoATM('SEU_CODIGO_ATM')
    ->setXml('./43240107715207000191570010000389171240119830-cte.xml');

if ($averba->validateCredentials()) {
    echo $averba->getResultStatusMessage() . PHP_EOL;
} else {
    print_r($averba->getErrors());
}

// if ($averba->averbaCTe()) {
//     print_r($averba->getResponse());
//     echo $averba->getResultStatusMessage();
// } else {
//     print_r($averba->getErrors());
// }

// $averba->setXml('./43200707000207000191580010000027841200748722-mdfe.xml');
// if ($averba->averbaMDFe()) {
//     print_r($averba->getResponse());
// } else {
//     print_r($averba->getErrors());
// }
