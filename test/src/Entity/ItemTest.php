<?php

namespace re24\Qiiter\Test\Entity;

use re24\Qiiter\Entity\Item;

class ItemTest extends \PHPUnit_Framework_TestCase
{
	private $item;
	
	public function setUp()
	{
		$this->item = new Item();
	}
	
	public function itemProvider()
	{
		return [
			[[
				'body' => "# Example",
				'coediting' => false,
				'created_at' => '2014-09-04T09:54:49+00:00',
				'id' => '4bd431809afb1bb99e4f',
				'private' => false,
				'rendered_body' => '<h1>Example</h1>',
				'tags' =>  [
					[
						'name' => 'test1',
						'versions' => ['1.0','<=']
					],
					[
						'name' => 'test2',
						'versions' => ['2.0','=']
					]
					
				],
				'title' => 'hello world',
				'updated_at' => '2014-09-04T09:54:49+00:00',
				'url' => 'https://qiita.com/yaotti/items/4bd431809afb1bb99e4f',
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
	 * @dataProvider  itemProvider
	 */
	public function testExchangeArrayGetArray($data) 
	{
		// 定義されていない値が配列にあっても無視する
		$added_data = $data;
		$added_data['dummy'] = 'このプロパティは無視される';
		$this->item->arrayToEntity($added_data);
		
		for($i=0;$i<count($this->item->tags);$i++) {
			$tag_entity = $this->item->tags[$i];
			$tag_array = $data['tags'][$i];
			$this->assertInstanceOf('re24\Qiiter\Entity\Tagging', $this->item->tags[$i]);
			$this->assertEquals($tag_entity->getArray(), $tag_array);
		}
		$this->assertInstanceOf('re24\Qiiter\Entity\User', $this->item->user);
		$this->assertEquals($data, $this->item->getArray());
	}
	
	public function testSetGetProperty()
	{
		$expect = 'test_id';
		$this->item->id = $expect;
		$this->assertEquals($expect, $this->item->id);
	}
	
	/**
	 * @expectedException re24\Qiiter\Exception\EntityException
	 * @expectedExceptionMessage re24\Qiiter\Entity\Item does not contain a property by the name of "test"
	 */
	public function testNotContainPropertyGet()
	{
		$temp = $this->item->test;
		echo $temp.'not print';
	}
	
	/**
	 * @expectedException \re24\Qiiter\Exception\EntityException
	 * @expectedExceptionMessage re24\Qiiter\Entity\Item does not contain a property by the name of "test"
	 */
	public function testNotContainPropertySet()
	{
		$this->item->test='AAA';
	}
	
}