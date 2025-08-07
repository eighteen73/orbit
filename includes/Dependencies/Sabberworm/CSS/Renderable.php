<?php

declare(strict_types=1);

namespace Eighteen73\Orbit\Dependencies\Sabberworm\CSS;

interface Renderable
{
    public function render(OutputFormat $outputFormat): string;
}
