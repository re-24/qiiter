<?php

namespace re24\Qiiter\Api;

use re24\Qiiter\Authorization\AccessToken;
use re24\Qiiter\Entity\AbstractEntity;
use re24\Qiiter\Entity\User;
use re24\Qiiter\Entity\Comment;
use re24\Qiiter\Entity\Item;
use re24\Qiiter\Entity\Tag;
use re24\Qiiter\Exception\HttpException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class Qiita
{
	private $token;
	private $httpclient;
	private $base_url;
	private $paginaton_query = [];
	
	public function __construct(AccessToken $token = null,$httpclient = null)
	{	
		$this->base_url = 'http://qiita.com/api/v2/';
		
		$headers = [];
		
		if(!is_null($token)) {
			$this->token = $token;
			$headers['Authorization'] = 'Bearer ' . $token->getAccessToken();
		} 

		if(is_null($httpclient)) {
			$this->httpclient = new Client();
		} else {
			// UT用に付け替えられるように
			$this->httpclient = $httpclient;
		}
		
		// ヘッダー設定
		$this->httpclient->setDefaultOption('headers', $headers);		
		
		// ページネーションクエリーデフォルト
		$this->paginaton_query['page'] = 1;
		$this->paginaton_query['per_page'] = 20;
	}
	
	/// ======================================================
	///  Authoricated user infomation
	/// ======================================================
	
	/**
	 * 認証に紐付いたUser情報を取得
	 * 
	 * @return User
	 */
	public function getMyProfile()
	{
		$response = $this->send('GET', 'authenticated_user');
		$user = new User();
		$user->arrayToEntity($response->json());
		return $user;
	}
	
	/**
	 * 認証に紐づいたUserの記事一覧を取得
	 * 
	 * @return array Item Entity Array
	 */
	public function getMyItems()
	{		
		$response = $this->send('GET', 'authenticated_user/items',['query' => $this->paginaton_query]);
		$items = $response->json();
			
		$ret = [];
		foreach($items as $item ) {
			$entity = new Item();

			$ret[] = $entity->arrayToEntity($item);
		}
		
		return $ret;
	}

	/// ======================================================
	///  Item Section
	/// ======================================================
	
	/**
	 * 記事の新規投稿
	 * 
	 * @param Item $item
	 * @return Item 記事作成後情報
	 */
	public function postItem(Item $item)
	{
		$response = $this->send('POST', 'items',['json' => $item->getArray()]);
		
		$ret_item = new Item();
		$ret_item->arrayToEntity($response->json());
		return $ret_item;
	}
	
	/**
	 * 記事の更新
	 * 
	 * @param Item $item
	 * @return Item
	 */
	public function updateItem(Item $item)
	{
		
		$response = $this->send('PATCH', 'items/'.$item->id, ['json' => $item->getArray()]);

		$ret_item = new Item();
		$ret_item->arrayToEntity($response->json());
			
		return $ret_item;
	}
	
	/**
	 * 記事の削除
	 * 
	 * @param string | Item $item
	 * @return \GuzzleHttp\Message\Response
	 */
	public function deleteItem($item)
	{
		$id = $this->getEntityId($item);
		return $this->send('DELETE', 'items/'.$id);
	}
	
	/**
	 * 記事取得(id指定)
	 * 
	 * @param string | Item $item
	 * @return Item
	 */
	public function getItem($item)
	{
		$id = $this->getEntityId($item);
		$response = $this->send('GET', 'items/'.$id);
		
		$ret_item = new Item();
		$ret_item->arrayToEntity($response->json());
		
		return $ret_item;
	}
	
	/**
	 * 記事検索(文字列検索)
	 * 
	 * @param string $query_string
	 * @return Item[] Item Entity Array
	 */
	public function getQueryItems($query_string=null)
	{
		$query = $this->paginaton_query;
		if (!is_null($query_string)) {
			$query = array_merge($query, ['query' => $query_string]);
		}
		
		$response = $this->send('GET', 'items', ['query' => $query]);
		$items = $response->json();
		
		$ret = [];
		foreach($items as $item) {
			$entity = new Item();
			$ret[] = $entity->arrayToEntity($item);
		}
	
		return $ret;
	}

	/**
	 * 記事検索(ユーザid指定)
	 * 
	 * @param string | User $user
	 * @return Item[] User Entities array
	 */
	public function getUserItems($user)
	{
		$id = $this->getEntityId($user);
		
		$response = $this->send('GET', 'users/'.$id.'/items',['query' => $this->paginaton_query]);
		
		$entites = $response->json();
		$ret = [];
		foreach($entites as $entity) {
			$item = new Item();
			$item->arrayToEntity($entity);
			
			$ret[] = $item;
		}
		return $ret;
	}
	
	/**
	 * 記事検索(指定ユーザidのストック検索)
	 * 
	 * @param string | User $user
	 * @return Item[] Item Entities array
	 */
	public function getStockItems($user)
	{
		$id = $this->getEntityId($user);
		
		$response = $this->send('GET', 'users/'. $id.'/stocks',['query' => $this->paginaton_query]);
		
		$entites = $response->json();
		$ret = [];
		foreach($entites as $entity) {
			$item = new Item();
			$item->arrayToEntity($entity);
			
			$ret[] = $item;
		}
		return $ret;
	}

	/**
	 * 記事検索(タグid検索)
	 * 
	 * @param string | Tag $user
	 * @return Item[] Item Entities array
	 */
	public function getTagItems($tag)
	{
		$id = $this->getEntityId($tag);
		$response = $this->send('GET', 'tags/'.$id.'/items',['query' => $this->paginaton_query]);
		
		$entites = $response->json();
		$ret = [];
		foreach($entites as $entity) {
			$item = new Item();
			$item->arrayToEntity($entity);
			
			$ret[] = $item;
		}
		return $ret;
	}

	/**
	 * 特定の投稿をストック済みかどうかを判定
	 * 
	 * @param  string | Item $item
	 * @return \GuzzleHttp\Message\Response
	 */
	public function isStockedItem($user)
	{
		$id = $this->getEntityId($user);
		return $this->send('GET', 'items/'.$id.'/stock');	
	}

	/**
	 * 投稿をストックする
	 * 
	 * @param  string | Item $item
	 * @return \GuzzleHttp\Message\Response
	 */
	public function stockItem($item)
	{
		$id = $this->getEntityId($item);
		return $this->send('PUT', 'items/'.$id.'/stock');
	}

	/**
	 * 投稿のストックを解除する
	 * 
	 * @param  string | Item $item
	 * @return \GuzzleHttp\Message\Response
	 */
	public function stockItemCancel($item)
	{
		$id = $this->getEntityId($item);
		return $this->send('DELETE', 'items/'.$id.'/stock');
	}

	/// ======================================================
	///  Comment Section
	/// ======================================================
	
	/**
	 * コメントの投稿
	 * 
	 * @param string | Item $item
	 * @param Comment $comment
	 * @return Comment
	 */
	public function postComment($item, Comment $comment)
	{
		$id = $this->getEntityId($item);
		$response = $this->send('POST', 'items/'.$id.'/comments', ['json' => $comment->getArray()] );

		$ret_item = new Comment();
		$ret_item->arrayToEntity($response->json());
		
		return $ret_item;
	}
	
	/**
	 * コメントの更新
	 * 
	 * @param Comment $comment
	 * @return Comment
	 */
	public function updateComment(Comment $comment)
	{
		$response = $this->send('PATCH', 'comments/'.$comment->id, ['json' => $comment->getArray()] );
		
		$ret_comment = new Comment();
		$ret_comment->arrayToEntity($response->json());
		
		return $ret_comment;
	}

	/**
	 * コメントの削除
	 * 
	 * @param string | Comment $comment
	 * @return \GuzzleHttp\Message\Response
	 */
	public function deleteComment($comment)
	{
		$id = $this->getEntityId($comment);
		return $this->send('DELETE', 'comments/'.$id);
	}
	
	
	/**
	 * コメント取得(コメントid指定)
	 * 
	 * @param string | Comment $comment
	 * @return Comment
	 */
	public function getComment($comment) 
	{
		$id = $this->getEntityId($comment);
		$response = $this->send('GET','comments/'.$id);
				
		$ret_comment = new Comment();
		$ret_comment->arrayToEntity($response->json());

		return $ret_comment;
	}
	
	/**
	 * 記事に付けられたコメント取得
	 * 
	 * @param string | Item $item
	 * @return Comment[]
	 */
	public function getItemComments($item)
	{
		$id = $this->getEntityId($item);
		
		$response = $this->send('GET', 'items/'.$id.'/comments',['query' => $this->paginaton_query]);

		$comment_arr = $response->json();
		$comments = [];
		foreach($comment_arr as $var) {
			$comment = new Comment();
			$comments[] = $comment->arrayToEntity($var);
		}

		return $comments;
	}
	
	/**
	 * コメントにthanksをつける
	 * 
	 * @param string | Comment $comment
	 * @return \GuzzleHttp\Message\Response
	 */
	public function thanksComent($comment) 
	{
		$id = $this->getEntityId($comment);
		return $this->send('PUT', 'comments/'.$id.'/thank');
	}
	
	/**
	 * コメントのthanksを外す
	 * 
	 * @param string | Comment $comment
	 * @return \GuzzleHttp\Message\Response
	 */
	public function thanksCommentCancel($comment)
	{
		$id = $this->getEntityId($comment);
		return $this->send('DELETE', 'comments/'.$id.'/thank');
	}
	
	/// ======================================================
	///  Tag Section
	/// ======================================================
	
	/**
	 * タグ一覧取得
	 * 
	 * @return Tag[]
	 */
	public function getTags()
	{		
		$response = $this->send('GET', 'tags', ['query' => $this->paginaton_query]);
		
		$tags_arr = $response->json();
		$tags = [];
		foreach($tags_arr as $var) {
			$tag = new Tag();
			$tag->arrayToEntity($var);
			$tags[] = $tag;
		}
		
		return $tags;
	}
	
	/**
	 * タグ取得(id指定)
	 * 
	 * @param string | Tag $tag
	 * @return Tag
	 */
	public function getTag($tag) 
	{
		$id = $this->getEntityId($tag);
		$response = $this->send('GET','tags/'.$id);
		
		$ret_tag = new Tag();
		$ret_tag->arrayToEntity($response->json());
		
		return $ret_tag;
	}
	
	/**
	 * ユーザがフォローしているTag取得
	 * 
	 * @param string | User $user
	 * @return Tag[]
	 */
	public function getUserFollowingTag($user)
	{
		$id = $this->getEntityId($user);

		$response = $this->send('GET','users/'.$id.'/following_tags',['query' => $this->paginaton_query]);
		
		$tags_arr = $response->json();
		$tags = [];
		foreach($tags_arr as $var) {
			$tag = new Tag();
			$tag->arrayToEntity($var);
			$tags[] = $tag;
		}
		
		return $tags;
	}
	
	/**
	 * タグがフォロー済みかどうかを判定
	 * 
	 * @param  string | Tag $tag
	 * @return \GuzzleHttp\Message\Response
	 */
	public function isFollowedTag($tag)
	{
		$id = $this->getEntityId($tag);
		return $this->send('GET', 'tags/'.$id.'/following');
	}
	
	/**
	 * タグをフォローする
	 * 
	 * @param  string | Tag $tag
	 * @return \GuzzleHttp\Message\Response
	 */
	public function followTag($tag)
	{
		$id = $this->getEntityId($tag);
		return $this->send('PUT', 'tags/'.$id.'/following');
	}

	/**
	 * タグをフォローを解除する
	 * 
	 * @param  string | Tag $tag
	 * @return \GuzzleHttp\Message\Response
	 */
	public function followTagCancel($tag)
	{
		$id = $this->getEntityId($tag);
		return $this->send('DELETE', 'tags/'.$id.'/following');
	}
	
	/// ======================================================
	///  User Section
	/// ======================================================
	
	/**
	 * ユーザ一覧取得
	 * 
	 * @return User[]
	 */
	public function getUsers()
	{
		$response = $this->send('GET','users',['query' => $this->paginaton_query]);
		
		$user_arr = $response->json();
		$users = [];
		foreach($user_arr as $var) {
			$user = new User();
			$user->arrayToEntity($var);
			$users[] = $user;
		}
		
		return $users;
	}
	
	/**
	 * ユーザ取得(id指定)
	 * 
	 * @param string | user $user
	 * @return User
	 */
	public function getUser($user)
	{
		$id = $this->getEntityId($user);
		$response = $this->send('GET','users/'.$id);
		
		$ret_user = new User();
		$ret_user->arrayToEntity($response->json());
		
		return $ret_user;
	}
	
	/**
	 * 指定ユーザ"が"フォローしているユーザ取得
	 * 
	 * @param string | User $user
	 * @return User[]
	 */
	public function getFollowingUser($user)
	{
		$id = $this->getEntityId($user);
		
		$response = $this->send('GET','users/'.$id.'/followees',['query' => $this->paginaton_query]);
		
		$user_arr = $response->json();
		$users = [];
		foreach($user_arr as $var) {
			$user = new User();
			$user->arrayToEntity($var);
			$users[] = $user;
		}
		
		return $users;
	}
	
	/**
	 * 指定ユーザ"を"フォローしているユーザ取得
	 * 
	 * @param string | User $user
	 * @return User[]
	 */
	public function getFollowedUser($user)
	{
		$id = $this->getEntityId($user);
		
		$response = $this->send('GET','users/'.$id.'/followers',['query' => $this->paginaton_query]);
		
		$user_arr = $response->json();
		$users = [];
		foreach($user_arr as $var) {
			$user = new User();
			$user->arrayToEntity($var);
			$users[] = $user;
		}
		
		return $users;
	}

	/**
	 * 特定の投稿をストックしているユーザ一覧を返す
	 * 
	 * @param string | Item $item
	 * @return User[]
	 */
	public function getItemStockUsers($item)
	{
		$id = $this->getEntityId($item);
		$response = $this->send('GET','items/'.$id.'/stockers',['query' => $this->paginaton_query]);

		$user_arr = $response->json();
		$users = [];
		foreach($user_arr as $var) {
			$user = new User();
			$user->arrayToEntity($var);
			$users[] = $user;
		}
		
		return $users;
	}

	/**
	 * ユーザをフォロー済みかどうかを判定
	 * 
	 * @param  string | User $user
	 * @return \GuzzleHttp\Message\Response
	 */
	public function isFollowedUser($user)
	{
		$id = $this->getEntityId($user);
		return $this->send('GET', 'users/'.$id.'/following');
	}

	/**
	 * ユーザをフォローする
	 * 
	 * @param  string | User $user
	 * @return \GuzzleHttp\Message\Response
	 */
	public function followUser($user)
	{
		$id = $this->getEntityId($user);
		return $this->send('PUT', 'users/'.$id.'/following');
	}

	/**
	 * ユーザフォローを解除する
	 * 
	 * @param  string | User $user
	 * @return \GuzzleHttp\Message\Response
	 */
	public function followUserCancel($user)
	{
		$id = $this->getEntityId($user);
		return $this->send('DELETE', 'users/'.$id.'/following');
	}
	
	/// ======================================================
	///  Utility private function
	/// ======================================================

	/**
	 * ページネーションクエリー設定
	 * 
	 * @param integer $page
	 * @param integer $per_page
	 */
	public function setPaginationQuery($page = 1, $per_page = 20)
	{
		$this->paginaton_query['page'] = $page;
		$this->paginaton_query['per_page'] =$per_page;
	}
	
	/**
	 * http send
	 * 
	 * @param string $method
	 * @param string $url
	 * @param array $param
	 * @return \GuzzleHttp\Message\Response
	 * @throws HttpException
	 */
	private function send($method, $url, array $param = null)
	{
		if (!preg_match('/^https?:\/\/.+$/', $url)) {
			$url = $this->base_url.$url; 
		}
		
		$client = $this->httpclient;
		if(!is_array($param)) {
			$request = $client->createRequest($method,$url);
		} else {
			$request = $client->createRequest($method,$url,$param);
		}
		
		try {
			$response = $client->send($request);
		} catch (TransferException $ex) {
			throw new HttpException($ex);
		}
		
		return $response;
	}
	

	/**
	 * get id from Entity or string 
	 * 
	 * @param string | AbstractEntity $id
	 * @return string
	 */
	private function getEntityId($id)
	{
		if($id instanceof AbstractEntity) {
			return $id->id;
		}
		
		return $id;
	}
	
	
}