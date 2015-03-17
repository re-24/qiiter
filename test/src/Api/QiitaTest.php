<?php

namespace re24\Qiiter\Test\Api;

use re24\Qiiter\Api\Qiita;
use re24\Qiiter\Authorization\AccessToken;
use re24\Qiiter\Test\Provider\Provider;
use re24\Qiiter\Entity\Item;
use re24\Qiiter\Entity\User;
use re24\Qiiter\Entity\Comment;
use re24\Qiiter\Entity\Tag;

class QiitaTest extends TestCase
{
	public function testGetMyProfiles()
	{
		// 準備
		$response = [
			['code'=>201,'file'=>'user_getone.json'], // myuser info
			['code'=>201,'file'=>'item_getlist.json'], // my post item
		];
		
		// モック作成
		$client = $this->createMockClient($response);
	}
	
	
	public function testSendItem()
	{
		// 準備
		$request = json_decode(Provider::getProviderData('item_send.json'), true);
		$response = [
			['code'=>201,'file'=>'item_getone.json'], // post
			['code'=>200,'file'=>'item_getone.json'], // update
			['code'=>204,'string'=>null],
		];
			
		// モック作成
		$client = $this->createMockClient($response);
		
		$item = new Item();
		$item->arrayToEntity($request);
		
		// api実行
		$api = new Qiita(null, $client);
		$item_post =  $api->postItem($item);	// 新規投稿
		$item_update = $api->updateItem($item); // 記事更新
		$item_delete = $api->deleteItem($item); // 記事削除
		
		// チェック
		$this->assertEquals('POST', $this->getRequestMethod(0));
		$this->assertEquals('/api/v2/items', $this->getRequestUrlPath(0));
		$this->assertEquals($request, $this->getRequestPostJson(0));
		$this->assertInstanceOf('re24\Qiiter\Entity\Item', $item_post);
		$this->assertEquals($this->getResponseBodyToArray(0),  $item_post->getArray());
		
		$this->assertEquals('PATCH', $this->getRequestMethod(1));
		$this->assertEquals('/api/v2/items/'.$item->id, $this->getRequestUrlPath(1));
		$this->assertEquals($request, $this->getRequestPostJson(1));
		$this->assertInstanceOf('re24\Qiiter\Entity\Item', $item_update);
		$this->assertEquals($this->getResponseBodyToArray(1),  $item_update->getArray());
		
		$this->assertEquals('DELETE', $this->getRequestMethod(2));
		$this->assertEquals('/api/v2/items/'.$item->id, $this->getRequestUrlPath(2));
		$this->assertInstanceOf('GuzzleHttp\Message\Response' , $item_delete);
	}
	
	public function testGetItems()
	{
		// 準備
		$response = [
			['code'=>200,'file'=>'item_getone.json'], // 記事指定
			['code'=>200,'file'=>'item_getlist.json'], // ユーザ指定
			['code'=>200,'file'=>'item_getlist.json'], // タグ指定
			['code'=>200,'file'=>'item_getlist.json'], // 指定ユーザのストック指定
			['code'=>200,'file'=>'item_getlist.json'], // 文字列検索
		];
			
		// モック作成
		$client = $this->createMockClient($response);
		
		// Api実行
		$item_id = '12345678';
		$user_id = '23457890';
		$tag_id = '345678901';
		$query = 'test_query';
		
		$api = new Qiita(null, $client);
		$from_id = $api->getItem($item_id); // 記事指定
		$from_user = $api->getUserItems($user_id); // ユーザ指定
		$from_tag = $api->getTagItems($tag_id); // タグ指定
		$from_stock = $api->getStockItems($user_id); // 指定ユーザのストック指定
		$from_query = $api->getQueryItems($query); // 文字列検索
	
		// チェック
		$this->assertEquals('GET', $this->getRequestMethod(0));
		$this->assertEquals('/api/v2/items/'.$item_id, $this->getRequestUrlPath(0));
		$this->assertInstanceOf('re24\Qiiter\Entity\Item', $from_id);
		$this->assertEquals($this->getResponseBodyToArray(0), $from_id->getArray());

		$this->assertEquals('GET', $this->getRequestMethod(1));
		$this->assertEquals('/api/v2/users/'.$user_id.'/items', $this->getRequestUrlPath(1));
		for($i=0; $i<count($from_user);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\Item', $from_user[$i]);
			$this->assertEquals($this->getResponseBodyToArray(1)[$i],$from_user[$i]->getArray());
		}

		$this->assertEquals('GET', $this->getRequestMethod(2));
		$this->assertEquals('/api/v2/tags/'.$tag_id.'/items', $this->getRequestUrlPath(2));
		for($i=0; $i<count($from_tag);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\Item', $from_tag[$i]);
			$this->assertEquals($this->getResponseBodyToArray(2)[$i],$from_tag[$i]->getArray());
		}
		
		$this->assertEquals('GET', $this->getRequestMethod(3));
		$this->assertEquals('/api/v2/users/'.$user_id.'/stocks', $this->getRequestUrlPath(3));
		for($i=0; $i<count($from_stock);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\Item', $from_stock[$i]);
			$this->assertEquals($this->getResponseBodyToArray(3)[$i],$from_stock[$i]->getArray());
		}
		
		$this->assertEquals('GET', $this->getRequestMethod(4));
		$this->assertEquals('/api/v2/items', $this->getRequestUrlPath(4));
		$this->assertEquals($query ,$this->getRequestUrlQuery(4)['query']);
		for($i=0; $i<count($from_query);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\Item', $from_query[$i]);
			$this->assertEquals($this->getResponseBodyToArray(4)[$i],$from_query[$i]->getArray());
		}
	}
	
