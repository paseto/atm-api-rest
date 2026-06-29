<?php
declare(strict_types=1);

namespace Paseto;

use DOMDocument;
use DOMElement;

final class OutroDocumentoXmlBuilder
{
    private const MODS_VALIDOS = [
        OutroDocumento::MOD_ROMANEIO,
        OutroDocumento::MOD_RPS,
        OutroDocumento::MOD_CRT,
        OutroDocumento::MOD_MINUTA,
        OutroDocumento::MOD_CONTROLE_EMBARQUE,
        OutroDocumento::MOD_MIC,
        OutroDocumento::MOD_ORDEM_COLETA,
        OutroDocumento::MOD_NFSE,
        OutroDocumento::MOD_CTRC,
    ];

    public static function build(OutroDocumento $documento): string
    {
        self::validate($documento);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;

        $cteProc = $doc->createElement('cteProc');
        $doc->appendChild($cteProc);

        $cte = $doc->createElement('CTe');
        $cteProc->appendChild($cte);

        $infCte = $doc->createElement('infCte');
        $cte->appendChild($infCte);

        self::appendIde($doc, $infCte, $documento);
        self::appendCompl($doc, $infCte, $documento);
        self::appendParte($doc, $infCte, 'emit', 'enderEmit', $documento->getEmit(), false);
        self::appendParte($doc, $infCte, 'rem', 'enderReme', $documento->getRem(), true);
        self::appendParte($doc, $infCte, 'dest', 'enderDest', $documento->getDest(), true);
        self::appendInfCTeNorm($doc, $infCte, $documento);

        return $doc->saveXML() ?: '';
    }

    private static function validate(OutroDocumento $documento): void
    {
        $errors = [];

        if (!in_array($documento->getMod(), self::MODS_VALIDOS, true)) {
            $errors[] = 'mod inválido. Utilize códigos de 91 a 99 conforme o manual AT&M.';
        }

        self::requireField($errors, 'serie', $documento->getSerie());
        self::requireField($errors, 'nCT', $documento->getNCT());
        self::requireField($errors, 'dhEmi', $documento->getDhEmi());

        if ($documento->getDhEmi() !== ''
            && \DateTime::createFromFormat('Y-m-d\TH:i:s', $documento->getDhEmi()) === false) {
            $errors[] = 'dhEmi deve estar no formato AAAA-MM-DDTHH:MM:SS.';
        }

        if (!in_array($documento->getTpAmb(), [
            OutroDocumento::TP_AMB_PRODUCAO,
            OutroDocumento::TP_AMB_HOMOLOGACAO,
        ], true)) {
            $errors[] = 'tpAmb deve ser 1 (Produção) ou 2 (Homologação).';
        }

        if (!in_array($documento->getModal(), [
            OutroDocumento::MODAL_RODOVIARIO,
            OutroDocumento::MODAL_AEREO,
            OutroDocumento::MODAL_AQUAVIARIO,
            OutroDocumento::MODAL_FERROVIARIO,
            OutroDocumento::MODAL_DUTOVIARIO,
        ], true)) {
            $errors[] = 'modal inválido.';
        }

        if (!in_array($documento->getTpServ(), [
            OutroDocumento::TP_SERV_NORMAL,
            OutroDocumento::TP_SERV_SUBCONTRATACAO,
            OutroDocumento::TP_SERV_REDESPACHO,
            OutroDocumento::TP_SERV_REDESPACHO_INTERMEDIARIO,
        ], true)) {
            $errors[] = 'tpServ inválido.';
        }

        self::requireField($errors, 'cMunIni', $documento->getCMunIni());
        self::requireField($errors, 'UFIni', $documento->getUFIni());
        self::requireField($errors, 'cMunFim', $documento->getCMunFim());
        self::requireField($errors, 'UFFim', $documento->getUFFim());

        if (!in_array($documento->getToma(), [
            OutroDocumento::TOMA_REMETENTE,
            OutroDocumento::TOMA_EXPEDIDOR,
            OutroDocumento::TOMA_RECEBEDOR,
            OutroDocumento::TOMA_DESTINATARIO,
        ], true)) {
            $errors[] = 'toma inválido.';
        }

        if ($documento->getEmit() === null) {
            $errors[] = 'emit é obrigatório.';
        }

        if ($documento->getRem() === null) {
            $errors[] = 'rem é obrigatório.';
        }

        if ($documento->getDest() === null) {
            $errors[] = 'dest é obrigatório.';
        }

        self::validateParte($errors, 'emit', $documento->getEmit(), false);
        self::validateParte($errors, 'rem', $documento->getRem(), true);
        self::validateParte($errors, 'dest', $documento->getDest(), true);

        self::requireValorMonetario($errors, 'vCarga', $documento->getVCarga());

        if (!in_array($documento->getRespSeg(), [
            OutroDocumento::RESP_SEG_REMETENTE,
            OutroDocumento::RESP_SEG_DESTINATARIO,
            OutroDocumento::RESP_SEG_EMITENTE,
            OutroDocumento::RESP_SEG_TOMADOR,
        ], true)) {
            $errors[] = 'respSeg inválido.';
        }

        self::requireValorMonetario($errors, 'vCarga (seguro)', $documento->getVCargaSeguro());

        foreach ($documento->getObsCont() as $index => $obsCont) {
            if ($obsCont->xCampo === '' || $obsCont->xTexto === '') {
                $errors[] = sprintf('ObsCont[%d] exige xCampo e xTexto.', $index);
            }
        }

        if ($errors !== []) {
            throw new \InvalidArgumentException(implode(' ', $errors));
        }
    }

