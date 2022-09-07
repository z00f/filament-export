<?php

namespace z00f\FilamentReport\Concerns;

use z00f\FilamentReport\FilamentReport;

trait HasFormat
{
    protected string $format;

    public function format(string $format): static
    {
        if (!array_key_exists($format, FilamentReport::FORMATS)) {
            return $this;
        }

        $this->format = $format;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