	public function testGetMyInformations()
	{
		// 準備
		$response = [
			['code'=>201,'file'=>'user_getone.json'], // get my profiles 
			['code'=>201,'file'=>'item_getlist.json'], // get my items 
		];
					
		// モック作成
		$client = $this->createMockClient($response);
		
		$token_str = 'ABCDEFGHIJKLMN';
		$token = new AccessToken();
		$token->setAccseeToken($token_str);
		
		// api実行
		$api = new Qiita($token, $client);
		$user_info = $api->getMyProfile();
		$user_post_items =$api->getMyItems();
		
		// チェック
		$this->assertEquals('Bearer '.$token_str,
							$this->getRequest(0)->getHeader('Authorization'));
		$this->assertEquals('GET', $this->getRequestMethod(0));
		$this->assertEquals('/api/v2/authenticated_user', $this->getRequestUrlPath(0));
		$this->assertInstanceOf('re24\Qiiter\Entity\User', $user_info);
		$this->assertEquals($this->getResponseBodyToArray(0), $user_info->getArray());
		
		$this->assertEquals('GET',$this->getRequestMethod(1));
		$this->assertEquals('/api/v2/authenticated_user/items', $this->getRequestUrlPath(1));
		for($i=0; $i<count( $user_post_items);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\Item',  $user_post_items[$i]);
			$this->assertEquals($this->getResponseBodyToArray(1)[$i], $user_post_items[$i]->getArray());
		}
	}
	
