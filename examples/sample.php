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

// $averba->setXml('./43240107715207000191570010000389171240119830-cte.xml');
// if ($averba->averbaCTe()) {
//     print_r($averba->getResponse());
//     echo $averba->getResultStatusMessage();
// } else {
//     print_r($averba->getErrors());
// }

// $averba->setXml('./cte-cancelado.xml');
// if ($averba->cancelaCTe()) {
//     print_r($averba->getResponse());
// } else {
//     print_r($averba->getErrors());
// }

// $averba->setXml('./43200707000207000191580010000027841200748722-mdfe.xml');
// if ($averba->averbaMDFe()) {
//     print_r($averba->getResponse());
// } else {
//     print_r($averba->getErrors());
// }

// $averba->setXml('./mdfe-encerrado.xml');
// if ($averba->encerraMDFe()) {
//     print_r($averba->getResponse());
// } else {
//     print_r($averba->getErrors());
// }

// $averba->setXml('./mdfe-cancelado.xml');
// if ($averba->cancelaMDFe()) {
//     print_r($averba->getResponse());
// } else {
//     print_r($averba->getErrors());
// }

// $documento = (new \Paseto\OutroDocumento())
//     ->setMod(\Paseto\OutroDocumento::MOD_CTRC)
//     ->setSerie('1')
//     ->setNCT('123456')
//     ->setDhEmi('2024-06-15T10:30:00')
//     ->setCMunIni('3550308')->setUFIni('SP')
//     ->setCMunFim('3304557')->setUFFim('RJ')
//     ->setEmit(new \Paseto\OutroDocumentoParte('07715207000191', '3550308', 'SP'))
//     ->setRem(new \Paseto\OutroDocumentoParte('07715207000191', '3550308', 'SP', '1058'))
//     ->setDest(new \Paseto\OutroDocumentoParte('00000000000191', '3304557', 'RJ', '1058'))
//     ->setVCarga('1500.00')
//     ->setVCargaSeguro('1500.00');
// if ($averba->averbaOutroDocumento($documento)) {
//     print_r($averba->getResponse());
// } else {
//     print_r($averba->getErrors());
// }
