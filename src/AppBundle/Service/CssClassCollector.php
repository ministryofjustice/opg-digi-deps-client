<?php
namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class CssClassCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $content = $response->getContent();
        $govukClasses = [];
        $otherClasses = [];

        preg_match_all('/class=("|\')(.+?)\1/', $response->getContent(), $matches);

        foreach($matches[2] as $classList) {
            foreach(explode(' ', $classList) as $className) {
                if ($className === '') continue;

                if (substr($className, 0, 6) === 'govuk-') {
                    $counter = &$govukClasses;
                } else {
                    $counter = &$otherClasses;
                }

                if (array_key_exists($className, $counter)) {
                    $counter[$className]++;
                } else {
                    $counter[$className] = 1;
                }
            }
        }

        arsort($govukClasses);
        arsort($otherClasses);

        $this->data = [
            'govuk' => $govukClasses,
            'other' => $otherClasses,
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
