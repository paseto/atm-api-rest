<?php
declare(strict_types=1);

namespace Paseto;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class ATMAverbaRest extends BaseATMRest implements ATMAverbaRestInterface
{
    private $user;
    private $password;
    private $codigoATM;
    private $token;
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
     * @return ATMAverbaRest
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
     * @return ATMAverbaRest
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoATM()
    {
        return $this->codigoATM;
    }

    /**
     * @param mixed $codigoATM
     * @return ATMAverbaRest
     */
    public function setCodigoATM($codigoATM)
    {
        $this->codigoATM = $codigoATM;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     * @return ATMAverbaRest
     */
    public function setToken($token)
    {
        $this->token = $token;
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
     * @return ATMAverbaRest
     */
    public function setXml($xml)
    {
        $this->xml = $xml;
        return $this;
    }

    /**
     * Averba CT-e
     *
     * @return bool
     */
    public function averbaCTe()
    {
        return $this->send('Cte');
    }

    /**
     * @return bool
     */
    public function averbaMDFe()
    {
        return $this->send('MDFe');
    }

    /**
     * Consume service
     * @param string $service
     * @return bool
     */
    private function send(string $service)
    {
        $this->setResultStatus(false);
        $client = new Client();
        try {
            $this->auth();
            if (!$this->getXml() || !is_file($this->getXml())) {
                $this->setErrors('Um arquivo deve ser informado.');
                return false;
            }
            $xml = file_get_contents($this->getXml());
            if ($this->isValidXml($xml) === false) {
                $this->setErrors('Arquivo XML inválido.');
                return false;
            }

            $result = $client->request('POST', URI . $service, [
                'body' => $xml,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/xml',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                ]
            ]);

            if (!is_file($this->getXml())) {
                $this->setErrors('Arquivo não encontrado.');
                return false;
            }
            $xml = file_get_contents($this->getXml());
            if ($this->isValidXml($xml) === false) {
                $this->setErrors('Arquivo XML inválido.');
                return false;
            }

            $response = json_decode($result->getBody()->getContents());

            //All details
            $this->setResponse($response);

            //Build standard response
            if (isset($response->Erros)) {
                $this->setResultStatus(false);
                $this->setResultStatusCode($response->Erros->Erro->Codigo);
                $this->setResultStatusMessage($response->Erros->Erro->Descricao);
            } else {
                $this->setResultStatus(true);
                if (isset($response->Infos->Info)) {
                    $this->setResultStatusCode($response->Infos->Info[0]->Codigo);
                    $this->setResultStatusMessage($response->Infos->Info[0]->Descricao);
                } else {
                    $this->setResultStatusCode(100);
                    $this->setResultStatusMessage('Documento Averbado');
                }
                if ($service == 'MDFe') {
                    $this->setResultProtocol($response->Declarado->Protocolo);
                    $this->setResultProtocolDate($response->Declarado->dhChancela);
                } else {
                    $this->setResultProtocol($response->Averbado->Protocolo);
                    $this->setResultProtocolDate($response->Averbado->dhAverbacao);
                }
            }
            return true;
        } catch (ClientException $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents());
            $this->setErrors($error);
            $this->setResultStatus(false);
            if (is_array($error->Erros->Erro)) {
                $this->setResultStatusCode($error->Erros->Erro[0]->Codigo);
                $this->setResultStatusMessage($error->Erros->Erro[0]->Descricao);
            }
            return false;
        } catch (GuzzleException $guzzleException){
            $this->setErrors('Erro ao executar aplicação. '.$guzzleException->getMessage());
            return false;
        }
    }

    private function auth()
    {
        $client = new Client();
        if (empty($this->getUser()) || empty($this->getPassword()) || empty($this->getCodigoATM())) {
            $this->setErrors('Todos os parâmetros são obrigatórios.');
            return false;
        }
        try {
            $res = $client->request('POST', URI . 'Auth', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                ],
                'body' => '{
                   "usuario":"' . $this->getUser() . '",
                   "senha":"' . $this->getPassword() . '",
                   "codigoatm":"' . $this->getCodigoATM() . '"
                }'
            ]);
            $content = $res->getBody()->getContents();
            $token = json_decode($content);
            $this->setToken($token->Bearer);
            return true;
        } catch (ClientException $exception) {
            $error = $exception->getResponse()->getBody()->getContents();
            $this->setErrors(json_decode($error));
            return false;
        }
    }

}
