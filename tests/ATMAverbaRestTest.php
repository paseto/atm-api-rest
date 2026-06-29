<?php
declare(strict_types=1);

namespace Paseto\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Paseto\ATMAverbaRest;
use PHPUnit\Framework\TestCase;

class ATMAverbaRestTest extends TestCase
{
    private function createClient(MockHandler $mock): ATMAverbaRest
    {
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return new ATMAverbaRest($client, 'http://example.test/rest/');
    }

    public function testValidateCredentialsReturnsTrueOnSuccess(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '{"Bearer":"token-abc"}'),
        ]);

        $averba = $this->createClient($mock);
        $averba
            ->setUser('user')
            ->setPassword('pass')
            ->setCodigoATM('123');

        $this->assertTrue($averba->validateCredentials());
        $this->assertSame('token-abc', $averba->getToken());
        $this->assertTrue($averba->getResultStatus());
        $this->assertSame('Credenciais válidas.', $averba->getResultStatusMessage());
    }

    public function testValidateCredentialsReturnsFalseWhenParametersMissing(): void
    {
        $mock = new MockHandler([]);
        $averba = $this->createClient($mock);

        $this->assertFalse($averba->validateCredentials());
        $this->assertSame('Todos os parâmetros são obrigatórios.', $averba->getErrors());
    }

    public function testValidateCredentialsHandlesApiError(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([
                'Erros' => [
                    'Erro' => [
                        ['Codigo' => 401, 'Descricao' => 'Credenciais inválidas'],
                    ],
                ],
            ])),
        ]);

        $averba = $this->createClient($mock);
        $averba
            ->setUser('user')
            ->setPassword('wrong')
            ->setCodigoATM('123');

        $this->assertFalse($averba->validateCredentials());
        $this->assertSame(401, $averba->getResultStatusCode());
        $this->assertSame('Credenciais inválidas', $averba->getResultStatusMessage());
    }

    public function testValidateCredentialsHandlesSingleErrorObject(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([
                'Erros' => [
                    'Erro' => ['Codigo' => 99, 'Descricao' => 'Erro único'],
                ],
            ])),
        ]);

        $averba = $this->createClient($mock);
        $averba
            ->setUser('user')
            ->setPassword('wrong')
            ->setCodigoATM('123');

        $this->assertFalse($averba->validateCredentials());
        $this->assertSame(99, $averba->getResultStatusCode());
        $this->assertSame('Erro único', $averba->getResultStatusMessage());
    }

    public function testAverbaCTeSuccess(): void
    {
        $xmlFile = tempnam(sys_get_temp_dir(), 'cte');
        file_put_contents($xmlFile, '<?xml version="1.0"?><cte/>');

        $mock = new MockHandler([
            new Response(200, [], '{"Bearer":"token-abc"}'),
            new Response(200, [], json_encode([
                'Averbado' => [
                    'Protocolo' => '12345',
                    'dhAverbacao' => '2024-01-15T10:00:00',
                ],
            ])),
        ]);

        $averba = $this->createClient($mock);
        $averba
            ->setUser('user')
            ->setPassword('pass')
            ->setCodigoATM('123')
            ->setXml($xmlFile);

        $this->assertTrue($averba->averbaCTe());
        $this->assertTrue($averba->getResultStatus());
        $this->assertSame('12345', $averba->getResultProtocol());
        $this->assertSame('2024-01-15T10:00:00', $averba->getResultProtocolDate());

        unlink($xmlFile);
    }

    public function testAverbaCTeFailsWithoutAuth(): void
    {
        $xmlFile = tempnam(sys_get_temp_dir(), 'cte');
        file_put_contents($xmlFile, '<?xml version="1.0"?><cte/>');

        $mock = new MockHandler([
            new Response(401, [], json_encode([
                'Erros' => [
                    'Erro' => [['Codigo' => 401, 'Descricao' => 'Não autorizado']],
                ],
            ])),
        ]);

        $averba = $this->createClient($mock);
        $averba
            ->setUser('user')
            ->setPassword('pass')
            ->setCodigoATM('123')
            ->setXml($xmlFile);

        $this->assertFalse($averba->averbaCTe());

        unlink($xmlFile);
    }

    public function testAuthUsesJsonEncodeForSpecialCharacters(): void
    {
        $mock = new MockHandler([
            new Response(200, [], '{"Bearer":"token"}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $averba = new ATMAverbaRest($client, 'http://example.test/rest/');
        $averba
            ->setUser('user"test')
            ->setPassword('pass\\word')
            ->setCodigoATM('123');

        $this->assertTrue($averba->validateCredentials());

        $request = $mock->getLastRequest();
        $this->assertNotNull($request);
        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame('user"test', $body['usuario']);
        $this->assertSame('pass\\word', $body['senha']);
    }
}
