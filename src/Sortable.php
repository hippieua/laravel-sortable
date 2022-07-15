<?php

namespace Hippie\Sortable;

use Exception;
use Illuminate\Support\Facades\Schema;

trait Sortable
{
    /**
     * @throws Exception
     */
    public static function bootSortable(): void
    {
        $model = new self();

        if (!$model->sortable_field()) {
            throw new Exception('Sortable: no protected $sortable_field property on '.self::class);
        }

        if (!Schema::hasColumn($model->getTable(), $model->sortable_field())) {
            throw new Exception('Sortable: Unknown column `'.$model->sortable_field().'` in '.$model->getTable().' table');
        }

        if ($model->sortable_relation()) {
            try {
                self::has($model->sortable_relation());
            } catch (\BadMethodCallException $exception) {
                throw new Exception('Sortable: Model '.self::class.' dont have '.$model->sortable_relation().' relationship');
            }
        }
    }

    public function sortable_field(): ?string
    {
        return property_exists($this, 'sortable_field')
            ? $this->sortable_field
            : null;
    }

    public function sortable_relation(): ?string
    {
        return property_exists($this, 'sortable_relation')
            ? $this->sortable_relation
            : null;
    }

    public function move(string $direction): void
    {
        if ($direction == 'down') {
            $this->moveDown();
        } elseif ($direction == 'up') {
            $this->moveUp();
        }
    }

    public function moveUp(): void
    {
        $array = $this->items()->toArray();

        if ($this->index() > 0 and $this->index() < count($array)) {
            $items = array_slice($array, 0, ($this->index() - 1), true);
            $items[] = $array[$this->index()];
            $items[] = $array[$this->index() - 1];
            $items += array_slice($array, ($this->index() + 1), count($array), true);

            $this->updateSortField($items);
        }
    }

    public function moveDown(): void
    {
        $array = $this->items()->toArray();

        if (count($array) - 1 > $this->index()) {
            $items = array_slice($array, 0, $this->index(), true);
            $items[] = $array[$this->index() + 1];
            $items[] = $array[$this->index()];
            $items += array_slice($array, $this->index() + 2, count($array), true);
            $this->updateSortField($items);
        }
    }

    public function updateSortOrderOnCreate(): void
    {
        if ($this->getRelationValue($this->sortable_relation())) {
            $this->update([
                'order' => $this->items()->count(),
            ]);
        }
    }

    private function items()
    {
        if ($this->sortable_relation()) {
            $relation = $this->getRelationValue($this->sortable_relation());
            $collection = self::class::where($relation->getForeignKey(), $relation->getKey())->get();
        } else {
            $collection = self::class::all();
        }

        return $collection->sortBy($this->sortable_field())->values();
    }

    private function index()
    {
        return $this->items()->search(self::class::find($this->id));
    }

    private function updateSortField($items): void
    {
        $index = 1;
        foreach ($items as $item) {
            self::class::find($item['id'])->update(['order' => $index++]);
        }
    }
}
