<?php

declare(strict_types=1);

namespace Codav\WebsiteFeatures\Services;

use Codav\WebsiteFeatures\Models\WebsiteFeature;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WebsiteFeatureService
{
    public function create(array $data): WebsiteFeature
    {
        return DB::transaction(function () use ($data): WebsiteFeature {
            $this->normalizeOrdering();

            $data["slug"] ??= Str::slug($data["title"]);

            $data["sort_order"] = $this->resolveCreatePosition(
                isset($data["sort_order"])
                    ? (int) $data["sort_order"]
                    : null
            );

            return WebsiteFeature::query()->create($data);
        });
    }

    public function update(
        WebsiteFeature $feature,
        array $data,
    ): WebsiteFeature {
        return DB::transaction(function () use (
            $feature,
            $data,
        ): WebsiteFeature {
            $hasPosition = array_key_exists(
                "sort_order",
                $data,
            );

            $position = $hasPosition
                ? (int) $data["sort_order"]
                : null;

            unset($data["sort_order"]);

            if (!empty($data)) {
                $feature->update($data);
            }

            if ($hasPosition && $position !== null) {
                $this->moveTo(
                    $feature->refresh(),
                    $position,
                );
            }

            return $feature->refresh();
        });
    }

    public function delete(
        WebsiteFeature $feature,
    ): void {
        DB::transaction(function () use ($feature): void {
            $feature->delete();

            $this->normalizeOrdering();
        });
    }

    public function activate(
        WebsiteFeature $feature,
    ): WebsiteFeature {
        $feature->update([
            "is_active" => true,
        ]);

        return $feature->refresh();
    }

    public function deactivate(
        WebsiteFeature $feature,
    ): WebsiteFeature {
        $feature->update([
            "is_active" => false,
        ]);

        return $feature->refresh();
    }

    public function toggle(
        WebsiteFeature $feature,
    ): WebsiteFeature {
        $feature->update([
            "is_active" => !$feature->is_active,
        ]);

        return $feature->refresh();
    }

    public function moveUp(
        WebsiteFeature $feature,
    ): WebsiteFeature {
        return DB::transaction(
            function () use ($feature): WebsiteFeature {
                $features = $this->lockedFeatures();

                $index = $features->search(
                    static fn (
                        WebsiteFeature $item,
                    ): bool =>
                        (int) $item->id ===
                        (int) $feature->id,
                );

                if ($index === false || $index === 0) {
                    return $feature->refresh();
                }

                $previous = $features[$index - 1];

                $this->swapPositions(
                    $feature,
                    $previous,
                );

                return $feature->refresh();
            },
        );
    }

    public function moveDown(
        WebsiteFeature $feature,
    ): WebsiteFeature {
        return DB::transaction(
            function () use ($feature): WebsiteFeature {
                $features = $this->lockedFeatures();

                $index = $features->search(
                    static fn (
                        WebsiteFeature $item,
                    ): bool =>
                        (int) $item->id ===
                        (int) $feature->id,
                );

                if (
                    $index === false ||
                    $index >= $features->count() - 1
                ) {
                    return $feature->refresh();
                }

                $next = $features[$index + 1];

                $this->swapPositions(
                    $feature,
                    $next,
                );

                return $feature->refresh();
            },
        );
    }

    public function moveTo(
        WebsiteFeature $feature,
        int $position,
    ): WebsiteFeature {
        if ($position < 1) {
            throw new InvalidArgumentException(
                "The feature position must be greater than or equal to 1.",
            );
        }

        return DB::transaction(
            function () use (
                $feature,
                $position,
            ): WebsiteFeature {
                $features = $this->lockedFeatures();

                $currentIndex = $features->search(
                    static fn (
                        WebsiteFeature $item,
                    ): bool =>
                        (int) $item->id ===
                        (int) $feature->id,
                );

                if ($currentIndex === false) {
                    throw new InvalidArgumentException(
                        "The feature does not exist in the ordering set.",
                    );
                }

                $targetIndex = min(
                    $position - 1,
                    max(
                        0,
                        $features->count() - 1,
                    ),
                );

                if ($currentIndex === $targetIndex) {
                    return $feature->refresh();
                }

                $items = $features->all();

                $moved = array_splice(
                    $items,
                    $currentIndex,
                    1,
                )[0];

                array_splice(
                    $items,
                    $targetIndex,
                    0,
                    [$moved],
                );

                $this->persistOrdering($items);

                return $feature->refresh();
            },
        );
    }

    public function reorder(
        array $featureIds,
    ): Collection {
        return DB::transaction(
            function () use ($featureIds): Collection {
                $featureIds = array_values(
                    array_unique(
                        array_map(
                            "intval",
                            $featureIds,
                        ),
                    ),
                );

                $features = $this->lockedFeatures();

                if (
                    count($featureIds) !==
                    $features->count()
                ) {
                    throw new InvalidArgumentException(
                        "The reorder payload must contain every feature exactly once.",
                    );
                }

                $existingIds = $features
                    ->pluck("id")
                    ->map(
                        static fn ($id): int => (int) $id,
                    )
                    ->sort()
                    ->values()
                    ->all();

                $requestedIds = $featureIds;

                sort($requestedIds);

                if ($existingIds !== $requestedIds) {
                    throw new InvalidArgumentException(
                        "The reorder payload contains invalid or missing feature IDs.",
                    );
                }

                $byId = $features->keyBy("id");

                $orderedItems = array_map(
                    static fn (int $id): WebsiteFeature =>
                        $byId->get($id),
                    $featureIds,
                );

                $this->persistOrdering($orderedItems);

                return WebsiteFeature::query()
                    ->ordered()
                    ->get();
            },
        );
    }

    protected function lockedFeatures(): Collection
    {
        return WebsiteFeature::query()
            ->orderBy("sort_order")
            ->orderBy("id")
            ->lockForUpdate()
            ->get();
    }

    protected function persistOrdering(
        array $features,
    ): void {
        foreach ($features as $index => $feature) {
            $position = $index + 1;

            if (
                (int) $feature->sort_order !==
                $position
            ) {
                $feature->update([
                    "sort_order" => $position,
                ]);
            }
        }
    }

    protected function swapPositions(
        WebsiteFeature $first,
        WebsiteFeature $second,
    ): void {
        $firstPosition = (int) $first->sort_order;
        $secondPosition = (int) $second->sort_order;

        if ($firstPosition === $secondPosition) {
            return;
        }

        $first->update([
            "sort_order" => 0,
        ]);

        $second->update([
            "sort_order" => $firstPosition,
        ]);

        $first->update([
            "sort_order" => $secondPosition,
        ]);
    }

    protected function normalizeOrdering(): void
    {
        $features = WebsiteFeature::query()
            ->orderBy("sort_order")
            ->orderBy("id")
            ->lockForUpdate()
            ->get();

        $this->persistOrdering(
            $features->all(),
        );
    }

    protected function resolveCreatePosition(
        ?int $position,
    ): int {
        $count = WebsiteFeature::query()
            ->lockForUpdate()
            ->count();

        if ($position === null) {
            return $count + 1;
        }

        return max(
            1,
            min(
                $position,
                $count + 1,
            ),
        );
    }
}