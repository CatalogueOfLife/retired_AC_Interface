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
}