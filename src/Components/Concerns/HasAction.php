<?php

namespace z00f\FilamentReport\Components\Concerns;

use z00f\FilamentReport\Actions\FilamentReportBulkAction;
use z00f\FilamentReport\Actions\FilamentReportHeaderAction;

trait HasAction
{
    protected FilamentReportBulkAction | FilamentReportHeaderAction $action;

    public function action(FilamentReportBulkAction | FilamentReportHeaderAction $action): static
    {
        $this->action = $action;

        $this->getExport()->fileName($this->getAction()->getFileName());

        $this->getExport()->table($this->getAction()->getTable());

        return $this;
    }

    public function getAction(): FilamentReportBulkAction | FilamentReportHeaderAction
    {
        return $this->action;
    }
}
