<?php

namespace z00f\FilamentReport\Actions;

use z00f\FilamentReport\FilamentReport;
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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilamentReportBulkAction extends \Filament\Tables\Actions\BulkAction
{
    use CanDisableAdditionalColumns;
    use CanDisableFilterColumns;
    use CanDisableFileName;
    use CanDisablePreview;
    use CanHaveExtraViewData;
    use CanUseSnappy;
    use HasAdditionalColumnsField;
    use HasFilterColumnsField;
    use HasDefaultFormat;
    use HasDefaultPageOrientation;
    use HasFileName;
    use CanDisableFileNamePrefix;
    use HasExportModelActions;
    use HasFileNameField;
    use HasFormatField;
    use HasPageOrientationField;
    use HasTimeFormat;
    use HasUniqueActionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uniqueActionId('bulk-action');

        FilamentReport::setUpFilamentReportAction($this);

        $this->form(static fn ($action, $records): array => FilamentReport::getFormComponents($action, $records))
            ->action(static fn ($action, $records, $data): BinaryFileResponse|StreamedResponse => FilamentReport::callDownload($action, $records, $data));
    }
}
