<?php

namespace z00f\FilamentReport\Actions\Concerns;

use Closure;

trait CanHaveExtraViewData
{
    protected array | Closure $extraViewData = [];

    public function extraViewData(array | Closure $extraViewData): static
    {
        $this->extraViewData = $extraViewData;

        return $this;
    }

    public function getExtraViewData(): array
    {
        return $this->evaluate($this->extraViewData);
    }
}
