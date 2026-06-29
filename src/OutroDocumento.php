<?php
declare(strict_types=1);

namespace Paseto;

final class OutroDocumento
{
    public const MOD_ROMANEIO = '91';
    public const MOD_RPS = '92';
    public const MOD_CRT = '93';
    public const MOD_MINUTA = '94';
    public const MOD_CONTROLE_EMBARQUE = '95';
    public const MOD_MIC = '96';
    public const MOD_ORDEM_COLETA = '97';
    public const MOD_NFSE = '98';
    public const MOD_CTRC = '99';

    public const TP_AMB_PRODUCAO = '1';
    public const TP_AMB_HOMOLOGACAO = '2';

    public const TOMA_REMETENTE = '0';
    public const TOMA_EXPEDIDOR = '1';
    public const TOMA_RECEBEDOR = '2';
    public const TOMA_DESTINATARIO = '3';

    public const MODAL_RODOVIARIO = '1';
    public const MODAL_AEREO = '2';
    public const MODAL_AQUAVIARIO = '3';
    public const MODAL_FERROVIARIO = '4';
    public const MODAL_DUTOVIARIO = '5';

    public const TP_SERV_NORMAL = '0';
    public const TP_SERV_SUBCONTRATACAO = '1';
    public const TP_SERV_REDESPACHO = '2';
    public const TP_SERV_REDESPACHO_INTERMEDIARIO = '3';

    public const TP_CTE_NORMAL = '0';

    public const RESP_SEG_REMETENTE = '0';
    public const RESP_SEG_DESTINATARIO = '3';
    public const RESP_SEG_EMITENTE = '4';
    public const RESP_SEG_TOMADOR = '5';

    private string $mod = self::MOD_CTRC;
    private string $serie = '';
    private string $nCT = '';
    private string $dhEmi = '';
    private string $tpAmb = self::TP_AMB_PRODUCAO;
    private string $tpCTe = self::TP_CTE_NORMAL;
    private string $modal = self::MODAL_RODOVIARIO;
    private string $tpServ = self::TP_SERV_NORMAL;
    private string $cMunIni = '';
    private string $UFIni = '';
    private string $cMunFim = '';
    private string $UFFim = '';
    private string $toma = self::TOMA_REMETENTE;
    private ?string $xObs = null;
    /** @var OutroDocumentoObsCont[] */
    private array $obsCont = [];
    private ?OutroDocumentoParte $emit = null;
    private ?OutroDocumentoParte $rem = null;
    private ?OutroDocumentoParte $dest = null;
    private string $vCarga = '';
    private string $respSeg = self::RESP_SEG_REMETENTE;
    private string $vCargaSeguro = '';

    public function setMod(string $mod): self
    {
        $this->mod = $mod;
        return $this;
    }

    public function getMod(): string
    {
        return $this->mod;
    }

    public function setSerie(string $serie): self
    {
        $this->serie = $serie;
        return $this;
    }

    public function getSerie(): string
    {
        return $this->serie;
    }

    public function setNCT(string $nCT): self
    {
        $this->nCT = $nCT;
        return $this;
    }

    public function getNCT(): string
    {
        return $this->nCT;
    }

    public function setDhEmi(string $dhEmi): self
    {
        $this->dhEmi = $dhEmi;
        return $this;
    }

    public function getDhEmi(): string
    {
        return $this->dhEmi;
    }

    public function setTpAmb(string $tpAmb): self
    {
        $this->tpAmb = $tpAmb;
        return $this;
    }

    public function getTpAmb(): string
    {
        return $this->tpAmb;
    }

    public function setTpCTe(string $tpCTe): self
    {
        $this->tpCTe = $tpCTe;
        return $this;
    }

    public function getTpCTe(): string
    {
        return $this->tpCTe;
    }

    public function setModal(string $modal): self
    {
        $this->modal = $modal;
        return $this;
    }

    public function getModal(): string
    {
        return $this->modal;
    }

    public function setTpServ(string $tpServ): self
    {
        $this->tpServ = $tpServ;
        return $this;
    }

    public function getTpServ(): string
    {
        return $this->tpServ;
    }

    public function setCMunIni(string $cMunIni): self
    {
        $this->cMunIni = $cMunIni;
        return $this;
    }

    public function getCMunIni(): string
    {
        return $this->cMunIni;
    }

    public function setUFIni(string $UFIni): self
    {
        $this->UFIni = $UFIni;
        return $this;
    }

    public function getUFIni(): string
    {
        return $this->UFIni;
    }

    public function setCMunFim(string $cMunFim): self
    {
        $this->cMunFim = $cMunFim;
        return $this;
    }

    public function getCMunFim(): string
    {
        return $this->cMunFim;
    }

    public function setUFFim(string $UFFim): self
    {
        $this->UFFim = $UFFim;
        return $this;
    }

    public function getUFFim(): string
    {
        return $this->UFFim;
    }

    public function setToma(string $toma): self
    {
        $this->toma = $toma;
        return $this;
    }

    public function getToma(): string
    {
        return $this->toma;
    }

    public function setXObs(?string $xObs): self
    {
        $this->xObs = $xObs;
        return $this;
    }

    public function getXObs(): ?string
    {
        return $this->xObs;
    }

    public function addObsCont(OutroDocumentoObsCont $obsCont): self
    {
        $this->obsCont[] = $obsCont;
        return $this;
    }

    /** @return OutroDocumentoObsCont[] */
    public function getObsCont(): array
    {
        return $this->obsCont;
    }

    public function setEmit(OutroDocumentoParte $emit): self
    {
        $this->emit = $emit;
        return $this;
    }

    public function getEmit(): ?OutroDocumentoParte
    {
        return $this->emit;
    }

    public function setRem(OutroDocumentoParte $rem): self
    {
        $this->rem = $rem;
        return $this;
    }

    public function getRem(): ?OutroDocumentoParte
    {
        return $this->rem;
    }

    public function setDest(OutroDocumentoParte $dest): self
    {
        $this->dest = $dest;
        return $this;
    }

    public function getDest(): ?OutroDocumentoParte
    {
        return $this->dest;
    }

    public function setVCarga(string $vCarga): self
    {
        $this->vCarga = $vCarga;
        return $this;
    }

    public function getVCarga(): string
    {
        return $this->vCarga;
    }

    public function setRespSeg(string $respSeg): self
    {
        $this->respSeg = $respSeg;
        return $this;
    }

    public function getRespSeg(): string
    {
        return $this->respSeg;
    }

    public function setVCargaSeguro(string $vCargaSeguro): self
    {
        $this->vCargaSeguro = $vCargaSeguro;
        return $this;
    }

    public function getVCargaSeguro(): string
    {
        return $this->vCargaSeguro;
    }
}
