<?php

declare(strict_types=1);

namespace Brackets\Craftable\Traits;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait PublishableTrait
{
    /**
     * Scope a query to only include published models.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->when(
                $this->hasPublishedAt(),
                static fn (Builder $query) => $query->where(static function (Builder $query): void {
                        $query->where('published_at', '<=', CarbonImmutable::now())
                            ->whereNotNull('published_at');
                }),
            )
            ->when(
                $this->hasPublishedTo(),
                static fn (Builder $query) => $query->where(static function (Builder $query): void {
                        $query->where('published_to', '>=', CarbonImmutable::now())
                            ->orWhereNull('published_to');
                }),
            );
    }

    /**
     * Scope a query to only include unpublished models.
     */
    public function scopeUnpublished(Builder $query): Builder
    {
        return $query
            ->when($this->hasPublishedAt(), static function (Builder $query): void {
                $query->where('published_at', '>', CarbonImmutable::now())
                    ->orWhereNull('published_at');
            })
            ->when($this->hasPublishedTo(), static function (Builder $query): void {
                $query->orWhere('published_to', '<', CarbonImmutable::now());
            });
    }

    public function isPublished(): bool
    {
        if (!$this->hasPublishedAt()) {
            return true;
        }

        if ($this->published_at === null) {
            return false;
        }

        return $this->published_at->lte(CarbonImmutable::now())
            && (
                $this->hasPublishedTo()
                ? ($this->published_to->gte(CarbonImmutable::now()) || $this->published_to === null)
                : true
            );
    }

    public function isUnpublished(): bool
    {
        return !$this->isPublished();
    }

    public function publish(): bool
    {
        if (!$this->hasPublishedAt()) {
            return true;
        }

        $data = [
            'published_at' => CarbonImmutable::now(),
        ];

        if ($this->hasPublishedTo() && $this->published_to->lte(CarbonImmutable::now())) {
            $data['published_to'] = null;
        }

        return $this->update($data);
    }

    public function unpublish(): bool
    {
        return $this->update([
            'published_at' => null,
        ]);
    }

    private function hasPublishedAt(): bool
    {
        if ($this instanceof Model) {
            return $this->hasAttribute('published_at')
                || ($this->dates !== null && in_array('published_at', $this->dates, true))
                || ($this->casts !== null && in_array('published_at', $this->casts, true));
        }

        return false;
    }

    private function hasPublishedTo(): bool
    {
        if ($this instanceof Model) {
            return $this->hasAttribute('published_to')
                || ($this->dates !== null && in_array('published_to', $this->dates, true))
                || ($this->casts !== null && in_array('published_to', $this->casts, true));
        }

        return false;
    }
}
