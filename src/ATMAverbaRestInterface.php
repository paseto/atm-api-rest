<?php
declare(strict_types=1);

namespace Paseto;

interface ATMAverbaRestInterface
{
    public function validateCredentials(): bool;

    public function averbaCTe(): bool;

    public function averbaMDFe(): bool;
}
