<?php
namespace insights\api\crawler;

use GuzzleHttp\Client AS HTTPClient;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class RequestApi extends Component {

	public $apiUrl;

	public $apiKey;

	public $testUserIds;

	protected $HTTPClient;

	protected $methodParams = [
		'get-sites' => [
			'url' => 'sites/index',
			'type' => 'get'
		],
		'check-site-available' => [
			'url' => 'sites/check-site-available',
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
		'get-site-page' => [
			'url' => 'site-pages/get-page',
			'type' => 'get'
		],
		'start-grab-site-page' => [
			'url' => 'site-pages/start-grab-page',
			'type' => 'post'
		],
		'stop-grab-site-page' => [
			'url' => 'site-pages/stop-grab-page',
			'type' => 'post'
		],
		'start-parse-site-page-links' => [
			'url' => 'site-pages/start-parse-page-links',
			'type' => 'post'
		],
		'stop-parse-site-page-links' => [
			'url' => 'site-pages/stop-parse-page-links',
			'type' => 'post'
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
		$response = $this->HTTPClient->$request_type($request_url, [
			'headers' => $headers,
			'body' => $params
		]);

		$answer = $response->json(); //Guzzle 5.3.0
		/*$answer = json_decode(         //Guzzle 6.2.0
			(string) $response->getBody(),
			true
		);*/

		return $answer;
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

	public function getSitePage($page_id)
	{
		return $this->sendRequest('get-site-page', ['id'=>$page_id]);
	}

	public function startGrabeSitePage($page_id)
	{
		return $this->sendRequest('start-grab-site-page', ['id'=>$page_id]);
	}

	public function stopGrabeSitePage($page_id, $data)
	{
		return $this->sendRequest('stop-grab-site-page', [
			'id' => $page_id,
			'data' => $data
		]);
	}

	public function startParseSitePageLinks($page_id)
	{
		return $this->sendRequest('start-parse-site-page-links', ['id'=>$page_id]);
	}
	public function stopParseSitePageLinks($page_id, $links)
	{
		return $this->sendRequest('stop-parse-site-page-links', [
			'id' => $page_id,
			'links' => $links
		]);
	}
}