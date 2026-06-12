<?php

namespace App\Notifications\Messages;

class FcmMessage
{
    public function __construct(
        public string $title,
        public string $body,
        public array $data = [],
    ) {}

    public static function make(string $title, string $body): self
    {
        return new self($title, $body);
    }

    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }
}
