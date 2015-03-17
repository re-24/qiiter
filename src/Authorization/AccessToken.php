<?php
namespace re24\Qiiter\Authorization;

/**
 * @todo delete access token を行う機能の追加
 * @todo 有効期間とかの期限を設定するのとかも必要？
 */
class AccessToken
{
	private $accesstoken;

	private $scope;
	
	public function setAccseeToken($token)
	{
		$this->accesstoken = $token;
		
		return $this;
	}
	
	public function getAccessToken()
	{
		return $this->accesstoken;
	}
		
	public function setScope(array $scope)
	{
		$this->scope = $scope;
	}
	
	public function getScope()
	{
		return $this->scope;
	}
	
	public function __toString()
	{
		return $this->accesstoken;
	}
}