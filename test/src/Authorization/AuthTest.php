<?php

namespace re24\Qiiter\Authorization;

use re24\Qiiter\Authorization\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Subscriber\History;

class AuthTest extends \PHPUnit_Framework_TestCase
{
    private $auth ;
    
    public function setUp()
    {
        $opt = [
            'client_id' => 'mock_id',
            'client_secret' => 'mock_secret',
            'scope' => ['read_qiita', 'write_qiita'],
            'state' => 'aaaabbbbcccc'
        ];
        
        $this->auth = new Auth($opt);
    }
    
    public function testGetAuthorizationUrl()
    {
        $expect_url = 'https://qiita.com/api/v2/oauth/authorize?client_id=mock_id&scope=read_qiita+write_qiita&state=aaaabbbbcccc';
        $this->assertEquals($expect_url, $this->auth->getAuthorizationUrl());
    }
    
    public function testSetGetState()
    {
        $state = 'XXXXXXYYYYYYYZZZZZZZ';
        
        $this->assertNotEquals($state, $this->auth->getState());
        $this->auth->setState($state);
        $this->assertEquals($state, $this->auth->getState());
    }
    
    public function testGetAccessToken()
    {
        $mock_token = 'AAAAAAAABBBBBBBBBBBCCCCCCCCCCc';
        $mock_scope = ['read_qiita', 'write_qiita'];
        $data = [
            'client_id'=>'a91f0396a0968ff593eafdd194e3d17d32c41b1da7b25e873b42e9058058cd9d',
            'scopes'=> $mock_scope,
            'token' => $mock_token
        ];
        $stream = Stream::factory(json_encode($data));
        $response = new Response(200, ['Content-Type' => 'application/json'], $stream);
        
        $mock = new Mock();
        $mock->addResponse($response);
        
        $client = new Client();
        $client->getEmitter()->attach($mock);
        
        $this->auth->setHttpClient($client);
        $token = $this->auth->getAccessToken('AAAAAAAA');
        
        $this->assertEquals($mock_token, $token);
        $this->assertEquals($mock_scope, $token->getScope());
        $this->assertInstanceOf('re24\Qiiter\Authorization\AccessToken', $token);
    }
    
    /**
     * @expectedException re24\Qiiter\Exception\HttpException
     */
    public function testGetAccessTokenFailer()
    {
        $response = new Response(404);
        $mock = new Mock();
        $mock->addResponse($response);
        
        $client = new Client();
        $client->getEmitter()->attach($mock);
        
        $this->auth->setHttpClient($client);
        $this->auth->getAccessToken('AAABBBCCCC');
    }
}
