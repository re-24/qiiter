<?php
namespace re24\Qiiter\Test\Exception;

use re24\Qiiter\Exception\HttpException;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Stream\Stream;

class HttpExceptionTest extends \PHPUnit_Framework_TestCase
{

	public function exception_400level_Provider()
	{
		$headers = [
			'Rate-Limit' => 300,
			'Rate-Remaining' => 200,
			'Rate-Reset' => 1422082696,
		];
		
		$headers_limitover = [
			'Rate-Limit' => 300,
			'Rate-Remaining' => 0,
			'Rate-Reset' => 1422082696,
		];
		
		// statusCode,header,responseBody
		// @todo 405の発生のさせ方がわからないので、responsebodyは適当
		return [
			[400, $headers, '{"message":"Bad request","type":"bad_request"}'],
			[401, $headers, '{"message":"Unauthorized","type":"unauthorized"}'],
			[403, $headers_limitover, '{"message":"Rate limit exceeded","type":"rate_limit_exceeded"}'],
			[404, $headers, '{"message":"Not found","type":"not_found"}'],
			[405, $headers, '{"message":"unclear ","type":"unclear "}'],			
		];
	}
	
	public function exception_500level_Provider()
	{
		$headers = [
			'Rate-Limit' => 300,
			'Rate-Remaining' => 200,
			'Rate-Reset' => 1422082696,
		];
		
		// statusCode,header,responseBody
		// @todo 500の発生のさせ方がわからないので、responsebodyは適当
		return [
			[500, $headers, '{"message":"unclear ","type":"unclear "}'],			
		];
	}
	
	public function exception_mock_create($statusCode, $headers=[],$responseBody="")
	{
		$client = new Client();
		$stream = Stream::factory($responseBody);
		$response = new Response($statusCode,$headers,$stream);
		
		$mock = new Mock();
		$mock->addResponse($response);
		$client->getEmitter()->attach($mock);
		
		return $client;
	}
	
	/**
	 * @dataProvider exception_400level_Provider
	 */
	public function testException_Cause_RequestException($statusCode, $headers,$responseBody)
	{
		$this->setExpectedException(
			're24\Qiiter\Exception\HttpException',
			'Client error (400 level HTTP error. )',
			$statusCode);
		
		$client = $this->exception_mock_create($statusCode, $headers, $responseBody);
		try {
			$client->get('/');
		} catch(\Exception $ex) {
			$qiiter_ex = new HttpException($ex);
			
			$this->assertInstanceOf('\GuzzleHttp\Message\Response', $qiiter_ex->getResponse());
			$this->assertInstanceOf('\GuzzleHttp\Message\Request', $qiiter_ex->getRequest());
			$this->assertEquals($headers['Rate-Limit'], $qiiter_ex->getAccessLimit());
			$this->assertEquals($headers['Rate-Remaining'], $qiiter_ex->getAccessRemaining());
			$this->assertEquals($headers['Rate-Reset'], $qiiter_ex->getAccessCountResetTime());
			if($headers['Rate-Remaining'] === 0) {
				$this->assertTrue($qiiter_ex->AccessLimitOver());
			} else {
				$this->assertFalse($qiiter_ex->AccessLimitOver());
			}
			
			throw $qiiter_ex;
		}
	}

	/**
	 * @dataProvider exception_500level_Provider
	 */
	public function testException_Cause_ServerException($statusCode, $headers,$responseBody)
	{
		$this->setExpectedException(
			're24\Qiiter\Exception\HttpException',
			'Server error (500 level HTTP error.)',
			$statusCode);
		
		$client = $this->exception_mock_create($statusCode, $headers, $responseBody);
		try {
			$client->get('/');
		} catch(\Exception $ex) {
			$qiiter_ex = new HttpException($ex);
			
			$this->assertInstanceOf('\GuzzleHttp\Message\Response', $qiiter_ex->getResponse());
			$this->assertInstanceOf('\GuzzleHttp\Message\Request', $qiiter_ex->getRequest());
			$this->assertInstanceOf('\GuzzleHttp\Message\Response', $qiiter_ex->getResponse());
			$this->assertInstanceOf('\GuzzleHttp\Message\Request', $qiiter_ex->getRequest());
			$this->assertEquals($headers['Rate-Limit'], $qiiter_ex->getAccessLimit());
			$this->assertEquals($headers['Rate-Remaining'], $qiiter_ex->getAccessRemaining());
			$this->assertEquals($headers['Rate-Reset'], $qiiter_ex->getAccessCountResetTime());
			$this->assertFalse($qiiter_ex->AccessLimitOver());
			throw $qiiter_ex;
		}
	}
	
	/**
	 * @expectedException re24\Qiiter\Exception\HttpException
	 * @expectedExceptionCode 400
	 * @expectedExceptionMessage A net working error ( connection timeout or DNS errors etc.)
	 */
	public function testException_Cause_TransferException()
	{
		$http_ex = new TransferException('connection failed', 400);
		
		$ex = new HttpException($http_ex);
		$this->assertNull($ex->getRequest());
		$this->assertNull($ex->getResponse());
		$this->assertEquals(-1, $ex->getAccessLimit());
		$this->assertEquals(-1, $ex->getAccessRemaining());
		$this->assertEquals(-1, $ex->getAccessCountResetTime());
		$this->assertFalse($ex->AccessLimitOver());

		throw $ex;
	}
	
	/**
	 * @expectedException re24\Qiiter\Exception\HttpException
	 * @expectedExceptionCode 999
	 * @expectedExceptionMessage Unexpected Exception Error
	 */
	public function testUnexpectedException()
	{
		$client = $this->exception_mock_create(999);
		try{
			$client->get('/');
		} catch (\Exception $ex) {
			throw  new HttpException($ex);
		}
	}
}
