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
		'add-crawler-task' => [
			'url' => 'crawler-sites/add-crawler-task',
			'type' => 'post'
		],
		'start-grab-site-robots' => [
			'url' => 'crawler-sites/start-grab-robots',
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

		// Crawler site page keywords
		'start-parse-site-page-keyword' => [
			'url' => 'crawler-site-page-keywords/start-parse-page-keyword',
			'type' => 'post'
		],
		'start-parse-site-page-keywords' => [
			'url' => 'crawler-site-page-keywords/start-parse-page-keywords',
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


	public function startGrabSiteRobots($site_id)
	{
		return $this->sendRequest('start-grab-site-robots', ['id'=>$site_id]);
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

	public function startParseSitePageKeyword($keyword_id)
	{
		return $this->sendRequest('start-parse-site-page-keyword', ['id'=>$keyword_id]);
	}

	public function startParseSitePageKeywords($page_id)
	{
		return $this->sendRequest('start-parse-site-page-keywords', ['page_id'=>$page_id]);
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

	public function addCrawlerTask($data)
	{
		return $this->sendRequest('add-crawler-task', ['data'=>$data]);
	}
}