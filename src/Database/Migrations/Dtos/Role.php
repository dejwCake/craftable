<?php

declare(strict_types=1);

namespace Brackets\Craftable\Database\Migrations\Dtos;

final readonly class Role
{
    public function __construct(public string $name, public string $guardName,)
    {
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'guard_name' => $this->guardName,
        ];
    }
}
