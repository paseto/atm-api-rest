<?php
declare(strict_types=1);

namespace Paseto;

final class OutroDocumentoObsCont
{
    public function __construct(
        public readonly string $xCampo,
        public readonly string $xTexto,
    ) {
    }
}
