<?php

namespace z00f\FilamentReport\Actions\Concerns;

trait CanDisableAdditionalColumns
{
    protected bool $isAdditionalColumnsDisabled;

    public function disableAdditionalColumns(bool $condition = true): static
    {
        $this->isAdditionalColumnsDisabled = $condition;

        return $this;
    }

    public function isAdditionalColumnsDisabled(): bool
    {
        return $this->isAdditionalColumnsDisabled;
    }
}