    private static function requireField(array &$errors, string $field, string $value): void
    {
        if (trim($value) === '') {
            $errors[] = sprintf('%s é obrigatório.', $field);
        }
    }

    private static function requireValorMonetario(array &$errors, string $field, string $value): void
    {
        if (trim($value) === '') {
            $errors[] = sprintf('%s é obrigatório.', $field);
            return;
        }

        if (!preg_match('/^\d{1,13}(\.\d{1,2})?$/', $value)) {
            $errors[] = sprintf('%s deve ter até 13 inteiros e 2 decimais (ex: 10.00).', $field);
        }
    }

    private static function validateParte(
        array &$errors,
        string $label,
        ?OutroDocumentoParte $parte,
        bool $exigePais
    ): void {
        if ($parte === null) {
            return;
        }

        self::requireField($errors, sprintf('%s.cnpj', $label), $parte->cnpj);
        self::requireField($errors, sprintf('%s.cMun', $label), $parte->cMun);
        self::requireField($errors, sprintf('%s.uf', $label), $parte->uf);

        if ($exigePais && ($parte->cPais === null || trim($parte->cPais) === '')) {
            $errors[] = sprintf('%s.cPais é obrigatório.', $label);
        }
    }

    private static function appendIde(DOMDocument $doc, DOMElement $infCte, OutroDocumento $documento): void
    {
        $ide = $doc->createElement('ide');
        $infCte->appendChild($ide);

        self::appendText($doc, $ide, 'mod', $documento->getMod());
        self::appendText($doc, $ide, 'serie', $documento->getSerie());
        self::appendText($doc, $ide, 'nCT', $documento->getNCT());
        self::appendText($doc, $ide, 'dhEmi', $documento->getDhEmi());
        self::appendText($doc, $ide, 'tpAmb', $documento->getTpAmb());
        self::appendText($doc, $ide, 'tpCTe', $documento->getTpCTe());
        self::appendText($doc, $ide, 'modal', $documento->getModal());
        self::appendText($doc, $ide, 'tpServ', $documento->getTpServ());
        self::appendText($doc, $ide, 'cMunIni', $documento->getCMunIni());
        self::appendText($doc, $ide, 'UFIni', $documento->getUFIni());
        self::appendText($doc, $ide, 'cMunFim', $documento->getCMunFim());
        self::appendText($doc, $ide, 'UFFim', $documento->getUFFim());

        $toma03 = $doc->createElement('toma03');
        $ide->appendChild($toma03);
        self::appendText($doc, $toma03, 'toma', $documento->getToma());
    }

    private static function appendCompl(DOMDocument $doc, DOMElement $infCte, OutroDocumento $documento): void
    {
        $xObs = $documento->getXObs();
        $obsCont = $documento->getObsCont();

        if (($xObs === null || $xObs === '') && $obsCont === []) {
            return;
        }

        $compl = $doc->createElement('compl');
        $infCte->appendChild($compl);

        if ($xObs !== null && $xObs !== '') {
            self::appendText($doc, $compl, 'xObs', $xObs);
        }

        foreach ($obsCont as $item) {
            $obsContEl = $doc->createElement('ObsCont');
            $compl->appendChild($obsContEl);
            self::appendText($doc, $obsContEl, 'xCampo', $item->xCampo);
            self::appendText($doc, $obsContEl, 'xTexto', $item->xTexto);
        }
    }

    private static function appendParte(
        DOMDocument $doc,
        DOMElement $infCte,
        string $tag,
        string $enderTag,
        ?OutroDocumentoParte $parte,
        bool $incluiPais
    ): void {
        if ($parte === null) {
            return;
        }

        $parteEl = $doc->createElement($tag);
        $infCte->appendChild($parteEl);
        self::appendText($doc, $parteEl, 'CNPJ', $parte->cnpj);

        $ender = $doc->createElement($enderTag);
        $parteEl->appendChild($ender);
        self::appendText($doc, $ender, 'cMun', $parte->cMun);
        self::appendText($doc, $ender, 'UF', $parte->uf);

        if ($incluiPais && $parte->cPais !== null) {
            self::appendText($doc, $ender, 'cPais', $parte->cPais);
        }
    }

    private static function appendInfCTeNorm(DOMDocument $doc, DOMElement $infCte, OutroDocumento $documento): void
    {
        $infCTeNorm = $doc->createElement('infCTeNorm');
        $infCte->appendChild($infCTeNorm);

        $infCarga = $doc->createElement('infCarga');
        $infCTeNorm->appendChild($infCarga);
        self::appendText($doc, $infCarga, 'vCarga', self::formatValor($documento->getVCarga()));

        $seg = $doc->createElement('seg');
        $infCTeNorm->appendChild($seg);
        self::appendText($doc, $seg, 'respSeg', $documento->getRespSeg());
        self::appendText($doc, $seg, 'vCarga', self::formatValor($documento->getVCargaSeguro()));
    }

    private static function formatValor(string $valor): string
    {
        return number_format((float) $valor, 2, '.', '');
    }

    private static function appendText(DOMDocument $doc, DOMElement $parent, string $name, string $value): void
    {
        $element = $doc->createElement($name);
        $element->appendChild($doc->createTextNode($value));
        $parent->appendChild($element);
    }
}
