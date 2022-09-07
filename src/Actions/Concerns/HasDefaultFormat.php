<?php

namespace z00f\FilamentReport\Actions\Concerns;

use z00f\FilamentReport\FilamentReport;

trait HasDefaultFormat
{
    protected string $defaultFormat;

    public function defaultFormat(string $defaultFormat): static
    {
        if (!array_key_exists($defaultFormat, FilamentReport::FORMATS)) {
            return $this;
        }

        $this->defaultFormat = $defaultFormat;

        return $this;
    }

    public function getDefaultFormat(): string
    {
        return $this->defaultFormat;
    }
}
