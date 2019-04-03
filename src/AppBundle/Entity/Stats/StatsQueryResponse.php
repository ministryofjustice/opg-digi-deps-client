<?php

namespace AppBundle\Entity\Stats;

use Symfony\Component\Validator\Constraints as Assert;

class StatsQueryResponse
{
    private $deputyCount;

    public function setDeputyCount($deputyCount)
    {
        $this->deputyCount = $deputyCount;
    }

    public function toJson()
}