<?php
/**
 * Annual Checklist Interface
 *
 * Class SearchControllerTest
 * Unit tests to the search controller
 *
 * @category    ACI
 * @package     tests
 * @subpackage  controllers
 *
 */
class SearchControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp ()
    {
        parent::setUp();
        $this->frontController
             ->addControllerDirectory(APPLICATION_PATH . '/controllers');
    }
    
    /**
     * Search controller redirects requests with no action to search/all
     */
    public function testDefaultSearchPageIsASuccessfulRequestToSearchAll()
    {
        $this->dispatch('/search');
        $this->assertFalse($this->response->isException());
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertController('search');
        $this->assertAction('all');
    }
    
    public function testDefaultInfoAction()
    {
        $this->dispatch('/search/dummy');
        $this->assertController('search');
        $this->assertAction('all');
    }
    
    public function testSearchAllContainsTheNeededFormElements()
    {
        $this->dispatch('/search/all');
        $this->assertQueryCount('form#searchForm', 1);
        $this->assertQueryCount('input#key', 1);
        $this->assertQueryCount('input#match', 1);
        $this->assertQueryCount('input#search', 1);
    }
    
    public function testSearchCommonContainsTheNeededFormElements()
    {
        $this->dispatch('/search/common');
        $this->assertQueryCount('form#searchForm', 1);
        $this->assertQueryCount('input#key', 1);
        $this->assertQueryCount('input#match', 1);
        $this->assertQueryCount('input#search', 1);
    }
    
    public function testSearchDistributionContainsTheNeededFormElements()
    {
        $this->dispatch('/search/distribution');
        $this->assertQueryCount('form#searchForm', 1);
        $this->assertQueryCount('input#key', 1);
        $this->assertQueryCount('input#match', 1);
        $this->assertQueryCount('input#search', 1);
    }
    
    public function testSearchFormIsNotSubmitted()
    {
        $this->dispatch('/search/all/key/aa/submit/0');
        $this->assertQueryCount('form#searchForm', 1);
    }
    
    public function testFormValidationErrorKeyTooShort()
    {
        $this->dispatch('/search/all/key/x/match/1');
        $this->assertQueryCountMin('div.errors', 1);
        $this->dispatch('/search/common/key/x/match/1');
        $this->assertQueryCountMin('div.errors', 1);
        $this->dispatch('/search/distribution/key/x/match/1');
        $this->assertQueryCountMin('div.errors', 1);
    }
    
    public function testFormValidationErrorNoKey()
    {
        $this->dispatch('/search/all/key//match/1');
        $this->assertQueryCountMin('div.errors', 1);
        $this->dispatch('/search/common/key//match/1');
        $this->assertQueryCountMin('div.errors', 1);
        $this->dispatch('/search/distribution/key//match/1');
        $this->assertQueryCountMin('div.errors', 1);
    }
    
    public function testFormValidationErrorOnlyWildcards()
    {
        $this->dispatch('/search/all/key/**/match/1');
        $this->assertQueryCountMin('div.errors', 1);
        $this->dispatch('/search/common/key/**/match/1');
        $this->assertQueryCountMin('div.errors', 1);
        $this->dispatch('/search/distribution/key/**/match/1');
        $this->assertQueryCountMin('div.errors', 1);
    }
}