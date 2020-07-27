# ATM API REST
Integração Web Service 2.0 - REST

# How To
Verificar pasta examples com um exemplo prático

## Return
Método getResultStatus() - (bool) Status da solicitação

#### Case return True or False
 
Método getStatusCode() - (int) Código da resposta da solicitação webservice (pode haver mais de uma, utilizar métogo getResponse() para exibir todas)
 
Método getStatusMessage() - (string) Descrição da resposta da solicitação webservice 

Método getResponse() - (array) Todos os detalhes do retorno 

#### True Only 

Método getResultProtocol() - (string) Número do protocolo da averbação

Método getResultProtocolDate() - (datetime Y-m-dTH:i:s) Data e hora do protocolo da averbação



