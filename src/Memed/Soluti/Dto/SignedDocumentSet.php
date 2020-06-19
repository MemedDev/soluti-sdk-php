<?php

declare(strict_types=1);

namespace Memed\Soluti\Dto;

use ArrayIterator;

class SignedDocumentSet extends ArrayIterator
{
    public static function create($documents): self
    {
        foreach ($documents as $key => $document) {
            $documents[$key] = SignedDocument::create($document);
        }

        return new static($documents);
    }
}