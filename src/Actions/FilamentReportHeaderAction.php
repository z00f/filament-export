<?php

namespace z00f\FilamentReport\Actions;

use z00f\FilamentReport\Actions\Concerns\CanDisableAdditionalColumns;
use z00f\FilamentReport\Actions\Concerns\CanDisableFilterColumns;
use z00f\FilamentReport\Actions\Concerns\CanDisableFileName;
use z00f\FilamentReport\Actions\Concerns\CanDisableFileNamePrefix;
use z00f\FilamentReport\Actions\Concerns\CanDisablePreview;
use z00f\FilamentReport\Actions\Concerns\CanHaveExtraViewData;
use z00f\FilamentReport\Actions\Concerns\CanUseSnappy;
use z00f\FilamentReport\Actions\Concerns\HasAdditionalColumnsField;
use z00f\FilamentReport\Actions\Concerns\HasFilterColumnsField;
use z00f\FilamentReport\Actions\Concerns\HasDefaultFormat;
use z00f\FilamentReport\Actions\Concerns\HasDefaultPageOrientation;
use z00f\FilamentReport\Actions\Concerns\HasExportModelActions;
use z00f\FilamentReport\Actions\Concerns\HasFileName;
use z00f\FilamentReport\Actions\Concerns\HasFileNameField;
use z00f\FilamentReport\Actions\Concerns\HasFormatField;
use z00f\FilamentReport\Actions\Concerns\HasPageOrientationField;
use z00f\FilamentReport\Actions\Concerns\HasTimeFormat;
use z00f\FilamentReport\Actions\Concerns\HasUniqueActionId;
use z00f\FilamentReport\FilamentReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FilamentReportHeaderAction extends \Filament\Tables\Actions\Action
{
    use CanDisableAdditionalColumns;
    use CanDisableFilterColumns;
    use CanDisableFileName;
    use CanDisableFileNamePrefix;
    use CanDisablePreview;
    use CanHaveExtraViewData;
    use CanUseSnappy;
    use HasAdditionalColumnsField;
    use HasFilterColumnsField;
    use HasDefaultFormat;
    use HasDefaultPageOrientation;
    use HasExportModelActions;
    use HasFileName;
    use HasFileNameField;
    use HasFormatField;
    use HasPageOrientationField;
    use HasTimeFormat;
    use HasUniqueActionId;

    protected Collection $records;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uniqueActionId('header-action');

        FilamentReport::setUpFilamentReportAction($this);

        $this->form(static function ($action): array {
            $records = $action->getTableRecords();

            $action->records = $records;

            return array_merge(
                FilamentReport::getFormComponents($action, $records),
                [
                    \Filament\Forms\Components\Hidden::make('records')
                        ->default($records)
                ]
            );
        })
            ->action(static function ($action, $data) {
                $records = $action->getRecords();

                return FilamentReport::callDownload($action, $records, $data);
            });
    }

    public function getTableRecords(): Collection
    {
        $livewire = $this->getLivewire();

        $model = $this->getTable()->getModel();
        $query = $model::query();

        $filterData = $livewire->tableFilters;

        if (isset($livewire->ownerRecord)) {
            $query->whereBelongsTo($livewire->ownerRecord);
        }

        $livewire->cacheTableFilters();

        $query->where(function (Builder $query) use ($filterData, $livewire) {
            foreach ($livewire->getCachedTableFilters() as $filter) {
                $filter->apply(
                    $query,
                    $filterData[$filter->getName()] ?? [],
                );
            }
        });

        $searchQuery = $livewire->tableSearchQuery;

        if ($searchQuery !== '') {
            foreach (explode(' ', $searchQuery) as $searchQueryWord) {
                $query->where(function (Builder $query) use ($searchQueryWord, $livewire) {
                    $isFirst = true;

                    foreach ($livewire->getCachedTableColumns() as $column) {
                        $column->applySearchConstraint($query, $searchQueryWord, $isFirst);
                    }
                });
            }
        }

        foreach ($livewire->getCachedTableColumns() as $column) {
            $column->applyEagerLoading($query);
            $column->applyRelationshipAggregates($query);
        }

        $this->applySortingToTableQuery($query);

        return $query->get();
    }

    protected function applySortingToTableQuery(Builder $query): Builder
    {
        $livewire = $this->getLivewire();

        $columnName = $livewire->tableSortColumn;

        if (!$columnName) {
            return $query;
        }

        $direction = $livewire->tableSortDirection === 'desc' ? 'desc' : 'asc';

        if ($column = $livewire->getCachedTableColumns()[$columnName]) {
            $column->applySort($query, $direction);

            return $query;
        }

        if ($columnName === $livewire->getDefaultSortColumn()) {
            return $query->orderBy($columnName, $direction);
        }

        return $query;
    }

    public function getRecords(): Collection
    {
        return $this->records;
    }
}
