<?php
/**
 * Annual Checklist Interface
 *
 * Class WebservicesControllerTest
 * Unit tests to the webservices controller
 *
 * @category    ACI
 * @package     tests
 * @subpackage  controllers
 *
 */
class WebservicesControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp ()
    {
        parent::setUp();
        $this->frontController
             ->addControllerDirectory(APPLICATION_PATH . '/controllers');
    }
    
    public function testDefaultAction()
    {
        $this->dispatch('/webservices');
        $this->assertController('webservices');
        $this->assertAction('query');
    }
    
    public function testDefaultResponse()
    {
        /*<results
         *     id=""
         *     name=""
         *     total_number_of_results="0"
         *     start="0"
         *     number_of_results_returned="0"
         *     error_message="No name or ID given"
         *     version="1.0">
         *</results>*/
        $this->dispatch('/webservices');
        $this->assertXpathCount('//results', 1);
        $this->assertXpath("//results[@id = '']");
        $this->assertXpath("//results[@name = '']");
        $this->assertXpath("//results[@total_number_of_results = '0']");
        $this->assertXpath("//results[@start = '0']");
        $this->assertXpath("//results[@number_of_results_returned = '0']");
        $this->assertXpath("//results[@error_message = 'No name or ID given']");
        $this->assertXpath("//results[@version = '1.0']");
    }
    
    public function testEmptySerializedResponse()
    {
        $expectedResponse = array(
            'id' => '',
            'name' => '',
            'total_number_of_results' => 0,
            'start' => 0,
            'number_of_results_returned' => 0,
            'error_message' => 'No name or ID given',
            'version' => '1.0'
        );
        $this->dispatch('/webservices/?format=php');
        $this->assertEquals(
            array_diff_assoc(
                unserialize($this->getResponse()->getBody()),
                $expectedResponse
            ), array()
        );
    }
    
    public function testNameAndIdGivenIsError()
    {
        $this->dispatch('/webservices/?id=1&name=Animalia');
        $this->assertXpath("//results[@id = '1']");
        $this->assertXpath("//results[@name = 'Animalia']");
        $this->assertXpath(
            "//results[@error_message = " .
            "'Both name and ID are given. Give either a name or an ID']"
        );
    }
    
    public function testNameTooShortIsError()
    {
        $this->dispatch('/webservices/?name=xx*');
        $this->assertXpath(
            "//results[@error_message = " .
            "'Invalid name given. The name given must consist of at least 3 " .
            "characters, not counting wildcards (*)']"
        );
    }
    
    public function testStringIdIsError()
    {
        $this->dispatch('/webservices/?id=foo');
        $this->assertXpath(
            "//results[@error_message = " .
            "'Invalid ID given. The ID must be a positive integer']"
        );
    }
    
    public function testNegativeIdIsError()
    {
        $this->dispatch('/webservices/?id=-1');
        $this->assertXpath(
            "//results[@error_message = " .
            "'Invalid ID given. The ID must be a positive integer']"
        );
    }
    
    public function testInvalidResponseFormat()
    {
        $invalidResponseFormat = 'xxx';
        $this->dispatch('/webservices/?id=1&response=' . $invalidResponseFormat);
        $this->assertXpath(
            "//results[@error_message = " .
            "'Unknown response format: " . $invalidResponseFormat . "']"
        );
    }
    
    public function testNoNamesFound()
    {
        $this->dispatch('/webservices/?name=nonexistantname');
        $this->assertXpath(
            "//results[@error_message = " .
            "'No names found']"
        );
    }
    
    public function testStartParam()
    {
        $start = 1;
        $this->dispatch('/webservices/?id=0&start=' . $start);
        $xml = simplexml_load_string($this->getResponse()->getBody());
        $results = $xml->xpath("//results");
        $this->assertEquals(count($results), 1);
        $this->assertTrue($results[0] instanceof SimpleXMLElement);
        $this->assertXpath("//results[@start = '" . $start . "']");
        $this->assertXpath(
            "//results[@number_of_results_returned = '" .
            ($results[0]['total_number_of_results'] - $start) .
            "']"
        );
    }
}