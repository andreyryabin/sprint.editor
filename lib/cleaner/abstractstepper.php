<?php

namespace Sprint\Editor\Cleaner;

abstract class AbstractStepper
{
    abstract public function getEntityIds(): array;

    abstract public function scanEntityElements($entityId, $pageNum = 1): array;

    abstract public function getSearchMessage($entityId, int $filesCount): string;

    public function getSearchColor(): string
    {
        return '';
    }
}