	public function testStock()
	{
		$response = [
			['code' => 204],	// ストックしているかどうか
			['code' => 204],	// 投稿ストック
			['code' => 204],	// 投稿のストックをやめる	
		];
		$client = $this->createMockClient($response);
		
		$user = new User();
		$user->id = 'aaa';
		
		$api = new Qiita(null , $client);
		$is_stocked = $api->isStockedItem($user);
		$do_stock = $api->stockItem($user);
		$rm_stock = $api->stockItemCancel($user);

		$this->assertEquals('GET',$this->getRequestMethod(0));
		$this->assertEquals('/api/v2/items/'.$user->id.'/stock',$this->getRequestUrlPath(0));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $is_stocked);
		$this->assertEquals('PUT', $this->getRequestMethod(1));
		$this->assertEquals('/api/v2/items/'.$user->id.'/stock', $this->getRequestUrlPath(1));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $do_stock);
		$this->assertEquals('DELETE', $this->getRequestMethod(2));
		$this->assertEquals('/api/v2/items/'.$user->id.'/stock', $this->getRequestUrlPath(2));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $rm_stock);
		
	}
	
	public function testGetComments()
	{
		$response = [
			['code' => 200, 'file' => 'comment_getone.json'], // idでコメント取得
			['code' => 200, 'file' => 'comment_getlist.json'], // 投稿についたコメント一覧取得
		];
		$client = $this->createMockClient($response);
		
		$com_id = '1234567';
		$item_id = '2345678';
		
		$api = new Qiita(null , $client);
		$get_com = $api->getComment($com_id);
		$get_coms = $api->getItemComments($item_id);
		
		$this->assertEquals('GET', $this->getRequestMethod(0));
		$this->assertEquals('/api/v2/comments/'.$com_id, $this->getRequestUrlPath(0));
		$this->assertInstanceOf('re24\Qiiter\Entity\Comment', $get_com);
		$this->assertEquals($this->getResponseBodyToArray(0), $get_com->getArray());
		
		$this->assertEquals('GET', $this->getRequestMethod(1));
		$this->assertEquals('/api/v2/items/'.$item_id.'/comments', $this->getRequestUrlPath(1));
		for($i=0; $i<count( $get_coms);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\Comment',  $get_coms[$i]);
			$this->assertEquals($this->getResponseBodyToArray(1)[$i], $get_coms[$i]->getArray());
		}
	}
	
	public function testSendComment()
	{
		// 準備
		$request = json_decode(Provider::getProviderData('comment_send.json'), true);
		$response = [
			['code'=>201,'file'=>'comment_getone.json'], // post
			['code'=>200,'file'=>'comment_getone.json'], // update
			['code'=>204,'string'=>null],
		];
			
		// モック作成
		$client = $this->createMockClient($response);
		
		$comment = new Comment();
		$comment->arrayToEntity($request);
		$comment->id = 'abcdef';
		
		$item_id = '12345';
		$api = new Qiita(null, $client);
		$post_comm = $api->postComment($item_id, $comment);
		$update_comm = $api->updateComment($comment);
		$delete_comm = $api->deleteComment($comment);
		
		$this->assertEquals('POST', $this->getRequestMethod(0));
		$this->assertEquals('/api/v2/items/'.$item_id.'/comments', $this->getRequestUrlPath(0));
		$this->assertInstanceOf('re24\Qiiter\Entity\Comment', $post_comm);
		$this->assertEquals($this->getResponseBodyToArray(0), $post_comm->getArray());
		
		$this->assertEquals('PATCH', $this->getRequestMethod(1));
		$this->assertEquals('/api/v2/comments/'.$comment->id, $this->getRequestUrlPath(1));
		$this->assertInstanceOf('re24\Qiiter\Entity\Comment', $update_comm);
		$this->assertEquals($this->getResponseBodyToArray(1), $update_comm->getArray());
		
		$this->assertEquals('DELETE', $this->getRequestMethod(2));
		$this->assertEquals('/api/v2/comments/'.$comment->id, $this->getRequestUrlPath(2));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $delete_comm);
	}
	
	public function testThanksComment()
	{
		$response = [
			['code'=>204,'string'=>null], // thanksをつける
			['code'=>204,'string'=>null], // thanksをとりけす
		];
			
		$client = $this->createMockClient($response);
		
		$comment_id = '12345';
		
		$api = new Qiita(null, $client);
		$thanks = $api->thanksComent($comment_id);
		$thanks_cancel = $api->thanksCommentCancel($comment_id);
		
		$this->assertEquals('PUT', $this->getRequestMethod(0));
		$this->assertEquals('/api/v2/comments/'.$comment_id.'/thank', $this->getRequestUrlPath(0));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $thanks);
		
		$this->assertEquals('DELETE', $this->getRequestMethod(1));
		$this->assertEquals('/api/v2/comments/'.$comment_id.'/thank', $this->getRequestUrlPath(1));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $thanks_cancel);
	}
	
	public function testGetUsers()
	{
		$response = [
			['code'=>200, 'file'=>'user_getlist.json'], // すべてのユーザ
			['code'=>200, 'file'=>'user_getone.json'],  // ユーザ指定で
			['code'=>200, 'file'=>'user_getlist.json'], // 指定ユーザがフォローしているユーザ一覧
			['code'=>200, 'file'=>'user_getlist.json'], // 指定ユーザをフォローしているユーザ一覧
			['code'=>200, 'file'=>'user_getlist.json'], // 特定の記事をストックしているユーザ一覧
		];
		$client = $this->createMockClient($response);
		
		$user_id = 'aaa';
		$item_id = 'bbb';
		
		$api = new Qiita(null, $client);
		$all_user = $api->getUsers();
		$one_user = $api->getUser($user_id);
		$following_user = $api->getFollowingUser($user_id);
		$follwed_user = $api->getFollowedUser($user_id);
		$stocked_user = $api->getItemStockUsers($item_id);

		$this->assertEquals('GET', $this->getRequestMethod(0));
		$this->assertEquals('/api/v2/users', $this->getRequestUrlPath(0));
		for($i=0; $i<count( $all_user);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\User',  $all_user[$i]);
			$this->assertEquals($this->getResponseBodyToArray(0)[$i], $all_user[$i]->getArray());
		}
		
		$this->assertEquals('GET', $this->getRequestMethod(1));
		$this->assertEquals('/api/v2/users/'.$user_id, $this->getRequestUrlPath(1));
		$this->assertInstanceOf('re24\Qiiter\Entity\User', $one_user);
		$this->assertEquals($this->getResponseBodyToArray(1), $one_user->getArray());
		
		$this->assertEquals('GET', $this->getRequestMethod(2));
		$this->assertEquals('/api/v2/users/'.$user_id.'/followees', $this->getRequestUrlPath(2));
		for($i=0; $i<count( $following_user);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\User',  $following_user[$i]);
			$this->assertEquals($this->getResponseBodyToArray(2)[$i], $following_user[$i]->getArray());
		}
		
		$this->assertEquals('GET', $this->getRequestMethod(3));
		$this->assertEquals('/api/v2/users/'.$user_id.'/followers', $this->getRequestUrlPath(3));
		for($i=0; $i<count( $follwed_user);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\User',  $follwed_user[$i]);
			$this->assertEquals($this->getResponseBodyToArray(3)[$i], $follwed_user[$i]->getArray());
		}
		
		$this->assertEquals('GET', $this->getRequestMethod(4));
		$this->assertEquals('/api/v2/items/'.$item_id.'/stockers', $this->getRequestUrlPath(4));
		for($i=0; $i<count( $stocked_user);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\User',  $stocked_user[$i]);
			$this->assertEquals($this->getResponseBodyToArray(4)[$i], $stocked_user[$i]->getArray());
		}
	}
	
	public function testGetTags()
	{
		$response = [
			['code'=>200, 'file'=>'tag_getlist.json'], // すべてのタグ
			['code'=>200, 'file'=>'tag_getone.json'],  // 特定のタグ
			['code'=>200, 'file'=>'tag_getlist.json'], // フォローしているタグ
		];
		
		$client = $this->createMockClient($response);
		
		$tag_id ='aaa';
		$user_id = 'bbb';
		
		$api = new Qiita(null, $client);
		$all_tags = $api->getTags();
		$one_tag = $api->getTag($tag_id);
		$following_tag = $api->getUserFollowingTag($user_id);
		
		$this->assertEquals('GET', $this->getRequestMethod(0));
		$this->assertEquals('/api/v2/tags', $this->getRequestUrlPath(0));
		for($i=0; $i<count( $all_tags);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\Tag',  $all_tags[$i]);
			$this->assertEquals($this->getResponseBodyToArray(0)[$i], $all_tags[$i]->getArray());
		}
		
		$this->assertEquals('GET', $this->getRequestMethod(1));
		$this->assertEquals('/api/v2/tags/'.$tag_id, $this->getRequestUrlPath(1));
		$this->assertInstanceOf('re24\Qiiter\Entity\Tag', $one_tag);
		$this->assertEquals($this->getResponseBodyToArray(1), $one_tag->getArray());
		
		$this->assertEquals('GET', $this->getRequestMethod(2));
		$this->assertEquals('/api/v2/users/'.$user_id.'/following_tags', $this->getRequestUrlPath(2));
		for($i=0; $i<count( $following_tag);$i++) {
			$this->assertInstanceOf('re24\Qiiter\Entity\Tag',  $following_tag[$i]);
			$this->assertEquals($this->getResponseBodyToArray(2)[$i], $following_tag[$i]->getArray());
		}
	}

	public function testFollow()
	{
		$response = [
			['code' => 204],	// ユーザをフォローしているか
			['code' => 204],	// ユーザをフォローする
			['code' => 204],	// ユーザのフォローをやめる	
			['code' => 204],	// タグをフォローしているか
			['code' => 204],	// タグをフォローする
			['code' => 204],	// タグのフォローをやめる	
		];
		$client = $this->createMockClient($response);
		
		$user = new User();
		$user->id = 'aaa';
		
		$tag = new Tag();
		$tag->id = 'bbb';
		
		$api = new Qiita(null , $client);
		$is_userfollowed = $api->isFollowedUser($user->id);
		$user_followed = $api->followUser($user->id);
		$user_followed_cancel = $api->followUserCancel($user->id);
		$is_tagfollowed = $api->isFollowedTag($tag->id);
		$tag_followed = $api->followTag($tag->id);
		$tag_followed_cancel = $api->followTagCancel($tag->id);

		$this->assertEquals('GET',$this->getRequestMethod(0));
		$this->assertEquals('/api/v2/users/'.$user->id.'/following',$this->getRequestUrlPath(0));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $is_userfollowed);
		$this->assertEquals('PUT', $this->getRequestMethod(1));
		$this->assertEquals('/api/v2/users/'.$user->id.'/following', $this->getRequestUrlPath(1));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $user_followed);
		$this->assertEquals('DELETE', $this->getRequestMethod(2));
		$this->assertEquals('/api/v2/users/'.$user->id.'/following', $this->getRequestUrlPath(2));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $user_followed_cancel);
		
		$this->assertEquals('GET',$this->getRequestMethod(3));
		$this->assertEquals('/api/v2/tags/'.$tag->id.'/following',$this->getRequestUrlPath(3));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $is_tagfollowed);
		$this->assertEquals('PUT', $this->getRequestMethod(4));
		$this->assertEquals('/api/v2/tags/'.$tag->id.'/following', $this->getRequestUrlPath(4));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $tag_followed);
		$this->assertEquals('DELETE', $this->getRequestMethod(5));
		$this->assertEquals('/api/v2/tags/'.$tag->id.'/following', $this->getRequestUrlPath(5));
		$this->assertInstanceOf('GuzzleHttp\Message\Response', $tag_followed_cancel);
	}
	
	public function testSetPaginationQuery()
	{
		$api = new Qiita();
		$reflectionClass = new \ReflectionClass($api);
		$reflectionProperty = $reflectionClass->getProperty('paginaton_query');
		$reflectionProperty->setAccessible(true);
		
		$default_expect = ['page'=>1,'per_page'=>20];
		$default_pagination = $reflectionProperty->getValue($api);
		
		$setting_expect = ['page'=>3,'per_page'=>80];
		$api->setPaginationQuery(3,80);
		$setting_pagination = $reflectionProperty->getValue($api);

		$this->assertEquals($default_expect,$default_pagination);
		$this->assertEquals($setting_expect,$setting_pagination);
		
	}
	
	public function testSendSuccess()
	{
		$response = [
			['code'=>200],
			['code'=>200],
			['code'=>200],
		];
		$client = $this->createMockClient($response);
		
		$reflectionClass = new \ReflectionClass('re24\Qiiter\Api\Qiita');
		$method = $reflectionClass->getMethod('send');
		$method->setAccessible(true);
		
		$args1 = [
			'GET',
			'http://localhost/',
			null
		];
		$args2 = [
			'GET',
			'https://localhost/',
			null
		];
		$args3 = [
			'GET',
			'users',
			['query' => ['page'=>2, 'per_page' => 50, 'query' => 'AAABBBCCC']]
		];
		
		$api = new Qiita(null, $client);
		$method->invokeArgs($api, $args1);
		$method->invokeArgs($api, $args2);
		$method->invokeArgs($api, $args3);
		
		$this->assertEquals('http://localhost/', $this->getRequest(0)->getUrl());
		$this->assertEquals('https://localhost/', $this->getRequest(1)->getUrl());
		$this->assertEquals('http://qiita.com/api/v2/users?page=2&per_page=50&query=AAABBBCCC', $this->getRequest(2)->getUrl());
	}
		
	/**
	 * @expectedException re24\Qiiter\Exception\HttpException
	 */
	public function testSendHttpException()
	{
		$response = [
			['code'=>400,'string'=>'{"message":"Bad request","type":"bad_request"}'], 
		];
		
		$client = $this->createMockClient($response);
		
		$api = new Qiita(null, $client);
		
		$reflectionClass = new \ReflectionClass('re24\Qiiter\Api\Qiita');
		$method = $reflectionClass->getMethod('send');
		$method->setAccessible(true);
		
		$args = [
			'GET',
			'http://localhost/',
			null
		];
		$method->invokeArgs($api ,$args);
		
	}
	
	public function testGetEntityId()
	{
		$api = new Qiita();
		$reflectionClass = new \ReflectionClass($api);
		$reflectionMethod = $reflectionClass->getMethod('getEntityId');
		$reflectionMethod->setAccessible(true);
		
		$user = new User();
		$user->id = 'aaa';
		
		$item = new Item();
		$item->id = 'bbb';
		
		$tag = new Tag();
		$tag->id = 'ccc';
		
		$string = 'ddd';
		
		$user_id = $reflectionMethod->invokeArgs($api, [$user]);
		$item_id = $reflectionMethod->invokeArgs($api, [$item]);
		$tag_id = $reflectionMethod->invokeArgs($api, [$tag]);
		$string_id = $reflectionMethod->invokeArgs($api, [$string]);
		
		$this->assertEquals($user->id,$user_id);
		$this->assertEquals($item->id,$item_id);
		$this->assertEquals($tag->id,$tag_id);
		$this->assertEquals($string,$string_id);
		
	}


}