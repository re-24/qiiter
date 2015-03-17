<?php
namespace re24\Qiiter\Exception;

use GuzzleHttp\Exception\TransferException;

/**
 * @todo access制限関係のメソッドとかは、他のapi呼び出し時でも利用しそうなので要検討
 */
class HttpException extends QiitaException
{
	private $request;
	private $response;
	private $access_limit = [];
	
	public function __construct(TransferException $ex)
	{
		$message = '';
		$code = $ex->getCode();
		$previous = $ex;
		
		$classname = get_class($ex);
		switch($classname) {
			case 'GuzzleHttp\Exception\TransferException':
				$message = 'A net working error ( connection timeout or DNS errors etc.)';
				break;
			case 'GuzzleHttp\Exception\ClientException':
				$message = 'Client error (400 level HTTP error. )';
				$this->request = $ex->getRequest();
				$this->response = $ex->getResponse();
				$this->parseAccessLimit($this->response);
				break;
			case 'GuzzleHttp\Exception\ServerException':
				$message = 'Server error (500 level HTTP error.)';
				$this->request = $ex->getRequest();
				$this->response = $ex->getResponse();
				$this->parseAccessLimit($this->response);
				break;
			default:
				$message = 'Unexpected Exception Error';
				break;
		}
		
		parent::__construct($message, $code, $previous);
	}
	
	public function getRequest()
	{
		return $this->request;
	}
	
	public function getResponse()
	{
		return $this->response;
	}
	
	/**
	 * アクセス制限情報をパース
	 * 
	 * @param $response  \GuzzleHttp\Message\Response以外は無視
	 */
	private function parseAccessLimit($response)
	{
		if($response instanceof \GuzzleHttp\Message\Response) {
			$this->access_limit = [
				'Rate-Limit' => $response->getHeader('Rate-Limit'),
				'Rate-Remaining' => $response->getHeader('Rate-Remaining'),
				'Rate-Reset' => $response->getHeader('Rate-Reset'),
			];
		}
	}
	
	/**
	 * 
	 * @return integer アクセス上限
	 */
	public function getAccessLimit()
	{
		return isset($this->access_limit['Rate-Limit'])?$this->access_limit['Rate-Limit']:-1;
	}

	/**
	 * 
	 * @return integer 残りアクセス可能数
	 */
	public function getAccessRemaining()
	{
		return isset($this->access_limit['Rate-Remaining'])?$this->access_limit['Rate-Remaining']:-1;
	}
	
	/**
	 * 
	 * @return integer 残りアクセス数リセット時間(timestamp)
	 */
	public function getAccessCountResetTime()
	{
		return isset($this->access_limit['Rate-Reset'])?$this->access_limit['Rate-Reset']:-1;
	}
	
	/**
	 * アクセス上限を超えてしまったかを判定
	 * 
	 * @return boolean アクセス上限に達している場合 true
	 */
	public function AccessLimitOver()
	{
		if(isset($this->access_limit['Rate-Remaining']) 
			&& $this->access_limit['Rate-Remaining'] >0) {
			return false;
		} elseif (!isset($this->access_limit['Rate-Remaining'])) {
			return false;
		}else {
			return true;
		}
	}
}