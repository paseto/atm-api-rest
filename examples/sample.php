<?php
ini_set('display_errors', 1);
require '../vendor/autoload.php';

$averba->setPassword('pass')->setUser('ws')->setCodigoATM('11000000')
    ->setXml('./43200707000207000191580010000027841200748722-cte.xml');
if ($averba->averbaCTe()){
    $t = ($averba->getResponse());
    print_r($t);
    echo $averba->getResultStatusMessage();
}else{
//    echo 'false';
    print_r($averba->getErrors());
}

$averba->setPassword('pass')->setUser('ws')->setCodigoATM('11000000')
    ->setXml('./43200707000207000191580010000027841200748722-mdfe.xml');
if ($averba->averbaMDFe()){
    $t = ($averba->getResponse());
    print_r($t);
}else{
    print_r($averba->getErrors());
}
