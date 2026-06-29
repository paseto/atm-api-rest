<?php
declare(strict_types=1);

namespace Paseto\Tests;

use DOMDocument;
use Paseto\OutroDocumento;
use Paseto\OutroDocumentoObsCont;
use Paseto\OutroDocumentoParte;
use Paseto\OutroDocumentoXmlBuilder;
use PHPUnit\Framework\TestCase;

class OutroDocumentoXmlBuilderTest extends TestCase
{
    private function createDocumento(): OutroDocumento
    {
        return (new OutroDocumento())
            ->setMod(OutroDocumento::MOD_CTRC)
            ->setSerie('1')
            ->setNCT('123456')
            ->setDhEmi('2024-06-15T10:30:00')
            ->setCMunIni('3550308')
            ->setUFIni('SP')
            ->setCMunFim('3304557')
            ->setUFFim('RJ')
            ->setEmit(new OutroDocumentoParte('07715207000191', '3550308', 'SP'))
            ->setRem(new OutroDocumentoParte('07715207000191', '3550308', 'SP', '1058'))
            ->setDest(new OutroDocumentoParte('00000000000191', '3304557', 'RJ', '1058'))
            ->setVCarga('1500.00')
            ->setVCargaSeguro('1500.00');
    }

    public function testBuildCreatesExpectedStructure(): void
    {
        $documento = $this->createDocumento()
            ->setXObs('Observacao geral')
            ->addObsCont(new OutroDocumentoObsCont('RCTRC', 'RCTRC'));

        $xml = OutroDocumentoXmlBuilder::build($documento);

        $this->assertStringContainsString('<cteProc>', $xml);
        $this->assertStringContainsString('<mod>99</mod>', $xml);
        $this->assertStringContainsString('<nCT>123456</nCT>', $xml);
        $this->assertStringContainsString('<ObsCont>', $xml);
        $this->assertStringContainsString('<xCampo>RCTRC</xCampo>', $xml);
        $this->assertStringNotContainsString('Signature', $xml);
        $this->assertStringNotContainsString('protCte', $xml);

        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($xml));
    }

    public function testBuildFailsWhenRequiredFieldsMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        OutroDocumentoXmlBuilder::build(new OutroDocumento());
    }

    public function testBuildFailsWhenValorMonetarioInvalido(): void
    {
        $documento = $this->createDocumento()->setVCarga('invalido');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('vCarga');

        OutroDocumentoXmlBuilder::build($documento);
    }
}
