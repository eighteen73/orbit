<?php

declare(strict_types=1);

namespace Eighteen73\Orbit\Dependencies\Sabberworm\CSS\Value;

use Eighteen73\Orbit\Dependencies\Sabberworm\CSS\OutputFormat;

class CalcRuleValueList extends RuleValueList
{
    /**
     * @param int<1, max>|null $lineNumber
     */
    public function __construct(?int $lineNumber = null)
    {
        parent::__construct(',', $lineNumber);
    }

    public function render(OutputFormat $outputFormat): string
    {
        return $outputFormat->getFormatter()->implode(' ', $this->components);
    }
}
