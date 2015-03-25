<?php

namespace re24\Qiiter\Test\Entity;

use re24\Qiiter\Entity\Comment;

class CommentTest extends \PHPUnit_Framework_TestCase
{
    private $comment;
    
    public function setUp()
    {
        $this->comment = new Comment();
    }
    
    public function commentProvider()
    {
        return [
            [[
                'body' => "# Example",
                'created_at' => '2014-09-04T09:54:49+00:00',
                'id' => '4bd431809afb1bb99e4f',
                'rendered_body' => '<h1>Example</h1>',
                'updated_at' => '2014-09-04T09:54:49+00:00',
                'user' => [
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
                ]
            ]]
        ];
    }

    /**
     * @dataProvider  commentProvider
     */
    public function testExchangeArrayGetArray($data)
    {
        $this->comment->arrayToEntity($data);
        
        $user = $this->comment->user;
        $this->assertInstanceOf('re24\Qiiter\Entity\User', $user);
        $this->assertEquals($data['user'], $user->getArray());
        $this->assertEquals($data, $this->comment->getArray());
    }
    
    public function testSetGetProperty()
    {
        $expect = 'test_id';
        $this->comment->id = $expect;
        $this->assertEquals($expect, $this->comment->id);
    }
    
    /**
     * @expectedException re24\Qiiter\Exception\EntityException
     * @expectedExceptionMessage re24\Qiiter\Entity\Comment does not contain a property by the name of "test"
     */
    public function testNotContainPropertyGet()
    {
        $temp = $this->comment->test;
        echo $temp.'not print';
    }
    
    /**
     * @expectedException \re24\Qiiter\Exception\EntityException
     * @expectedExceptionMessage re24\Qiiter\Entity\Comment does not contain a property by the name of "test"
     */
    public function testNotContainPropertySet()
    {
        $this->comment->test='AAA';
    }
}
