<?php
namespace insights\api\crawler;

use GuzzleHttp\Client AS HTTPClient;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use common\components\Log;

class CrawlerApiClient extends Component {

	public $apiUrl;

	public $apiKey;

	public $testUserIds;

	protected $HTTPClient;

	protected $methodParams = [

		// Crawler sites
		'check-core-task-state' => [
			'url' => 'crawler-sites/check-core-task-state',
			'type' => 'get'
		],
		'terminate-core-task' => [
			'url' => 'crawler-sites/terminate-core-task',
			'type' => 'post'
		],
		'add-crawler-task' => [
			'url' => 'crawler-sites/add-crawler-task',
			'type' => 'post'
		],
		'start-grab-site-robots' => [
			'url' => 'crawler-sites/start-grab-robots',
			'type' => 'post'
		],
		'start-get-serp-pages-count' => [
			'url' => 'crawler-sites/start-get-serp-pages-count',
			'type' => 'post'
		],


		// Crawler site pages
		'get-site-page' => [
			'url' => 'crawler-site-pages/get-page',
			'type' => 'get'
		],
		'start-grab-site-page-with-links' => [
			'url' => 'crawler-site-pages/start-grab-page-with-links',
			'type' => 'post'
		],
		'stop-grab-site-page-with-links' => [
			'url' => 'crawler-site-pages/stop-grab-page-with-links',
			'type' => 'post'
		],
		'start-parse-site-page-content' => [
			'url' => 'crawler-site-pages/start-parse-page-content',
			'type' => 'post'
		],

		'start-track-page-forms' => [
			'url' => 'crawler-site-pages/start-track-page-forms',
			'type' => 'post'
		],

		'start-parse-page-keywords' => [
			'url' => 'crawler-site-pages/start-parse-page-keywords',
			'type' => 'post'
		],

		// Crawler external links
		'start-grab-external-link' => [
			'url' => 'crawler-external-links/start-grab-link',
			'type' => 'post'
		],

		// Crawler sitemaps
		'start-grab-sitemap' => [
			'url' => 'crawler-site-sitemaps/start-grab-sitemap',
			'type' => 'post'
		],
		'start-parse-sitemap-links' => [
			'url' => 'crawler-site-sitemaps/start-parse-sitemap-links',
			'type' => 'post'
		],

		// Crawler keywords serp
		'start-grab-keywords-serp' => [
			'url' => 'crawler-keywords-serp/start-grab-serp',
			'type' => 'post'
		],
		'start-parse-keywords-serp' => [
			'url' => 'crawler-keywords-serp/start-parse-serp',
			'type' => 'post'
		],
		'get-stop-words' => [
			'url' => 'site-keywords/get-stop-words',
			'type' => 'get'
		],
	];

	public function __construct() {
		$this->HTTPClient = new HTTPClient;
	}

	protected function sendRequest($name, $params=[], $headers=[], $api_url=null)
	{
		$headers = ArrayHelper::merge($headers, [
			'Accept' =>'application/json',
			'api-key' => $this->apiKey
		]);

		$request_params = $this->getRequestParams($name);
		$request_type = $request_params['type'];

		$apiUrl = ($api_url) ? $api_url : $this->apiUrl;

		$request_url = $apiUrl . $request_params['url'];

		$response = null;
		try {
			$response = $this->HTTPClient->$request_type($request_url, [
				'headers' => $headers,
				'json' => $params,
			]);

			$answer = $response->json(); //Guzzle 5.3.0
			/*$answer = json_decode(         //Guzzle 6.2.0
				(string) $response->getBody(),
				true
			);*/

			return $answer;

		} catch(\GuzzleHttp\Exception\BadResponseException $e) {

			$this->logExceptions($request_url, $response, $e);

			return false;
		} catch(\GuzzleHttp\Exception\ParseException $e) {

			$this->logExceptions($request_url, $response, $e);

			return false;
		}

	}

