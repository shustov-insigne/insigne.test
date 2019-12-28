<?php

namespace App\Entity;

interface CacheableInterface
{
    /**
     * @return string[]
     */
    public function getCacheKeys(): array;
}