<?php

namespace Eighteen73\Orbit\Dependencies\Sabberworm\CSS\Value;

use Eighteen73\Orbit\Dependencies\Sabberworm\CSS\OutputFormat;

class CalcRuleValueList extends RuleValueList
{
    /**
     * @param int $iLineNo
     */
    public function __construct($iLineNo = 0)
    {
        parent::__construct(',', $iLineNo);
    }

    /**
     * @param OutputFormat|null $oOutputFormat
     *
     * @return string
     */
    public function render($oOutputFormat)
    {
        return $oOutputFormat->implode(' ', $this->aComponents);
    }
}
