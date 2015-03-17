<?php

namespace re24\Qiiter\Test\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Subscriber\History;
use re24\Qiiter\Test\Provider\Provider;

class TestCase extends \PHPUnit_Framework_TestCase
{
	protected $client;
	protected $history;
	
	private $request_hist;
	private $row_responses;
	
	protected function createMockClient(array $res_array)
	{
		$code = 200;
		$data = "";
		$headers = ['Content-Type' => 'application/json'];
		
		$mock = new Mock();
		foreach($res_array as $res) {
			// statuscode
			if (isset($res['code'])) {
				$code = $res['code'];
			}
			
			// response body
			if (isset($res['file'])) {
				$data = Provider::getProviderData($res['file']);
			} elseif(isset ($res['string'])) {
				$data = $res['string'];
			}

			$this->row_responses[] = $data;
			$stream = Stream::factory($data);
			
			if(isset($res['headers'])) {
				$headers = array_merge($headers, $res['headers']);
			}
			$mock->addResponse(new Response($code, $headers, $stream));
		}
		
		$client = new Client();
		$history = new History();
		$client->getEmitter()->attach($mock);
		$client->getEmitter()->attach($history);
		
		$this->client = $client;
		$this->history = $history;
		
		return $client;
	}
	
	/**
	 * 
	 * @param integer $index
	 * @return \GuzzleHttp\Message\Request
	 */
	protected function getRequest($index = 0)
	{
		$this->requestHistoryInit();
		return $this->request_hist[$index];
	}
	
	protected function getRequestPostJson($index = 0)
	{
		return json_decode((string)$this->getRequest($index)->getBody(),true);
	}

	protected function getRequestMethod($index = 0)
	{
		return $this->getRequest($index)->getMethod();
	}

	protected function getRequestUrlPath($index = 0)
	{
		$url = $this->getRequest($index)->getUrl();
		return parse_url($url,PHP_URL_PATH);
	}
	
	protected function getRequestUrlQuery($index = 0) 
	{
		return $this->getRequest($index)->getQuery();
	}
	
	protected function getResponse($index = 0)
	{
		return $this->row_responses[$index];
	}
	
	protected function getResponseBodyToArray($index = 0) 
	{
		return json_decode($this->getResponse($index), true);
	}
	
	
	private function requestHistoryInit($force = false)
	{
		if(!is_null($this->request_hist) && $force === false) {
			return;
		}
		
		$this->request_hist = [];
		foreach ($this->history as $transaction) {
			$this->request_hist[] = $transaction['request'];
		}
	}
}
