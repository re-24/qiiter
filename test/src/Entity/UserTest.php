<?php
namespace re24\Qiiter\Test\Entity;

use re24\Qiiter\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    private $user;
    
    public function setUp()
    {
        $this->user = new User();
    }
    
    public function userProvider()
    {
        return [
            [[
                   'description' =>'Qiita, Qiita:Team(RoR)やKobito(Objective-C)の開発をしています．',
                   'facebook_id' => 'yaotti',
                   'followees_count' => 118,
                   'followers_count' => 181,
                   'github_login_name' => 'yaotti_git',
                   'id' => 'yaotti_id',
                   'items_count' => 101,
                   'linkedin_id' => 'yaotti_linked',
                   'location' => 'Tokyo, Japan',
                   'name' => 'Hiroshige Umino',
                   'organization' => 'Increments Inc',
                   'profile_image_url' => 'https://si0.twimg.com/profile_images/2309761038',
                   'twitter_screen_name' => 'yaotti_twitter',
                   'website_url' => 'http://yaotti.hatenablog.com',
            ]]
        ];
    }
    
    /**
     * @dataProvider  userProvider
     */
    public function testExchangeArrayGetArray($data)
    {
        $this->user->arrayToEntity($data);
        $this->assertEquals($data, $this->user->getArray());
    }
    
    public function testSetGetProperty()
    {
        $expect = 'test_id';
        $this->user->id = $expect;
        
        $this->assertEquals($expect, $this->user->id);
    }
    
    /**
     * @expectedException re24\Qiiter\Exception\EntityException
     * @expectedExceptionMessage re24\Qiiter\Entity\User does not contain a property by the name of "test"
     */
    public function testNotContainPropertyGet()
    {
        $temp = $this->user->test;
        echo $temp.'not print';
    }
    
    /**
     * @expectedException \re24\Qiiter\Exception\EntityException
     * @expectedExceptionMessage re24\Qiiter\Entity\User does not contain a property by the name of "test"
     */
    public function testNotContainPropertySet()
    {
        $this->user->test='AAA';
    }
}
