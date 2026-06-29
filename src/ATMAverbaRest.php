<?php
declare(strict_types=1);

namespace Paseto;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class ATMAverbaRest extends BaseATMRest implements ATMAverbaRestInterface
{
    public const DEFAULT_BASE_URI = 'http://webserver.averba.com.br/rest/';
    public const HOMOLOG_BASE_URI = 'http://homologaws.averba.com.br/rest/';

    private ?string $user = null;
    private ?string $password = null;
    private ?string $codigoATM = null;
    private ?string $token = null;
    private ?string $xml = null;
    private ClientInterface $client;
    private string $baseUri;

    public function __construct(
        ?ClientInterface $client = null,
        string $baseUri = self::DEFAULT_BASE_URI
    ) {
        $this->baseUri = rtrim($baseUri, '/') . '/';
        $this->client = $client ?? new Client(['base_uri' => $this->baseUri]);
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getCodigoATM(): ?string
    {
        return $this->codigoATM;
    }

    public function setCodigoATM(string $codigoATM): static
    {
        $this->codigoATM = $codigoATM;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getXml(): ?string
    {
        return $this->xml;
    }

    public function setXml(string $xml): static
    {
        $this->xml = $xml;
        return $this;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * Valida se usuário, senha e código ATM são aceitos pela API.
     */
    public function validateCredentials(): bool
    {
        $this->setResultStatus(false);

        if (!$this->auth()) {
            return false;
        }

        $this->setResultStatus(true);
        $this->setResultStatusCode(100);
        $this->setResultStatusMessage('Credenciais válidas.');

        return true;
    }

    public function averbaCTe(): bool
    {
        return $this->send('Cte');
    }

    public function averbaMDFe(): bool
    {
        return $this->send('MDFe');
    }

    private function send(string $service): bool
    {
        $this->setResultStatus(false);

        try {
            if (!$this->auth()) {
                return false;
            }

            $xmlPath = $this->getXml();
            if ($xmlPath === null || !is_file($xmlPath)) {
                $this->setErrors('Um arquivo deve ser informado.');
                return false;
            }

            $xml = file_get_contents($xmlPath);
            if ($xml === false || !$this->isValidXml($xml)) {
                $this->setErrors('Arquivo XML inválido.');
                return false;
            }

            $result = $this->client->request('POST', $this->baseUri . $service, [
                'body' => $xml,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/xml',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                ],
            ]);

            $response = json_decode($result->getBody()->getContents());
            if (!is_object($response)) {
                $this->setErrors('Resposta inválida da API.');
                return false;
            }

            $this->setResponse($response);
            $this->applySuccessResponse($response, $service);

            return $this->getResultStatus();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response !== null) {
                $this->applyErrorFromBody($response->getBody()->getContents());
            }
            $this->setResultStatus(false);
            return false;
        } catch (GuzzleException $guzzleException) {
            $this->setErrors('Erro ao executar aplicação. ' . $guzzleException->getMessage());
            return false;
        }
    }

    private function applySuccessResponse(object $response, string $service): void
    {
        if (isset($response->Erros->Erro)) {
            $this->setResultStatus(false);
            $erro = $this->firstItem($response->Erros->Erro);
            $this->setResultStatusCode($erro->Codigo);
            $this->setResultStatusMessage($erro->Descricao);
            return;
        }

        $this->setResultStatus(true);

        if (isset($response->Infos->Info)) {
            $info = $this->firstItem($response->Infos->Info);
            $this->setResultStatusCode($info->Codigo);
            $this->setResultStatusMessage($info->Descricao);
        } else {
            $this->setResultStatusCode(100);
            $this->setResultStatusMessage('Documento Averbado');
        }

        if ($service === 'MDFe') {
            $this->setResultProtocol($response->Declarado->Protocolo ?? null);
            $this->setResultProtocolDate($response->Declarado->dhChancela ?? null);
        } else {
            $this->setResultProtocol($response->Averbado->Protocolo ?? null);
            $this->setResultProtocolDate($response->Averbado->dhAverbacao ?? null);
        }
    }

    private function auth(): bool
    {
        if ($this->getUser() === null || $this->getUser() === ''
            || $this->getPassword() === null || $this->getPassword() === ''
            || $this->getCodigoATM() === null || $this->getCodigoATM() === '') {
            $this->setErrors('Todos os parâmetros são obrigatórios.');
            return false;
        }

        try {
            $res = $this->client->request('POST', $this->baseUri . 'Auth', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                ],
                'body' => json_encode([
                    'usuario' => $this->getUser(),
                    'senha' => $this->getPassword(),
                    'codigoatm' => $this->getCodigoATM(),
                ], JSON_THROW_ON_ERROR),
            ]);

            $token = json_decode($res->getBody()->getContents());
            if (!is_object($token) || !isset($token->Bearer)) {
                $this->setErrors('Resposta de autenticação inválida.');
                return false;
            }

            $this->setToken((string) $token->Bearer);
            return true;
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
            if ($response !== null) {
                $this->applyErrorFromBody($response->getBody()->getContents());
            }
            return false;
        } catch (GuzzleException $guzzleException) {
            $this->setErrors('Erro ao executar aplicação. ' . $guzzleException->getMessage());
            return false;
        } catch (\JsonException) {
            $this->setErrors('Erro ao montar requisição de autenticação.');
            return false;
        }
    }
}
