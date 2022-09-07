<?php

namespace z00f\FilamentReport\Concerns;

trait HasFileName
{
    protected string $fileName;

    public function fileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}
