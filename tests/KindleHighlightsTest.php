<?php
/**
 * Test Suite for the KindleHighlights Class
 *
 * @author jphayes
 */
class KindleHighlightsTest extends PHPUnit_Framework_TestCase {
    public $kindle;
    
    public function setup() {
        require('../KindleHighlights.php');
    }
    
    public function testGetLoginPage() {
        $this->kindle = new KindleHighlights('someusername', 'somepassword');
        $this->kindle->getKindleLoginPage();
        $this->assertNotEmpty($this->kindle->loginPage);
    }
}