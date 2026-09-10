<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Manual ordering, done with two links rather than a drag handle.
 *
 * Every ordered list in this CMS is short — six process stages, five people,
 * three services — and a pair of move buttons is keyboard-operable, survives
 * a page reload and cannot half-save. Positions are kept dense (0..n) after
 * every move and delete, so a swap is always a swap of adjacent integers.
 */
final class Ordering
{
    public const UP = 'up';

    public const DOWN = 'down';

    /** @return list<string> */
    public static function directions(): array
    {
        return [self::UP, self::DOWN];
    }

    /**
     * Moves a record one place within its ordered scope.
     *
     * @param  Builder<Model>  $scope  the sibling set, unordered
     * @return bool  false when the record already sits at that end
     */
    public static function move(Model $model, string $direction, Builder $scope): bool
    {
        self::resequence($scope);
        $model->refresh();

        $position = (int) $model->getAttribute('position');
        $target = $direction === self::UP ? $position - 1 : $position + 1;

        if ($target < 0) {
            return false;
        }

        $neighbour = (clone $scope)
            ->whereKeyNot($model->getKey())
            ->where('position', $target)
            ->first();

        if (! $neighbour) {
            return false;
        }

        DB::transaction(function () use ($model, $neighbour, $position, $target): void {
            $neighbour->forceFill(['position' => $position])->save();
            $model->forceFill(['position' => $target])->save();
        });

        return true;
    }

    /**
     * Rewrites positions to a dense 0..n sequence in current display order,
     * so gaps left by a delete never accumulate into ambiguous ordering.
     *
     * @param  Builder<Model>  $scope
     */
    public static function resequence(Builder $scope): void
    {
        $key = $scope->getModel()->getKeyName();
        $models = (clone $scope)->orderBy('position')->orderBy($key)->get();

        DB::transaction(function () use ($models): void {
            foreach ($models->values() as $index => $model) {
                if ((int) $model->getAttribute('position') !== $index) {
                    $model->forceFill(['position' => $index])->save();
                }
            }
        });
    }

    /**
     * The position a new record should take at the end of an ordered scope.
     *
     * @param  Builder<Model>  $scope
     */
    public static function nextPosition(Builder $scope): int
    {
        $max = (clone $scope)->max('position');

        return $max === null ? 0 : ((int) $max) + 1;
    }
}
