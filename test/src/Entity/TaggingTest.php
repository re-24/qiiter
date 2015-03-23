<?php

namespace re24\Qiiter\Test\Entity;

use re24\Qiiter\Entity\Tagging;

class TaggingTest extends \PHPUnit_Framework_TestCase
{
    private $tagging;
    
    public function setUp()
    {
        $this->tagging = new Tagging();
    }
    
    public function taggingProvider()
    {
        return [
            [[
                'name' => 'example',
                'versions'=>[
                    "0.0.1",
                    "0.0.2",
                ]
            ]]
        ];
    }
    
    /**
     * @dataProvider taggingProvider
     */
    public function testExchangeArrayGetArray($data)
    {
        $this->tagging->arrayToEntity($data);
        $this->assertEquals($data, $this->tagging->getArray());
    }
    
    public function testSetGetProperty()
    {
        $expect = 'test_id';
        $this->tagging->name = $expect;
        
        $this->assertEquals($expect, $this->tagging->name);
    }
    
    /**
     * @expectedException re24\Qiiter\Exception\EntityException
     * @expectedExceptionMessage re24\Qiiter\Entity\Tagging does not contain a property by the name of "test"
     */
    public function testNotContainPropertyGet()
    {
        $temp = $this->tagging->test;
        echo $temp.'not print';
    }
    
    /**
     * @expectedException \re24\Qiiter\Exception\EntityException
     * @expectedExceptionMessage re24\Qiiter\Entity\Tagging does not contain a property by the name of "test"
     */
    public function testNotContainPropertySet()
    {
        $this->tagging->test='AAA';
    }
}
