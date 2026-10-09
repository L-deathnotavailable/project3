<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    /** @return Collection<int, Tag> */
    public function list(): Collection
    {
        return Tag::query()->withCount('notes')->orderBy('name')->get();
    }

    /** @param array{name: string} $attributes */
    public function create(array $attributes): Tag
    {
        $tag = Tag::query()->create($attributes);

        return $tag->loadCount('notes');
    }

    /** @param array{name: string} $attributes */
    public function update(Tag $tag, array $attributes): Tag
    {
        $tag->update($attributes);

        return $tag->loadCount('notes');
    }

    public function isUsed(Tag $tag): bool
    {
        return $tag->notes()->exists();
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }
}
