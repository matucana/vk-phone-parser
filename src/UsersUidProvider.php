<?php

namespace Matucana\VkPhoneParser;

use Generator;

class UsersUidProvider
{
    private int $start;

    private int $step;

    private int $limit;

    public function __construct(int $start = 1, int $step = 1000, int $limit = 251510316)
    {
        $this->start = $start;
        $this->step = $step;
        $this->limit = $limit;
    }

    public function getSeriesUid()
    {
        for ($this->start; $this->start <= $this->limit; $this->start = $this->start + $this->step) {
            yield implode(',', range($this->start, $this->start + $this->step - 1));
        }
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}