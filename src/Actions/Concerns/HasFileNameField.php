<?php

namespace z00f\FilamentReport\Actions\Concerns;

trait HasFileNameField
{
    protected string|null $fileNameFieldLabel;

    public function fileNameFieldLabel(string|null $label = null): static
    {
        $this->fileNameFieldLabel = $label;

        return $this;
    }

    public function getFileNameFieldLabel(): string|null
    {
        return $this->fileNameFieldLabel;
    }
}
