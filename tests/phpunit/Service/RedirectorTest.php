<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use MockeryStub as m;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RedirectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Redirector
     */
    protected $object;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var Session
     */
    protected $session;

    public function setUp()
    {
        $this->user = m::mock(User::class);
        $this->tokenStorage = m::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $this->tokenStorage->shouldReceive('getToken->getUser')->andReturn($this->user);
        $this->router = m::mock('Symfony\Component\Routing\RouterInterface')
            ->shouldReceive('generate')->andReturnUsing(function ($route, $params = []) {
                return [$route, $params];
            })->getMock();
        $this->session = m::mock('Symfony\Component\HttpFoundation\Session\Session');

        $this->tokenStorage->shouldReceive('getToken->getUser')->andReturn($this->user);

        $this->authChecker = m::mock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');

        $this->object = new Redirector($this->tokenStorage, $this->authChecker, $this->router, $this->session, 'prod');
    }

    public static function firstPageAfterLoginProvider()
    {
        $clientWithDetails = m::mock(Client::class, ['hasDetails'=>true]);
        $clientWithoutDetails = m::mock(Client::class)->shouldReceive('hasDetails')->andReturn(false)->getMock();

        return [
           ['ROLE_ADMIN', [], ['admin_homepage', []]],
           ['ROLE_LAY_DEPUTY', ['hasDetails'=>false], ['user_details', []]],
           ['ROLE_LAY_DEPUTY', ['hasDetails'=>true, 'getIdOfClientWithDetails'=>null], ['client_add', []]],
           ['ROLE_LAY_DEPUTY', ['hasDetails'=>true, 'getIdOfClientWithDetails'=>1, 'getActiveReportId'=>1], ['report_overview', ['reportId'=>1]]],
           ['ROLE_LAY_DEPUTY', ['hasDetails'=>true, 'getIdOfClientWithDetails'=>1, 'getActiveReportId'=>null], ['ndr_index', []]],
        ];
    }

    /**
     * @dataProvider firstPageAfterLoginProvider
     */
    public function testgetFirstPageAfterLogin($grantedRole, $userMocks, $expectedRouteAndParams)
    {
        $this->markTestIncomplete('fix when specs are 100% defined');

        $this->authChecker->shouldIgnoreMissing();
        $this->authChecker->shouldReceive('isGranted')->with($grantedRole)->andReturn(true);
        foreach ($userMocks as $k => $v) {
            $this->user->shouldReceive($k)->andReturn($v);
        }

        $actual = $this->object->getFirstPageAfterLogin(false);
        $this->assertEquals($actual, $expectedRouteAndParams);
    }

    public function tearDown()
    {
        m::close();
    }
}
