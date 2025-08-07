<?php

declare(strict_types=1);

namespace Eighteen73\Orbit\Dependencies\Sabberworm\CSS\Parsing;

use Eighteen73\Orbit\Dependencies\Sabberworm\CSS\Position\Position;
use Eighteen73\Orbit\Dependencies\Sabberworm\CSS\Position\Positionable;

class SourceException extends \Exception implements Positionable
{
    use Position;

    /**
     * @param int<1, max>|null $lineNumber
     */
    public function __construct(string $message, ?int $lineNumber = null)
    {
        $this->setPosition($lineNumber);
        if ($lineNumber !== null) {
            $message .= " [line no: $lineNumber]";
        }
        parent::__construct($message);
    }
}