	protected function logExceptions($request_url, $response, $e)
	{
		Log::add(
			'Url:'.$request_url."\n".
			'Message: '.$e->getMessage()."\n".
			'Response: '.(($response) ? $response->getBody() : '')."\n",
			'api-http-errors',
			\Yii::getAlias('@runtime').'/logs'
		);
	}

	protected function getRequestParams($name)
	{
		if (empty($this->methodParams[$name])) {
			throw new \BadMethodCallException('Api method is undefined.');
		}

		return $this->methodParams[$name];
	}

	public function checkCoreTaskState($task_type, $object_id, $crawler_name, $api_url=null)
	{
		$data = [
			'task_type' => $task_type,
			'object_id' => $object_id,
			'crawler_name' => $crawler_name
		];
		return $this->sendRequest('check-core-task-state', $data, [], $api_url);
	}

	public function terminateCoreTask($task_type, $object_id, $crawler_name, $api_url=null)
	{
		$data = [
			'task_type' => $task_type,
			'object_id' => $object_id,
			'crawler_name' => $crawler_name
		];
		return $this->sendRequest('terminate-core-task', $data, [], $api_url);
	}

	public function getSites()
	{
		return $this->sendRequest('get-sites');
	}


	public function startGrabSiteRobots($site_id)
	{
		return $this->sendRequest('start-grab-site-robots', ['id'=>$site_id]);
	}

	public function startGetSerpPagesCount($site_id)
	{
		return $this->sendRequest('start-get-serp-pages-count', ['id'=>$site_id]);
	}

	public function getSitemap($sitemap_id)
	{
		return $this->sendRequest('get-site-page', ['id'=>$sitemap_id]);
	}

	public function startGrabSitemap($sitemap_id)
	{
		return $this->sendRequest('start-grab-sitemap', ['id'=>$sitemap_id]);
	}


	public function startGrabExternalLink($link_id)
	{
		return $this->sendRequest('start-grab-external-link', ['id'=>$link_id]);
	}

	public function startParseSitemapLinks($sitemap_id)
	{
		return $this->sendRequest('start-parse-sitemap-links', ['id'=>$sitemap_id]);
	}
	public function stopParseSitemapLinks($sitemap_id, $links)
	{
		return $this->sendRequest('stop-parse-sitemap-links', [
			'id' => $sitemap_id,
			'links' => $links
		]);
	}

	public function getSitePage($page_id)
	{
		return $this->sendRequest('get-site-page', ['id'=>$page_id]);
	}

	public function startGrabSitePageWithLinks($page_id)
	{
		return $this->sendRequest('start-grab-site-page-with-links', ['id'=>$page_id]);
	}

	public function stopGrabSitePageWithLinks($page_id, $data)
	{
		return $this->sendRequest('stop-grab-site-page-with-links', [
			'id' => $page_id,
			'data' => $data
		]);
	}

	public function startParseSitePageContent($page_id)
	{
		return $this->sendRequest('start-parse-site-page-content', ['id'=>$page_id]);
	}

	public function startGrabKeywordsSerp($keyword_id)
	{
		return $this->sendRequest('start-grab-keywords-serp', ['id'=>$keyword_id]);
	}

	public function startParseKeywordsSerp($keyword_id)
	{
		return $this->sendRequest('start-parse-keywords-serp', ['id'=>$keyword_id]);
	}

	public function startTrackPageForms($page_id)
	{
		return $this->sendRequest('start-track-page-forms', ['id'=>$page_id]);
	}

	public function startParsePageKeywords($page_id)
	{
		return $this->sendRequest('start-parse-page-keywords', ['id'=>$page_id]);
	}

	public function addCrawlerTask($data)
	{
		return $this->sendRequest('add-crawler-task', ['data'=>$data]);
	}

	public function getStopWords()
	{
		return $this->sendRequest('get-stop-words', []);
	}
}