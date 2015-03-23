<?php

namespace re24\Qiiter\Test\Entity;

use re24\Qiiter\Entity\Tag;

class TagTest extends \PHPUnit_Framework_TestCase
{
    private $tag;
    
    public function setUp()
    {
        $this->tag = new Tag();
    }
    
    public function tagProvider()
    {
        return [
            [[
                'followers_count' => 100,
                'icon_url' => 'https://s3-ap-northeast-1.amazonaws.com/qiita-tag-image/9de6a11d330f5694820082438f88ccf4a1b289b2/medium.jpg',
                'id' => 'qiita',
                'items_count' => 200
            ]]
        ];
    }
    
    /**
     * @dataProvider tagProvider
     */
    public function testExchangeArrayGetArray($data)
    {
        $this->tag->arrayToEntity($data);
        $this->assertEquals($data, $this->tag->getArray());
    }
    
    public function testSetGetProperty()
    {
        $expect = 'test_id';
        $this->tag->id = $expect;
        
        $this->assertEquals($expect, $this->tag->id);
    }
    
    /**
     * @expectedException re24\Qiiter\Exception\EntityException
     * @expectedExceptionMessage re24\Qiiter\Entity\Tag does not contain a property by the name of "test"
     */
    public function testNotContainPropertyGet()
    {
        $temp = $this->tag->test;
        echo $temp.'not print';
    }
    
    /**
     * @expectedException \re24\Qiiter\Exception\EntityException
     * @expectedExceptionMessage re24\Qiiter\Entity\Tag does not contain a property by the name of "test"
     */
    public function testNotContainPropertySet()
    {
        $this->tag->test='AAA';
    }
}
