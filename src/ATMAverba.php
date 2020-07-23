<?php
declare(strict_types=1);

namespace Paseto;

use Zend\Soap\Client;

class ATMAverba extends BaseATM implements ATMAverbaInterface
{
    private $user;
    private $password;
    private $cod;
    private $xml;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return ATMAverba
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return ATMAverba
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCod()
    {
        return $this->cod;
    }

    /**
     * @param mixed $cod
     * @return ATMAverba
     */
    public function setCod($cod)
    {
        $this->cod = $cod;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * @param mixed $xml
     * @return ATMAverba
     */
    public function setXml($xml)
    {
        $this->xml = $xml;
        return $this;
    }

    public function averbaCTe()
    {
        $std = new \stdClass();
        $std->method = 'averbaCTe';
        $std->arrayParam = 'xmlCTe';
        $this->send($std);
    }

    public function declaraMDFe()
    {
        $std = new \stdClass();
        $std->method = 'declaraMDFe';
        $std->arrayParam = 'xmlMDFe';
        $this->send($std);
    }

    /**
     * @param \stdClass $stdClass
     * @return bool
     */
    private function send(\stdClass $stdClass)
    {
        $this->setResultStatus(false);
        try {
            $client = new Client(WSDL, ["soap_version" => SOAP_1_1]);
            if (!is_file($this->getXml())) {
                $this->setErrors('Arquivo não encontrado.');
                return false;
            }
            $xml = file_get_contents($this->getXml());
            if ($this->isValidXml($xml) === false) {
                $this->setErrors('Arquivo XML inválido.');
                return false;
            }
            if (empty($this->getUser()) || empty($this->getPassword()) || empty($this->getCod())) {
                $this->setErrors('Todos os parâmetros são obrigatórios.');
                return false;
            }

            $params = [
                'usuario' => $this->getUser(),
                'senha' => $this->getPassword(),
                'codatm' => $this->getCod(),
                $stdClass->arrayParam => $xml,
            ];

            $client->call($stdClass->method, $params);
            $request = $client->getLastRequest();
            $response = $client->getLastResponse();

            $std = new \stdClass();
            $std->request = $request;
            $std->response = $response;
            $std->object = $this->readReturn('Response', $response);
            $this->setResponse($std);

            //Build standard response
            $this->setMethod($stdClass->method);
            if (isset($std->object->Erros->Erro)) {
                $this->setResultStatus(false);
                $this->setResultStatusCode($std->object->Erros->Erro->Codigo);
                $this->setResultStatusMessage($std->object->Erros->Erro->Descricao);
            } else {
                $this->setResultStatus(true);
                if (isset($std->object->Infos->Info)) {
                    $this->setResultStatusCode($std->object->Infos->Info->Codigo);
                    $this->setResultStatusMessage($std->object->Infos->Info->Descricao);
                } else {
                    $this->setResultStatusCode(100);
                    $this->setResultStatusMessage('Documento Averbado');
                }
                if ($stdClass->method == 'declaraMDFe') {
                    $this->setResultProtocol($std->object->Declarado->Protocolo);
                    $this->setResultProtocolDate($std->object->Declarado->dhChancela);
                } else {
                    $this->setResultProtocol($std->object->Averbado->Protocolo);
                    $this->setResultProtocolDate($std->object->Averbado->dhAverbacao);
                }
            }
            return true;
        } catch (\Exception $e) {
            $this->setErrors($e->getMessage());
            return false;
        }
    }

}
