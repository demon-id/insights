<?php
namespace insights\api\crawlerCore;

use GuzzleHttp\Client AS HTTPClient;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use common\components\Log;

class CrawlerCoreApiClient extends Component {

	public $apiUrl;

	public $apiKey;

	public $testUserIds;

	protected $HTTPClient;

	protected $methodParams = [

		// Sites
		'get-sites' => [
			'url' => 'sites/index',
			'type' => 'get'
		],
		'add-site' => [
			'url' => 'sites/add-site',
			'type' => 'post'
		],
		'delete-site' => [
			'url' => 'sites/delete-site',
			'type' => 'put'
		],
		'hard-delete-site' => [
			'url' => 'sites/hard-delete-site',
			'type' => 'put'
		],
		'start-crawl-site' => [
			'url' => 'sites/start-crawl-site',
			'type' => 'post'
		],
		'check-site-available' => [
			'url' => 'sites/check-site-available',
			'type' => 'get'
		],

		// Keywords
		'add-keywords' => [
			'url' => 'site-keywords/add-keywords',
			'type' => 'post'
		],
		'get-site-keywords' => [
			'url' => 'site-keywords/get-site-keywords',
			'type' => 'get'
		],
		'count-sites-keywords' => [
			'url' => 'site-keywords/count-sites-keywords',
			'type' => 'get'
		],
		'delete-site-keyword' => [
			'url' => 'site-keywords/delete-site-keyword',
			'type' => 'put'
		],
		'start-crawl-serp-keyword' => [
			'url' => 'site-keywords/start-crawl-serp-keyword',
			'type' => 'put'
		],
		'stop-crawl-serp-keyword' => [
			'url' => 'site-keywords/stop-crawl-serp-keyword',
			'type' => 'put'
		],

		// Proxy
		'get-proxy' => [
			'url' => 'proxy/get-proxy',
			'type' => 'get'
		],

	];

	public function __construct() {
		$this->HTTPClient = new HTTPClient;
	}

	protected function sendRequest($name, $params=[], $headers=[])
	{
		$headers = ArrayHelper::merge($headers, [
			'Accept' =>'application/json',
			'api-key' => $this->apiKey
		]);

		$request_params = $this->getRequestParams($name);
		$request_type = $request_params['type'];
		$request_url = $this->apiUrl.$request_params['url'];


		try {
			$response = $this->HTTPClient->$request_type($request_url, [
				'headers' => $headers,
				'json' => $params,
			]);

			$answer = $response->json();

			return $answer;

		} catch(\GuzzleHttp\Exception\BadResponseException $e) {

			Log::add($e->getMessage(), 'api-http-errors', \Yii::getAlias('@runtime').'/logs');

			return false;
		}

	}

	protected function getRequestParams($name)
	{
		if (empty($this->methodParams[$name])) {
			throw new \BadMethodCallException('Api method is undefined.');
		}

		return $this->methodParams[$name];
	}

	public function getSites()
	{
		return $this->sendRequest('get-sites');
	}

	/**
	 * Check site availability
	 * @param $url
	 * @return bool
	 */
	public function checkSiteAvailable($url)
	{
		return $this->sendRequest('check-site-available', ['url'=>$url]);
	}

	public function addSite($site_id, $url)
	{
		return $this->sendRequest('add-site', ['site_id'=>$site_id, 'url'=>$url]);
	}

	public function deleteSite($site_id)
	{
		return $this->sendRequest('delete-site', ['site_id'=>$site_id]);
	}

	public function hardDeleteSite($site_id)
	{
		return $this->sendRequest('hard-delete-site', ['site_id'=>$site_id]);
	}

	public function addKeywords($site_id, $keywords=[])
	{
		return $this->sendRequest('add-keywords', ['site_id'=>$site_id, 'keywords'=>$keywords]);
	}

	public function getSiteKeywords($site_id)
	{
		return $this->sendRequest('get-site-keywords', ['site_id'=>$site_id]);
	}

	public function deleteSiteKeyword($id, $site_id)
	{
		return $this->sendRequest('delete-site-keyword', ['id'=>$id, 'site_id'=>$site_id]);
	}

	public function countSitesKeywords($site_ids)
	{
		return $this->sendRequest('count-sites-keywords', ['site_ids'=>$site_ids]);
	}

	public function getProxy()
	{
		return $this->sendRequest('get-proxy');
	}

	public function startCrawlSite($site_id, $data)
	{
		return $this->sendRequest('start-crawl-site', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function startCrawlSerpKeyword($keyword_id, $data)
	{
		return $this->sendRequest('start-crawl-serp-keyword', ['keyword_id'=>$keyword_id, 'data'=>$data]);
	}

	public function stopCrawlSerpKeyword($keyword_id, $data)
	{
		return $this->sendRequest('stop-crawl-serp-keyword', ['keyword_id'=>$keyword_id, 'data'=>$data]);
	}
}