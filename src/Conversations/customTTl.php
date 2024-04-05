<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations;

trait customTTl
{
    protected ?int $customTtl = null;

    public function withCustomTTL(int $ttl): static
    {
        $this->customTtl = $ttl;
        return $this;
    }

    public function ttl(): ?int
    {
        return $this->customTtl;
    }
}
