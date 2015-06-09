<?php
namespace AppBundle\EventListener;

use Mockery as m;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;

/**
 * Act on session on each request
 * 
 */
class SessionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->event = m::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        $this->router = m::mock('Symfony\Bundle\FrameworkBundle\Routing\Router');
        $this->memcached = m::mock('\Memcached');
        $this->memcached->shouldReceive('flush')->andReturn(null);
    }
    
    
    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function onKernelRequestNoMasterWrongCtor()
    {
       new SessionListener($this->router, ['idleTimeout' => 0], $this->memcached);
    }
    
    /**
     * @test
     */
    public function onKernelRequestNoMasterReq()
    {
        $object = new SessionListener($this->router, ['idleTimeout'=>600], $this->memcached);
        
        
        $this->event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::SUB_REQUEST);
        $this->assertEquals('no-master-request', $object->onKernelRequest($this->event));
        
    }
    
     /**
     * @test
     */
    public function onKernelRequestNoSession()
    {
        $object = new SessionListener($this->router, ['idleTimeout'=>600], $this->memcached);
        
        $event = m::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MASTER_REQUEST);
        $event->shouldReceive('getRequest->hasSession')->andReturn(false);
        $this->assertEquals('no-session', $object->onKernelRequest($event));
        
    }
    
    /**
     * @test
     */
    public function onKernelRequestNoLastUsed()
    {
        $object = new SessionListener($this->router, ['idleTimeout'=>600], $this->memcached);
        
        $event = m::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        
        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MASTER_REQUEST);
        $event->shouldReceive('getRequest->hasSession')->andReturn(true);
         
        $event->shouldReceive('getRequest->getSession->getMetadataBag->getLastUsed')->andReturn(0);
        $this->assertEquals('no-timeout', $object->onKernelRequest($event));
        
    }
    
    
    public static function provider()
    {
        return [
            [1500, 0, 0],
            [1500, -10, 0],
            [1500, -1490, 0], //close to epire
            
            [1500, -1500 - 10, 1], // expired 10 sec ago
            [1500, -1500 - 25 * 3600, 1], //expired 25h ago
        ];
    }
    
    /**
     * @test
     * @dataProvider provider
     */
    public function onKernelRequest($idleTimeout, $lastUsedRelativeToCurrentTime, $callsToManualExpire)
    {
        $object = new SessionListener($this->router, ['idleTimeout'=>$idleTimeout], $this->memcached);
        
        $this->event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MASTER_REQUEST);
        $this->event->shouldReceive('getRequest->hasSession')->andReturn(true);
         
        $this->event->shouldReceive('getRequest->getSession->getMetadataBag->getLastUsed')->andReturn(time() + $lastUsedRelativeToCurrentTime);
        
        // expectations
        $this->event->shouldReceive('getRequest->getSession->invalidate')->times($callsToManualExpire);
        $this->event->shouldReceive('getRequest->getSession->set')->times($callsToManualExpire)->with('_security.secured_area.target_path', 'URI');
        $this->event->shouldReceive('getRequest->getSession->set')->times($callsToManualExpire)->with('loggedOutFrom', 'timeout');
        
        $this->event->shouldReceive('setResponse')->times($callsToManualExpire)->with(m::type('Symfony\Component\HttpFoundation\RedirectResponse'));
        $this->event->shouldReceive('stopPropagation')->times($callsToManualExpire);
        $this->router->shouldReceive('generate')->with('login')->times($callsToManualExpire)->andReturn('/login/timeout');
        
        $this->event->shouldReceive('getRequest->getUri')->times($callsToManualExpire)->andReturn('URI');
        
        $object->onKernelRequest($this->event);
    }
    
    public function tearDown()
    {
        m::close();
    }
}