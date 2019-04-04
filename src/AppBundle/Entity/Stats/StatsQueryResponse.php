<?php

namespace AppBundle\Entity\Stats;

use Symfony\Component\Validator\Constraints as Assert;

class StatsQueryResponse
{
    private $paNamedDeputyCount;

    private $profNamedDeputyCount;

    private $reportsCount;

    public function setPaNamedDeputyCount($paNamedDeputyCount)
    {
        $this->paNamedDeputyCount = $paNamedDeputyCount;
    }

    public function getPaNamedDeputyCount()
    {
        return $this->paNamedDeputyCount;
    }

    public function setProfNamedDeputyCount($ProfNamedDeputyCount)
    {
        $this->profNamedDeputyCount = $ProfNamedDeputyCount;
    }

    public function getProfNamedDeputyCount()
    {
        return $this->profNamedDeputyCount;
    }

    public function setReportsCount($reportsCount)
    {
        $this->reportsCount = $reportsCount;
    }

    public function getReportsCount()
    {
        return $this->reportsCount;
    }
}