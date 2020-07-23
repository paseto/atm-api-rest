<?php
ini_set('display_errors', 1);
require '../vendor/autoload.php';

echo '<pre>';
$averba = new \Paseto\ATMAverba();
$result = $averba->setUser('WS')
    ->setPassword('?')
    ->setCod('?')
//    ->setXml('43191202633114000102570010000100121191210019-cte.xml')
    ->averbaCTe();

    $response = $averba->getResponse();

    echo '<pre>';
    print_r($response);
    if ($response->object->Erros->Erro->Codigo) {
        echo $response->object->Erros->Erro->Descricao;
    } else {
        echo $response->object->dtRec;
        echo $response->object->cProtocolo;
        echo $response->object->listaMensagem->cStatus;
    }
//}