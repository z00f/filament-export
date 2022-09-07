<?php

namespace z00f\FilamentReport\Actions\Concerns;

trait CanUseSnappy
{
    protected bool $shouldUseSnappy = false;

    public function snappy(bool $condition = true): static
    {
        $this->shouldUseSnappy = $condition;

        return $this;
    }

    public function shouldUseSnappy(): bool
    {
        return $this->shouldUseSnappy;
    }
}
