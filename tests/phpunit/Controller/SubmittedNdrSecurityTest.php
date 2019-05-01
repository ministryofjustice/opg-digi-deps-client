<?php
namespace App\Tests;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Service\Client\RestClient;
use MockeryStub as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SubmittedNdrSecurityTest extends WebTestCase
{
    /**
     * @var RestClient
     */
    private $restClient;

    public function setUp()
    {
        $this->restClient = m::mock(RestClient::class);
        $this->client = static::createClient();

        $this->login();

        // Mock NDR 12 as a new report
        $this->restClient
            ->shouldReceive('get')
            ->with('ndr/12', 'Ndr\Ndr', \Mockery::any())
            ->andReturn(new Ndr());

        // Mock NDR 13 as a submitted report
        $submittedNdr = new Ndr();
        $submittedNdr->setSubmitted(true);

        $this->restClient
            ->shouldReceive('get')
            ->with('ndr/13', 'Ndr\Ndr', \Mockery::any())
            ->andReturn($submittedNdr);
    }

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        // Ensure URL returns redirect
        $this->client->getContainer()->set('rest_client', $this->restClient);
        $this->client->request('GET', '/ndr/12/' . $url);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsUnsuccessful($url)
    {
        // Ensure URL returns error
        $this->client->getContainer()->set('rest_client', $this->restClient);
        $this->client->request('GET', '/ndr/13/' . $url);

        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }

    public function urlProvider()
    {
        yield ['visits-care/summary'];
        yield ['deputy-expenses/summary'];
        yield ['income-benefits/summary'];
        yield ['bank-accounts/summary'];
        yield ['assets/summary'];
        yield ['debts/summary'];
        yield ['actions/summary'];
        yield ['any-other-info/summary'];
    }


    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewallName = 'secure_area';
        $firewallContext = 'secured_area';

        $token = new UsernamePasswordToken('admin', null, $firewallName, ['ROLE_LAY_DEPUTY']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
