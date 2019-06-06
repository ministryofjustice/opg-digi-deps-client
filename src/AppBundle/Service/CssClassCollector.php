<?php
namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class CssClassCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'govuk_count' => 16,
            'other_count' => 4,
            'govuk' => ['grehe', 'whwh', 'hehe'],
            'other' => ['grehe', 'whwh', 'hehe'],
        ];
    }

    public function reset()
    {
        $this->data = [];
    }

    public function getName()
    {
        return 'css-class-collector';
    }

    public function getData()
    {
        return $this->data;
    }
}
