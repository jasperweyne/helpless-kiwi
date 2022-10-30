<?php

namespace App\Mail;

class Attachment
{
    public function __construct(
        public readonly string $body,
        public readonly string $filename,
        public readonly string $mimetype
    ) {
    }
}
