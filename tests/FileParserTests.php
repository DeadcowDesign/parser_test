<?php declare(strict_types=1);

require_once '..' . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'FileParser.php';
require_once 'PHPUnit.php';

class FileParserTests extends PHPUnit\Framework\TestCase
{   
        var $fileParser;
        private string $mockDataPath = "mockdata";

        // constructor of the test suite
        function ApiTest(string $name) :void{
            $this->PHPUnit_TestCase($name);
         }
     
         /**
          * setUp - instantiate our test object
          */
         protected function setUp() :void
         {
           parent::setUp();
           $this->fileParser = new \Application\FileParser();
         }
     
         // Reflection - allow access to any protected methods for testing
         protected static function getMethod(string $name) :object {
           $class = new ReflectionClass('\Application\FileParser');
           $method = $class->getMethod($name);
           $method->setAccessible(true);
           return $method;
         }


         // testGetDirectoryListing
         public function test_getDirectoryListing_returns_a_list_of_files()
         {
            $getDirectoryListing = self::getMethod('getDirectoryListing');
            $actual = $getDirectoryListing->invokeArgs($this->fileParser, array($this->mockDataPath));
            $this->assertIsArray($actual);
         }

         public function test_getDirectoryListing_only_returns_log_files()
         {
             $testPath = 'mockdata' . DIRECTORY_SEPARATOR . 'invalid-files';
             $expected = ['mockdata\\invalid-files\\mockfile-2.log', 'mockdata\\invalid-files\\mockfile.log'];
             $getDirectoryListing = self::getMethod('getDirectoryListing');
             $actual = $getDirectoryListing->invokeArgs($this->fileParser, array($testPath));
             $this->assertEquals($expected, $actual);
         }

         public function test_setIniSettings_loads_ini_file()
         {
            $setIniSettings = self::getMethod('setIniSettings');
            $actual = $setIniSettings->invokeArgs($this->fileParser, array());

            $this->assertIsArray($actual);
         }

}