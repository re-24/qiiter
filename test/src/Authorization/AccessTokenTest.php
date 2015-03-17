<?php

namespace re24\Qiiter\Test\Authorization;

use re24\Qiiter\Authorization\AccessToken;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
	public function testSetGetAccessToken()
	{
		$token_str = 'ABCDEFG';
		
		$token = new AccessToken();
		$token->setAccseeToken($token_str);
		
		$this->assertEquals($token_str, $token->getAccessToken());
		$this->assertEquals($token_str, $token);
	}
	
	public function testSetGetScope()
	{
		$scope = ['write','read'];
		
		$token = new AccessToken();
		$token->setScope($scope);
		
		$this->assertEquals($scope, $token->getScope());
	}
}