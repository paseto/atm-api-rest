<?php
declare(strict_types=1);

namespace Paseto;

final class OutroDocumentoParte
{
    public function __construct(
        public readonly string $cnpj,
        public readonly string $cMun,
        public readonly string $uf,
        public readonly ?string $cPais = null,
    ) {
    }
}
