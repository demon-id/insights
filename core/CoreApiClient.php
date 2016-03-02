<?php
namespace insights\api\core;

use GuzzleHttp\Client AS HTTPClient;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class CoreApiClient extends Component {

	public $apiUrl;

	public $apiKey;

	protected $HTTPClient;

	protected $methodParams = [
		'not-in-crawler-sites' => [
			'url' => 'sites/not-in-crawler-sites',
			'type' => 'get'
		],
		'mark-site-as-in-crawler' => [
			'url' => 'sites/mark-as-in-crawler',
			'type' => 'put'
		],
	];

	public function __construct() {
		$this->HTTPClient = new HTTPClient;
	}

	protected function sendRequest($name, $params=[], $headers=[])
	{
		$headers = ArrayHelper::merge($headers, [
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

			Log::add('Message: '.$e->getMessage().' Response: '.$response->getBody(), 'api-http-errors', \Yii::getAlias('@runtime').'/logs');

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


	public function getNotInCrawlerSites($limit=10)
	{
		return $this->sendRequest('not-in-crawler-sites', ['limit'=>$limit]);
	}

	public function markSiteAsInCrawler($core_site_id)
	{
		return $this->sendRequest('mark-site-as-in-crawler', ['site_id'=>$core_site_id]);
	}
}