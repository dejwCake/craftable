<?php

declare(strict_types=1);

namespace Brackets\Craftable\Database\Migrations\Dtos;

use Carbon\CarbonInterface;

final readonly class Permission
{
    public function __construct(
        public string $name,
        public string $guardName,
        public CarbonInterface $createdAt,
        public CarbonInterface $updatedAt,
    ) {
    }

    /** @return array<string, string|CarbonInterface> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'guard_name' => $this->guardName,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
