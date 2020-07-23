<?php
declare(strict_types=1);

namespace Paseto;

use DOMDocument;

//const WSDL = 'http://homologaws.averba.com.br/20/index.soap?wsdl';
const WSDL = 'http://webserver.averba.com.br/20/index.soap?wsdl';

abstract class BaseATM
{

    private $errors;
    private $request;
    private $response;

    /**
     * Response
     * @var
     */
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
     * @return BaseATM
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     * @return BaseATM
     */
    public function setRequest($request)
    {
        $this->request = $request;
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
     * @return BaseATM
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
     * @return BaseATM
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
     * @return BaseATM
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
     * @return BaseATM
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
     * @return BaseATM
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
     * @return BaseATM
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    protected function removeLatinCharacters($value, $normalize = 'n')
    {
        $from = 'áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ';
        $to = 'aaaaeeiooouucAAAAEEIOOOUUC';

        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);
        $value = strtr($value, $mapping);
        if ($normalize == 'u') {
            $value = strtoupper(strtolower($value));
        }
        if ($normalize == 'l') {
            $value = strtolower($value);
        }

        return $value;
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


    protected function readReturn($tag, $response)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadXML($response);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if (!empty($errors)) {
            $msg = '';
            foreach ($errors as $error) {
                $msg .= $error->message();
            }
            throw new \RuntimeException($msg);
        }
        $reason = $this->checkForFault($dom);
        if ($reason != '') {
            throw new \RuntimeException($reason);
        }
        //converte o xml em uma stdClass
        return $this->xml2Obj($dom, $tag);
    }

    /**
     * Convert DOMDocument in stdClass
     * @param \DOMDocument $dom
     * @param string $tag
     * @return \stdClass
     */
    private function xml2Obj(DOMDocument $dom, $tag)
    {
        $node = $dom->getElementsByTagName($tag)->item(0);
        $newdoc = new DOMDocument('1.0', 'utf-8');
        $newdoc->appendChild($newdoc->importNode($node, true));
        $xml = $newdoc->saveXML();
        unset($newdoc);
        $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
        $xml = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $xml);
        $xml = str_replace('&lt;?xml version="1.0" encoding="UTF-8"?&gt;', '', $xml);
        $xml = str_replace('&lt;?xml version="1.0" encoding="utf-8"?&gt;', '', $xml);

        $xml = str_replace(['&lt;', '&gt;'], ['<', '>'], $xml);
        $docret = new DOMDocument('1.0', 'utf-8');
        $docret->loadXML($xml);
        $ret = $docret->getElementsByTagName($tag);
        if ($ret->length > 0) {
            $xml = $ret->item(1);
            $xml = $docret->saveXML($xml);
        }

        $resp = simplexml_load_string($xml, null, LIBXML_NOCDATA);
        $std = json_encode($resp);
        $std = str_replace('@attributes', 'attributes', $std);
        $std = json_decode($std);
        return $std;
    }

    private function checkForFault(DOMDocument $dom)
    {
        $tagfault = $dom->getElementsByTagName('Fault')->item(0);
        if (empty($tagfault)) {
            return '';
        }
        $tagreason = $tagfault->getElementsByTagName('Reason')->item(0);
        if (!empty($tagreason)) {
            $reason = $tagreason->getElementsByTagName('Text')->item(0)->nodeValue;
            return $reason;
        }
        return 'Houve uma falha na comunicação.';
    }
}
