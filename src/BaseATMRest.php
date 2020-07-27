<?php
declare(strict_types=1);

namespace Paseto;

use DOMDocument;

//const URI = 'http://homologaws.averba.com.br/rest/';
const URI = 'http://webserver.averba.com.br/rest/';

abstract class BaseATMRest
{

    private $errors;
    private $response;
    private $resultStatus;
    private $resultStatusCode;
    private $resultStatusMessage;
    private $resultProtocol;
    private $resultProtocolDate;
    private $method;

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     */
    public function setErrors($errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     * @return BaseATMRest
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return bool
     */
    public function getResultStatus(): bool
    {
        return $this->resultStatus;
    }

    /**
     * @param bool $resultStatus
     * @return BaseATMRest
     */
    public function setResultStatus(bool $resultStatus)
    {
        $this->resultStatus = $resultStatus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultStatusCode()
    {
        return $this->resultStatusCode;
    }

    /**
     * @param mixed $resultStatusCode
     * @return BaseATMRest
     */
    public function setResultStatusCode($resultStatusCode)
    {
        $this->resultStatusCode = $resultStatusCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultStatusMessage()
    {
        return $this->resultStatusMessage;
    }

    /**
     * @param mixed $resultStatusMessage
     * @return BaseATMRest
     */
    public function setResultStatusMessage($resultStatusMessage)
    {
        $this->resultStatusMessage = $resultStatusMessage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultProtocol()
    {
        return $this->resultProtocol;
    }

    /**
     * @param mixed $resultProtocol
     * @return BaseATMRest
     */
    public function setResultProtocol($resultProtocol)
    {
        $this->resultProtocol = $resultProtocol;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getResultProtocolDate()
    {
        return $this->resultProtocolDate;
    }

    /**
     * @param \DateTime $resultProtocolDate
     * @return BaseATMRest
     */
    public function setResultProtocolDate($resultProtocolDate)
    {
        $this->resultProtocolDate = $resultProtocolDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod():string 
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return BaseATMRest
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    protected function isValidXml($xml)
    {
        $content = trim($xml);
        if (empty($content)) {
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
}
