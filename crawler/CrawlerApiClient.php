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

		// Crawler sites
		'check-site-available' => [
			'url' => 'crawler-sites/check-site-available',
			'type' => 'get'
		],
		'start-grab-site-robots' => [
			'url' => 'crawler-sites/start-grab-robots',
			'type' => 'post'
		],		
		'stop-grab-site-robots' => [
			'url' => 'crawler-sites/stop-grab-robots',
			'type' => 'post'
		],

		// Crawler site pages
		'get-site-page' => [
			'url' => 'crawler-site-pages/get-page',
			'type' => 'get'
		],
		'start-grab-site-page' => [
			'url' => 'crawler-site-pages/start-grab-page',
			'type' => 'post'
		],
		'stop-grab-site-page' => [
			'url' => 'crawler-site-pages/stop-grab-page',
			'type' => 'post'
		],
		'start-parse-site-page-links' => [
			'url' => 'crawler-site-pages/start-parse-page-links',
			'type' => 'post'
		],
		'stop-parse-site-page-links' => [
			'url' => 'crawler-site-pages/stop-parse-page-links',
			'type' => 'post'
		],
		'start-parse-site-page-content' => [
			'url' => 'crawler-site-pages/start-parse-page-content',
			'type' => 'post'
		],
		'stop-parse-site-page-content' => [
			'url' => 'crawler-site-pages/stop-parse-page-content',
			'type' => 'post'
		],
		'start-track-page-forms' => [
			'url' => 'crawler-site-pages/start-track-page-forms',
			'type' => 'post'
		],
		'stop-track-page-forms' => [
			'url' => 'crawler-site-pages/stop-track-page-forms',
			'type' => 'post'
		],

		// Crawler external links
		'start-grab-external-link' => [
			'url' => 'crawler-external-links/start-grab-link',
			'type' => 'post'
		],
		'stop-grab-external-link' => [
			'url' => 'crawler-external-links/stop-grab-link',
			'type' => 'post'
		],

		// Crawler sitemaps
		'start-grab-sitemap' => [
			'url' => 'crawler-site-sitemaps/start-grab-sitemap',
			'type' => 'post'
		],
		'stop-grab-sitemap' => [
			'url' => 'crawler-site-sitemaps/stop-grab-sitemap',
			'type' => 'post'
		],
		'start-parse-sitemap-links' => [
			'url' => 'crawler-site-sitemaps/start-parse-sitemap-links',
			'type' => 'post'
		],
		'stop-parse-sitemap-links' => [
			'url' => 'crawler-site-sitemaps/stop-parse-sitemap-links',
			'type' => 'post'
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

		// Crawler site page keywords
		'start-parse-site-page-keyword' => [
			'url' => 'crawler-site-page-keywords/start-parse-page-keyword',
			'type' => 'post'
		],
		'stop-parse-site-page-keyword' => [
			'url' => 'crawler-site-page-keywords/stop-parse-page-keyword',
			'type' => 'post'
		],
		'start-parse-site-page-keywords' => [
			'url' => 'crawler-site-page-keywords/start-parse-page-keywords',
			'type' => 'post'
		],
		'stop-parse-site-page-keywords' => [
			'url' => 'crawler-site-page-keywords/stop-parse-page-keywords',
			'type' => 'post'
		],

		// Crawler keywords serp
		'start-grab-keywords-serp' => [
			'url' => 'crawler-keywords-serp/start-grab-serp',
			'type' => 'post'
		],
		'stop-grab-keywords-serp' => [
			'url' => 'crawler-keywords-serp/stop-grab-serp',
			'type' => 'post'
		],
		'start-parse-keywords-serp' => [
			'url' => 'crawler-keywords-serp/start-parse-serp',
			'type' => 'post'
		],
		'stop-parse-keywords-serp' => [
			'url' => 'crawler-keywords-serp/stop-parse-serp',
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

	public function startCrawlSite($site_id, $force)
	{
		return $this->sendRequest('start-crawl-site', ['site_id'=>$site_id, 'force'=>$force]);
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

	public function startGrabSiteRobots($site_id)
	{
		return $this->sendRequest('start-grab-site-robots', ['id'=>$site_id]);
	}

	public function stopGrabSiteRobots($site_id, $data)
	{
		return $this->sendRequest('stop-grab-site-robots', [
			'id' => $site_id,
			'data' => $data
		]);
	}

	public function getSitemap($sitemap_id)
	{
		return $this->sendRequest('get-site-page', ['id'=>$sitemap_id]);
	}

	public function startGrabSitemap($sitemap_id)
	{
		return $this->sendRequest('start-grab-sitemap', ['id'=>$sitemap_id]);
	}

	public function stopGrabSitemap($sitemap_id, $data)
	{
		return $this->sendRequest('stop-grab-sitemap', [
			'id' => $sitemap_id,
			'data' => $data
		]);
	}

	public function startGrabExternalLink($link_id)
	{
		return $this->sendRequest('start-grab-external-link', ['id'=>$link_id]);
	}

	public function stopGrabExternalLink($link_id, $data)
	{
		return $this->sendRequest('stop-grab-external-link', [
			'id' => $link_id,
			'data' => $data
		]);
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

	public function startGrabSitePage($page_id)
	{
		return $this->sendRequest('start-grab-site-page', ['id'=>$page_id]);
	}

	public function stopGrabSitePage($page_id, $data)
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

	public function startParseSitePageContent($page_id)
	{
		return $this->sendRequest('start-parse-site-page-content', ['id'=>$page_id]);
	}

	public function stopParseSitePageContent($page_id, $data)
	{
		return $this->sendRequest('stop-parse-site-page-content', [
			'id' => $page_id,
			'data' => $data
		]);
	}

	public function startParseSitePageKeyword($keyword_id)
	{
		return $this->sendRequest('start-parse-site-page-keyword', ['id'=>$keyword_id]);
	}

	public function stopParseSitePageKeyword($keyword_id, $data)
	{
		return $this->sendRequest('stop-parse-site-page-keyword', [
			'id' => $keyword_id,
			'data' => $data
		]);
	}

	public function startParseSitePageKeywords($page_id)
	{
		return $this->sendRequest('start-parse-site-page-keywords', ['page_id'=>$page_id]);
	}

	public function stopParseSitePageKeywords($data)
	{
		return $this->sendRequest('stop-parse-site-page-keywords', [
			'data' => $data
		]);
	}

	public function startGrabKeywordsSerp($keyword_id)
	{
		return $this->sendRequest('start-grab-keywords-serp', ['id'=>$keyword_id]);
	}

	public function stopGrabKeywordsSerp($keyword_id, $data)
	{
		return $this->sendRequest('stop-grab-keywords-serp', [
			'id' => $keyword_id,
			'data' => $data
		]);
	}

	public function startParseKeywordsSerp($keyword_id)
	{
		return $this->sendRequest('start-parse-keywords-serp', ['id'=>$keyword_id]);
	}

	public function stopParseKeywordsSerp($keyword_id, $data)
	{
		return $this->sendRequest('stop-parse-keywords-serp', [
			'id' => $keyword_id,
			'data' => $data
		]);
	}

	public function startTrackPageForms($page_id)
	{
		return $this->sendRequest('start-track-page-forms', ['id'=>$page_id]);
	}

	public function stopTrackPageForms($page_id)
	{
		return $this->sendRequest('stop-track-page-forms', [
			'id' => $page_id
		]);
	}
}