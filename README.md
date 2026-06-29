# ATM API REST
Integração Web Service 2.0 - REST

## How To
Verificar pasta `examples` com um exemplo prático.

### Ambiente
Por padrão, a biblioteca usa produção. Para homologação:

```php
$averba = new \Paseto\ATMAverbaRest(null, \Paseto\ATMAverbaRest::HOMOLOG_BASE_URI);
```

### Validar credenciais
```php
if ($averba->validateCredentials()) {
    echo $averba->getResultStatusMessage();
} else {
    print_r($averba->getErrors());
}
```

## Return
Método `getResultStatus()` - (bool) Status da solicitação

#### Case return True or False

Método `getResultStatusCode()` - (int) Código da resposta da solicitação webservice (pode haver mais de uma, utilizar método `getResponse()` para exibir todas)

Método `getResultStatusMessage()` - (string) Descrição da resposta da solicitação webservice

Método `getResponse()` - (object) Todos os detalhes do retorno

#### True Only

Método `getResultProtocol()` - (string) Número do protocolo da averbação

Método `getResultProtocolDate()` - (string datetime Y-m-dTH:i:s) Data e hora do protocolo da averbação
