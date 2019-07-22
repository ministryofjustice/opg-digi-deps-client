<?php

namespace AppBundle\Resources\views\Ndr;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\AssetOther;
use AppBundle\Entity\Ndr\AssetProperty;
use AppBundle\Entity\Ndr\BankAccount;
use AppBundle\Entity\Ndr\Debt;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Ndr\VisitsCare;
use AppBundle\Entity\User;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

/**
 * //TODO add more coverage
 */
class NdrFormattedTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;
    protected $report;
    protected $reportClient;
    protected $deputy;
    protected $decisions;
    protected $contacts;
    protected $twig;

    public function setUp()
    {
        $this->frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => false]);
        $request = new Request();
        $request->create('/');
        $this->container = $this->frameworkBundleClient->getContainer();
        $this->container->set('request', $request, 'request');
        $this->twig = $this->frameworkBundleClient->getContainer()->get('templating');
        $this->container->get('request_stack')->push(Request::createFromGlobals());

        $this->user = new User();
        $this->user
            ->setFirstname('John')
            ->setLastname('White');

        $this->client = new Client();
        $this->client
            ->setFirstname('Peter')
            ->setLastname('Jones')
            ->setCaseNumber('1234567t');
        $this->client->addUser($this->user);

        $this->visitsCare = new VisitsCare();

        $this->account1 = new BankAccount();
        $this->account1->setBank('barclays');
        $this->account2 = new BankAccount();

        $this->asset1 = new AssetOther();
        $this->asset1->setId(1)->setTitle('Artwork')->setDescription('monna lisa');
        $this->asset2 = new AssetOther();
        $this->asset2->setId(2)->setTitle('Antiques')->setDescription('chest of drawers');
        $this->assetProp = new AssetProperty();
        $this->assetProp->setAddress('plat house')->setPostcode('ha1')->setId(3)->setOwned(AssetProperty::OWNED_FULLY)->setValue(500000);
        $this->assetProp2 = new AssetProperty();
        $this->assetProp2->setAddress('victoria rd')->setPostcode('sw1')->setid(4)->setValue(100000)->setOwned(AssetProperty::OWNED_PARTLY)->setOwnedPercentage(60);

        $this->debt1 = new Debt('debt-type-id', 1.1, false, null);

        $this->ndr = new Ndr();
        $this->ndr
            ->setClient($this->client)
            ->setVisitsCare($this->visitsCare)
            ->setBankAccounts([$this->account1, $this->account2])
            ->setAssets([$this->asset1, $this->asset2, $this->assetProp, $this->assetProp2])
            ->setDebts([$this->debt1])
        ;

        $this->html = $this->twig->render('AppBundle:ndr/formatted:formatted.html.twig', [
            'ndr' => $this->ndr,
            'app' => ['user'=>$this->user] //mock twig app.user from the view
        ]);

        $this->crawler = new Crawler($this->html);

//        file_put_contents('/app/tests/ndr.html', $this->html);
    }

    private function html($crawler, $expr)
    {
        return $crawler->filter($expr)->eq(0)->html();
    }

    public function testLayout()
    {
        $this->assertEquals('New deputy report', $this->html($this->crawler, 'h1'));
    }

    public function testNdr()
    {
        $this->assertEquals('1234567t', $this->html($this->crawler, '#caseNumber'));
    }

    public function testDeputy()
    {
        $this->assertContains('John', $this->html($this->crawler, '#deputy-details-subsection'));
    }

    public function testClient()
    {
        $this->assertContains('Jones', $this->html($this->crawler, '#client-details-subsection'));
    }

    public function testBankAccount()
    {
        $this->assertContains('barclays', $this->html($this->crawler, '#account-summary'));
    }

    public function testAssets()
    {
        $this->assertContains('monna lisa', $this->html($this->crawler, '#assets-section'));
        $this->assertContains('chest of drawers', $this->html($this->crawler, '#assets-section'));
        $this->assertContains('plat house', $this->html($this->crawler, '#assets-section'));
        $this->assertContains('sw1', $this->html($this->crawler, '#assets-section'));
        $this->assertContains('£560,000.00', $this->html($this->crawler, '#assetsTotal', 'asset total must be 500k + 60% of 100k'));
    }

    public function tearDown()
    {
        m::close();
        unset($this->frameworkBundleClient);
    }
}
