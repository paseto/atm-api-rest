<?php
declare(strict_types=1);

namespace Paseto;

interface ATMAverbaRestInterface
{
    public function validateCredentials(): bool;

    public function averbaCTe(): bool;

    public function cancelaCTe(): bool;

    public function averbaMDFe(): bool;

    public function encerraMDFe(): bool;

    public function cancelaMDFe(): bool;

    public function incluiCondutorMDFe(): bool;

    public function averbaOutroDocumento(OutroDocumento $documento): bool;
}
