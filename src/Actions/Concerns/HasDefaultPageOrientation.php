<?php

namespace z00f\FilamentReport\Actions\Concerns;

use z00f\FilamentReport\FilamentReport;

trait HasDefaultPageOrientation
{
    protected string $defaultPageOrientation;

    public function defaultPageOrientation(string $defaultPageOrientation): static
    {
        if (!array_key_exists($defaultPageOrientation, FilamentReport::getPageOrientations())) {
            return $this;
        }

        $this->defaultPageOrientation = $defaultPageOrientation;

        return $this;
    }

    public function getDefaultPageOrientation(): string
    {
        return $this->defaultPageOrientation;
    }
}
