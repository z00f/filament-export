<?php

namespace z00f\FilamentReport;

use z00f\FilamentReport\Actions\FilamentReportBulkAction;
use z00f\FilamentReport\Actions\FilamentReportHeaderAction;
use z00f\FilamentReport\Components\TableView;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;
use z00f\FilamentReport\Concerns\CanFilterColumns;
use z00f\FilamentReport\Concerns\CanHaveAdditionalColumns;
use z00f\FilamentReport\Concerns\CanHaveExtraViewData;
use z00f\FilamentReport\Concerns\CanUseSnappy;
use z00f\FilamentReport\Concerns\HasData;
use z00f\FilamentReport\Concerns\HasFileName;
use z00f\FilamentReport\Concerns\HasFormat;
use z00f\FilamentReport\Concerns\HasPageOrientation;
use z00f\FilamentReport\Concerns\HasTable;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilamentReport implements FromCollection, WithHeadings, WithTitle, WithCustomCsvSettings, ShouldAutoSize
{
    use CanFilterColumns;
    use CanHaveAdditionalColumns;
    use CanHaveExtraViewData;
    use CanUseSnappy;
    use HasData;
    use HasFileName;
    use HasFormat;
    use HasPageOrientation;
    use HasTable;

    public const FORMATS = [
        'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
        'csv' => \Maatwebsite\Excel\Excel::CSV,
        'pdf' => 'Pdf'
    ];

    public static function make(): static
    {
        $static = app(static::class);
        $static->setUp();

        return $static;
    }

    protected function setUp(): void
    {
        $this->fileName(Date::now()->toString());

        $this->format(config('filament-export.default_format'));
    }

    public function getAllColumns(): Collection
    {
        $columns = collect($this->getTable()->getColumns());

        if ($this->getFilteredColumns()->isNotEmpty()) {
            $columns = $columns->filter(fn ($column) => $this->getFilteredColumns()->contains($column->getName()));
        }

        if ($this->getAdditionalColumns()->isNotEmpty()) {
            $columns = $columns->merge($this->getAdditionalColumns());
        }

        return $columns;
    }

    public function getPdfView(): string
    {
        return 'filament-export::pdf';
    }

    public function getPdfViewData(): array
    {
        return array_merge(
            [
                'fileName' => $this->getFileName(),
                'columns' => $this->getAllColumns(),
                'rows' => $this->collection()
            ],
            $this->getExtraViewData()
        );
    }

    public function download(): BinaryFileResponse | StreamedResponse
    {
        if ($this->getFormat() === 'pdf') {
            $pdf = $this->getPdf();

            return response()->streamDownload(fn () => print($pdf->output()), "{$this->getFileName()}.{$this->getFormat()}");
        }

        return Excel::download($this, "{$this->getFileName()}.{$this->getFormat()}", static::FORMATS[$this->getFormat()]);
    }

    public function getPdf(): \Barryvdh\DomPDF\PDF | \Barryvdh\Snappy\PdfWrapper
    {
        if ($this->shouldUseSnappy()) {
            return \Barryvdh\Snappy\Facades\SnappyPdf::loadView($this->getPdfView(), $this->getPdfViewData())
                ->setPaper('A4', $this->getPageOrientation());
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView($this->getPdfView(), $this->getPdfViewData())
            ->setPaper('A4', $this->getPageOrientation());
    }

    public static function setUpFilamentReportAction(FilamentReportHeaderAction | FilamentReportBulkAction $action): void
    {
        $action->timeFormat(config('filament-export.time_format'));

        $action->defaultFormat(config('filament-export.default_format'));

        $action->defaultPageOrientation(config('filament-export.default_page_orientation'));

        $action->disableAdditionalColumns(config('filament-export.disable_additional_columns'));

        $action->disableFilterColumns(config('filament-export.disable_filter_columns'));

        $action->disableFileName(config('filament-export.disable_file_name'));

        $action->disableFileNamePrefix(config('filament-export.disable_file_name_prefix'));

        $action->disablePreview(config('filament-export.disable_preview'));

        $action->snappy(config('filament-export.use_snappy', false));

        $action->icon(config('filament-export.action_icon'));

        $action->fileName(Carbon::now()->translatedFormat($action->getTimeFormat()));

        $action->fileNameFieldLabel(__('filament-export::export_action.file_name_field_label'));

        $action->filterColumnsFieldLabel(__('filament-export::export_action.filter_columns_field_label'));

        $action->formatFieldLabel(__('filament-export::export_action.format_field_label'));

        $action->pageOrientationFieldLabel(__('filament-export::export_action.page_orientation_field_label'));

        $action->additionalColumnsFieldLabel(__('filament-export::export_action.additional_columns_field.label'));

        $action->additionalColumnsTitleFieldLabel(__('filament-export::export_action.additional_columns_field.title_field_label'));

        $action->additionalColumnsDefaultValueFieldLabel(__('filament-export::export_action.additional_columns_field.default_value_field_label'));

        $action->additionalColumnsAddButtonLabel(__('filament-export::export_action.additional_columns_field.add_button_label'));

        $action->modalButton(__('filament-export::export_action.export_action_label'));

        $action->modalHeading(__('filament-export::export_action.modal_heading'));

        $action->modalActions($action->getExportModalActions());
    }

    public static function getFormComponents(FilamentReportHeaderAction | FilamentReportBulkAction $action, Collection $records): array
    {
        $action->fileNamePrefix($action->getFileNamePrefix() ?: $action->getTable()->getHeading());

        $columns = collect($action->getTable()->getColumns())
            ->mapWithKeys(fn ($column) => [$column->getName() => $column->getLabel()])
            ->toArray();

        $updateTableView = function ($component, $livewire) use ($records, $action) {
            $data =  $action instanceof FilamentReportBulkAction ? $livewire->mountedTableBulkActionData : $livewire->mountedTableActionData;

            $export = FilamentReport::make()
                ->filteredColumns($data["filter_columns"] ?? [])
                ->additionalColumns($data["additional_columns"] ?? [])
                ->data($records)
                ->table($action->getTable())
                ->extraViewData($action->getExtraViewData());

            $table_view = $component->getContainer()->getComponent(fn ($component) => $component->getName() === 'table_view');

            $table_view->export($export);
        };

        return [
            \Filament\Forms\Components\TextInput::make('file_name')
                ->label($action->getFileNameFieldLabel())
                ->default($action->getFileName())
                ->hidden($action->isFileNameDisabled())
                ->rule('regex:/[a-zA-Z0-9\s_\\.\-\(\):]/')
                ->required(),
            \Filament\Forms\Components\Select::make('format')
                ->label($action->getFormatFieldLabel())
                ->options(FilamentReport::FORMATS)
                ->default($action->getDefaultFormat())
                ->reactive(),
            \Filament\Forms\Components\Select::make('page_orientation')
                ->label($action->getPageOrientationFieldLabel())
                ->options(FilamentReport::getPageOrientations())
                ->default($action->getDefaultPageOrientation())
                ->visible(fn ($get) => $get('format') === 'pdf')
                ->reactive(),
            \Filament\Forms\Components\CheckboxList::make('filter_columns')
                ->label($action->getFilterColumnsFieldLabel())
                ->options($columns)
                ->columns(4)
                ->default(array_keys($columns))
                ->afterStateUpdated($updateTableView)
                ->reactive()
                ->hidden($action->isFilterColumnsDisabled()),
            \Filament\Forms\Components\KeyValue::make('additional_columns')
                ->label($action->getAdditionalColumnsFieldLabel())
                ->keyLabel($action->getAdditionalColumnsTitleFieldLabel())
                ->valueLabel($action->getAdditionalColumnsDefaultValueFieldLabel())
                ->addButtonLabel($action->getAdditionalColumnsAddButtonLabel())
                ->afterStateUpdated($updateTableView)
                ->reactive()
                ->hidden($action->isAdditionalColumnsDisabled()),
            TableView::make('table_view')
                ->export(
                    FilamentReport::make()
                        ->data($records)
                        ->table($action->getTable())
                        ->extraViewData($action->getExtraViewData())
                )
                ->uniqueActionId($action->getUniqueActionId())
                ->reactive()
        ];
    }

    public static function callDownload(FilamentReportHeaderAction | FilamentReportBulkAction $action, Collection $records, array $data)
    {
        return FilamentReport::make()
            ->fileName($data["file_name"] ?? $action->getFileName())
            ->data($records)
            ->table($action->getTable())
            ->filteredColumns(!$action->isFilterColumnsDisabled() ? $data["filter_columns"] : [])
            ->additionalColumns(!$action->isAdditionalColumnsDisabled() ? $data["additional_columns"] : [])
            ->format($data["format"] ?? $action->getDefaultFormat())
            ->pageOrientation($data["page_orientation"] ?? $action->getDefaultPageOrientation())
            ->snappy($action->shouldUseSnappy())
            ->extraViewData($action->getExtraViewData())
            ->download();
    }

    public function collection(): Collection
    {
        $records = $this->getData();

        $columns = $this->getAllColumns();

        $items = [];

        foreach ($records as $record) {
            $item = [];
            foreach ($columns as $column) {
                $column = $column->record($record);
                $state = in_array(\Filament\Tables\Columns\Concerns\CanFormatState::class, class_uses($column)) ? $column->getFormattedState() : $column->getState();
                if (is_array($state)) {
                    $state = implode(", ", $state);
                } elseif ($column instanceof ImageColumn) {
                    $state = $column->getImagePath();
                } elseif ($column instanceof ViewColumn) {
                    $state = trim(preg_replace('/\s+/', ' ', strip_tags($column->render()->render())));
                }
                $item[$column->getName()] = $state;
            }
            array_push($items, $item);
        }

        return collect($items);
    }

    public function headings(): array
    {
        $allColumns = $this->getAllColumns();

        $headers = $allColumns->map(fn ($column) => $column->getLabel());

        if ($this->getFilteredColumns()->isNotEmpty()) {
            $headers = $allColumns
                ->filter(fn ($column) => $this->getFilteredColumns()->contains($column->getName()) || $this->getAdditionalColumns()->contains($column))
                ->map(fn ($column) => $column->getLabel());
        }

        return $headers->toArray();
    }

    public function title(): string
    {
        return $this->getFileName();
    }

    public function getCsvSettings(): array
    {
        return ['use_bom' => true];
    }
}
