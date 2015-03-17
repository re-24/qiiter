<?php

namespace re24\Qiiter\Authorization;

use re24\Qiiter\Exception\HttpException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Client;

class Auth
{
	/**
	 * @var string Authorize page url
	 */
	const AUTHORIZE_URL = 'https://qiita.com/api/v2/oauth/authorize';
	
	/**
	 * @var string get accsess token url
	 */
	const ACCESSTOKEN_URL = 'https://qiita.com/api/v2/access_tokens';
	
	private $client_id = '';
	
	private $client_secret ='';
	
	private $scope = [];
	
	private $state = '';
	
	private $httpclient;
	
	public function __construct($options = [])
	{
		foreach($options as $key=>$val) {
			if(property_exists($this, $key)) {
				$this->{$key} = $val;
			}
		}
	}
	
	/**
	 * UT 用
	 */
	public function setHttpClient($client)
	{
		$this->httpclient = $client;
	}
		
	/**
	 * Get Authorization url. 
	 * 
	 * @return string
	 */
	public function getAuthorizationUrl()
	{
		$params = [
			'client_id'=>$this->client_id,
			'scope'=> is_array($this->scope)?implode(' ', $this->scope):$this->scope,
			'state'=> $this->state,
		];
		return $url = self::AUTHORIZE_URL.'?'.http_build_query($params); 
	}
	
	/**
	 * get state ,which send Authorization url
	 * 
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}
	
	/**
	 * set state ,which send Authorization url
	 * 
	 * @param string $state
	 * @return this
	 */
	public function setState($state)
	{
		$this->state = $state;
		return $this;
	}
	
	public function getAccessToken($code)
	{
		$json = [
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'code' => $code,
		];
			
		try{
			
			// @codeCoverageIgnoreStart
			if (is_null($this->httpclient)) {
				// UT用でなければ作成
				$client = new Client();
			} else {
				$client = $this->httpclient;
			}
			// @codeCoverageIgnoreEnd
			
			$request = $client->createRequest('POST',self::ACCESSTOKEN_URL,['json'=> $json, 'verify' => false]);
			$response = $client->send($request);
			
			$data = $response->json();
			$accesstoken = new AccessToken();
			$accesstoken->setAccseeToken($data['token'])->setScope($data['scopes']);
			
			return $accesstoken;
			
		} catch (TransferException $ex) {
			throw new HttpException($ex);
		}

	}

}