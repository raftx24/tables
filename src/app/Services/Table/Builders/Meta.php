<?php

namespace LaravelEnso\Tables\app\Services\Table\Builders;

use ReflectionClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Tables\app\Contracts\Table;
use LaravelEnso\Tables\app\Services\Config;
use LaravelEnso\Tables\app\Traits\TableCache;
use LaravelEnso\Tables\app\Contracts\RawTotal;
use LaravelEnso\Tables\app\Services\Table\Filters;
use LaravelEnso\Tables\app\Exceptions\MetaException;
use LaravelEnso\Tables\app\Exceptions\CacheException;

class Meta
{
    private $table;
    private $config;
    private $query;
    private $filters;
    private $count;
    private $filtered;
    private $total;
    private $fullRecordInfo;

    public function __construct(Table $table, Config $config)
    {
        $this->table = $table;
        $this->config = $config;
        $this->query = $table->query();
        $this->total = collect();
        $this->filters = false;
    }

    public function build()
    {
        $this->setCount()
            ->filter()
            ->setDetailedInfo()
            ->countFiltered()
            ->setTotal();

        return $this;
    }

    public function toArray()
    {
        $this->build();

        return [
            'count' => $this->count,
            'filtered' => $this->filtered,
            'total' => $this->total,
            'fullRecordInfo' => $this->fullRecordInfo,
            'filters' => $this->filters,
        ];
    }

    public function count()
    {
        return $this->query->count();
    }

    private function setCount()
    {
        $this->count = $this->cachedCount();
        $this->filtered = null;

        return $this;
    }

    private function filter()
    {
        $filters = new Filters(
            $this->table, $this->config, $this->query
        );

        if ($this->filters = $filters->applies()) {
            $filters->handle();
        }

        return $this;
    }

    private function setDetailedInfo()
    {
        $this->fullRecordInfo = $this->config->meta()->get('forceInfo')
            || $this->count <= $this->config->meta()->get('fullInfoRecordLimit')
            || ! $this->filters;

        return $this;
    }

    private function countFiltered()
    {
        if ($this->filters && $this->fullRecordInfo) {
            $this->filtered = $this->count();
        }

        return $this;
    }

    private function setTotal()
    {
        $this->config->columns()
            ->filter(function ($column) {
                return $column->get('meta')->get('total')
                    || $column->get('meta')->get('customTotal')
                    || $column->get('meta')->get('rawTotal');
            })->each(function ($column) {
                $this->total[$column->get('name')] = $column->get('meta')->get('rawTotal')
                    ? $this->rawTotal($column)
                    : $this->query->sum($column->get('data'));

                if ($column->get('meta')->get('cents')) {
                    $this->total[$column->get('name')] /= 100;
                }
            });

        return $this;
    }

    private function rawTotal($column)
    {
        if (! $this->table instanceof RawTotal) {
            throw MetaException::missingInterface();
        }

        return (clone $this->query)->select(
            DB::raw("{$this->table->rawTotal($column)} as {$column->get('name')}")
        )->first()->{$column->get('name')};
    }

    private function cachedCount()
    {
        return $this->shouldCache()
            ? Cache::remember($this->cacheKey(), now()->addHour(), function () {
                return $this->count();
            }) : $this->count();
    }

    private function cacheKey()
    {
        return config('enso.tables.cache.prefix')
            .':'.$this->query->getModel()->getTable();
    }

    private function shouldCache()
    {
        $shouldCache = $this->config->has('countCache')
            ? $this->config->get('countCache')
            : config('enso.tables.cache.count'); 

        if ($shouldCache) {
            $model = $this->query->getModel();

            if (! collect((new ReflectionClass($model))->getTraits())->has(TableCache::class)) {
                throw CacheException::missingTrait(get_class($model));
            }
        }

        return $shouldCache;
    }
}
