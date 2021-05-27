<?php

namespace Easy\Interfaces;

use Illuminate\Support\Collection;

/**
 * @method getDirty()
 */
interface ILogable
{
    function getLogModel(): string;

    function getLogData(bool $with_relations, bool $include_null, ?array $only_attributes = null): Collection;

    function getLogableAttributes(): array;

    function getLogableRelations(): array;

    public function logs();
}
