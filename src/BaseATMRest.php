<?php
declare(strict_types=1);

namespace Paseto;

abstract class BaseATMRest
{
    private mixed $errors = null;
    private mixed $response = null;
    private bool $resultStatus = false;
    private mixed $resultStatusCode = null;
    private mixed $resultStatusMessage = null;
    private mixed $resultProtocol = null;
    private ?string $resultProtocolDate = null;
    private ?string $method = null;

    public function getErrors(): mixed
    {
        return $this->errors;
    }

    public function setErrors(mixed $errors): void
    {
        $this->errors = $errors;
    }

    public function getResponse(): mixed
    {
        return $this->response;
    }

    public function setResponse(mixed $response): static
    {
        $this->response = $response;
        return $this;
    }

    public function getResultStatus(): bool
    {
        return $this->resultStatus;
    }

    public function setResultStatus(bool $resultStatus): static
    {
        $this->resultStatus = $resultStatus;
        return $this;
    }

    public function getResultStatusCode(): mixed
    {
        return $this->resultStatusCode;
    }

    public function setResultStatusCode(mixed $resultStatusCode): static
    {
        $this->resultStatusCode = $resultStatusCode;
        return $this;
    }

    public function getResultStatusMessage(): mixed
    {
        return $this->resultStatusMessage;
    }

    public function setResultStatusMessage(mixed $resultStatusMessage): static
    {
        $this->resultStatusMessage = $resultStatusMessage;
        return $this;
    }

    public function getResultProtocol(): mixed
    {
        return $this->resultProtocol;
    }

    public function setResultProtocol(mixed $resultProtocol): static
    {
        $this->resultProtocol = $resultProtocol;
        return $this;
    }

    public function getResultProtocolDate(): ?string
    {
        return $this->resultProtocolDate;
    }

    public function setResultProtocolDate(?string $resultProtocolDate): static
    {
        $this->resultProtocolDate = $resultProtocolDate;
        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;
        return $this;
    }

    protected function isValidXml(string $xml): bool
    {
        $content = trim($xml);
        if ($content === '') {
            return false;
        }

        if (stripos($content, '<!DOCTYPE html>') !== false) {
            return false;
        }

        libxml_use_internal_errors(true);
        simplexml_load_string($content);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return empty($errors);
    }

    protected function firstItem(mixed $value): mixed
    {
        return is_array($value) ? $value[0] : $value;
    }

    protected function applyErrorFromBody(string $body): void
    {
        $error = json_decode($body);
        if (!is_object($error)) {
            $this->setErrors('Resposta inválida da API.');
            return;
        }

        $this->setErrors($error);
        if (isset($error->Erros->Erro)) {
            $erro = $this->firstItem($error->Erros->Erro);
            $this->setResultStatusCode($erro->Codigo);
            $this->setResultStatusMessage($erro->Descricao);
        }
    }
}
