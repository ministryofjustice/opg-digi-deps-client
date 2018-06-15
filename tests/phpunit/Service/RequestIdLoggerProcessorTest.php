<?php

namespace AppBundle\Service;

use Mockery as m;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestIdLoggerProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestIdLoggerProcessor
     */
    private $object;

    private $record = ['key1' => 'abc', 'key2' => 2];

    public function setUp()
    {
        $this->container = m::mock(Container::class);
        $this->reqStack = m::mock(RequestStack::class);
        $this->request = m::mock(Request::class);

        $this->object = new RequestIdLoggerProcessor($this->container);
    }

    public function testProcessRecordNoReqStack()
    {
        $this->container->shouldReceive('get')->with('request_stack')->andReturn(false);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequest()
    {
        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn(false);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequestId()
    {
        $request = new Request();
        $request->headers = new ParameterBag();

        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $this->reqStack->shouldReceive('has')->with('x-request-id')->andReturn(false);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasRequestId()
    {
        $request = new Request();
        $request->headers = new ParameterBag();
        $request->headers->set('x-request-id', 'THIS_IS_THE_REQUEST_ID');

        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $this->reqStack->shouldReceive('has')->with('x-request-id')->andReturn(true);
        $this->reqStack->shouldReceive('get')->with('x-request-id')->andReturn('THIS_IS_THE_REQUEST_ID');
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record + ['extra' => ['request_id' => 'THIS_IS_THE_REQUEST_ID']], $this->object->processRecord($this->record));
    }

    public function tearDown()
    {
        m::close();
    }
}
