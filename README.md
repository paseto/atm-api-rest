# ATM API REST
Integração Web Service 2.0 - REST



[![Latest Version on Packagist](https://img.shields.io/packagist/v/paseto/atm-api-rest.svg?style=flat-square)](https://packagist.org/packages/paseto/atm-api-rest)
[![Total Downloads](https://img.shields.io/packagist/dt/paseto/atm-api-rest.svg?style=flat-square)](https://packagist.org/packages/paseto/atm-api-rest)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

## How To
Verificar pasta `examples` com um exemplo prático.

### Ambiente
Por padrão, a biblioteca usa produção em `https://webserver.averba.com.br/rest/`.

Para homologação, use o construtor com `HOMOLOG_BASE_URI` e credenciais/código ATM de teste fornecidos pelo suporte AT&M:

```php
$averba = new \Paseto\ATMAverbaRest(null, \Paseto\ATMAverbaRest::HOMOLOG_BASE_URI);
```

Produção e homologação utilizam o mesmo endereço do webservice; a distinção é feita pelas credenciais. O manual v1.1 (§27.9) cita `homologaws.averba.com.br`, porém a AT&M orienta usar `webserver.averba.com.br` para ambos os ambientes.

### Validar credenciais
```php
if ($averba->validateCredentials()) {
    echo $averba->getResultStatusMessage();
} else {
    print_r($averba->getErrors());
}
```

### Métodos disponíveis
| Método | Endpoint | XML esperado |
|--------|----------|----------------|
| `averbaCTe()` | `/rest/Cte` | CT-e protocolado |
| `cancelaCTe()` | `/rest/Cte` | CT-e cancelado protocolado |
| `averbaMDFe()` | `/rest/MDFe` | MDF-e protocolado |
| `encerraMDFe()` | `/rest/MDFe` | MDF-e encerrado |
| `cancelaMDFe()` | `/rest/MDFe` | MDF-e cancelado |
| `incluiCondutorMDFe()` | `/rest/MDFe` | MDF-e inclusão de condutor |
| `averbaOutroDocumento()` | `/rest/Cte` | Monta XML layout AT&M (seção 14) |

Todos os métodos de documento com arquivo exigem `setXml()` antes da chamada, exceto `averbaOutroDocumento()`, que recebe um `OutroDocumento`.

### Outros documentos (layout AT&M)
```php
$documento = (new \Paseto\OutroDocumento())
    ->setMod(\Paseto\OutroDocumento::MOD_CTRC) // 99=CTRC, 98=NFSe, 97=Ordem Coleta...
    ->setSerie('1')
    ->setNCT('123456')
    ->setDhEmi('2024-06-15T10:30:00')
    ->setCMunIni('3550308')->setUFIni('SP')
    ->setCMunFim('3304557')->setUFFim('RJ')
    ->setEmit(new \Paseto\OutroDocumentoParte('07715207000191', '3550308', 'SP'))
    ->setRem(new \Paseto\OutroDocumentoParte('07715207000191', '3550308', 'SP', '1058'))
    ->setDest(new \Paseto\OutroDocumentoParte('00000000000191', '3304557', 'RJ', '1058'))
    ->setVCarga('1500.00')
    ->setVCargaSeguro('1500.00');

if ($averba->averbaOutroDocumento($documento)) {
    echo $averba->getResultProtocol();
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
