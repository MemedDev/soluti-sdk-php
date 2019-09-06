<?php

declare(strict_types=1);

namespace Memed\Soluti\Receiver;

use ArrayIterator;

class DocumentSet extends ArrayIterator
{
    /**
     * Checks if this set has at least one document unsigned.
     */
    public function isWaiting(): bool
    {
        foreach ($this as $document) {
            if (false === $document->isSigned()) {
                return true;
            }
        }

        return false;
    }
}
