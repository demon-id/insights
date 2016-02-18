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
		'get-site' => [
			'url' => 'sites/get-site',
			'type' => 'get'
		],
		'add-site' => [
			'url' => 'sites/add-site',
			'type' => 'post'
		],
		'update-sites-ga-access-token' => [
			'url' => 'sites/update-sites-ga-access-token',
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
		'stop-crawl-site' => [
			'url' => 'sites/stop-crawl-site',
			'type' => 'post'
		],
		'start-crawl-site-ga-data' => [
			'url' => 'sites/start-crawl-site-ga-data',
			'type' => 'post'
		],
		'stop-crawl-site-ga-data' => [
			'url' => 'sites/stop-crawl-site-da-data',
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
		'get-keyword-serp' => [
			'url' => 'site-keywords/get-keyword-serp',
			'type' => 'get'
		],

		// View Sites
		'get-view-site' => [
			'url' => 'view-sites/get-view-site',
			'type' => 'get'
		],
		'get-view-site-attribute-error-data' => [
			'url' => 'view-sites/get-view-site-attribute-error-data',
			'type' => 'get'
		],
		'get-view-site-page-source' => [
			'url' => 'view-sites/get-view-site-page-source',
			'type' => 'get'
		],

		// View Site Pages
		'get-view-site-page' => [
			'url' => 'view-site-pages/get-view-site-page',
			'type' => 'get'
		],
		'get-site-map-pages' => [
			'url' => 'view-site-pages/get-site-map-pages',
			'type' => 'get'
		],
		'get-view-site-page-parents' => [
			'url' => 'view-site-pages/get-site-page-parents',
			'type' => 'get'
		],
		'get-view-site-page-children' => [
			'url' => 'view-site-pages/get-site-page-children',
			'type' => 'get'
		],
		'get-view-site-page-keywords' => [
			'url' => 'view-site-pages/get-site-page-keywords',
			'type' => 'get'
		],

		// View External Links
		'get-view-external-link' => [
			'url' => 'view-external-links/get-view-external-link',
			'type' => 'get'
		],

		// View Site Keywords
		'get-view-site-keywords' => [
			'url' => 'view-site-keywords/get-view-site-keywords',
			'type' => 'get'
		],

		// View Site Page Keywords
		'get-view-site-page-keywords-by-keyword' => [
			'url' => 'view-site-page-keywords/get-view-site-page-keywords-by-keyword',
			'type' => 'get'
		],

		// Data Sites
		'get-diagram-data' => [
			'url' => 'data-sites/get-diagram-data',
			'type' => 'get'
		],

		// Data Site Keywords
		'get-data-site-page-keyword-diagram' => [
			'url' => 'data-site-page-keywords/get-data-site-page-keywords-diagram',
			'type' => 'get'
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
			throw new \BadMethodCallException('Crawler Core Api method is undefined.');
		}

		return $this->methodParams[$name];
	}

	public function getSites()
	{
		return $this->sendRequest('get-sites');
	}

	public function getSite($site_id)
	{
		return $this->sendRequest('get-site', ['site_id'=>$site_id]);
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

	public function addSite($site_id, $url, $ga_access_token='')
	{
		return $this->sendRequest('add-site', ['site_id'=>$site_id, 'url'=>$url, 'ga_access_token'=>$ga_access_token]);
	}

	public function updateSitesGaAccessToken($site_ids, $ga_access_token='')
	{
		return $this->sendRequest('update-sites-ga-access-token', ['site_ids'=>$site_ids, 'ga_access_token'=>$ga_access_token]);
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

	public function getViewSite($site_id)
	{
		return $this->sendRequest('get-view-site', ['id'=>$site_id]);
	}

	public function getViewSiteAttributeErrorData($site_id, $attribute, $current_page)
	{
		return $this->sendRequest('get-view-site-attribute-error-data', ['site_id'=>$site_id, 'attribute'=>$attribute, 'current_page'=>$current_page]);
	}

	public function getViewSitePageSource($site_id, $attribute, $page_id, $current_page)
	{
		return $this->sendRequest('get-view-site-page-source', ['site_id'=>$site_id, 'attribute'=>$attribute, 'site_page_id'=>$page_id, 'current_page'=>$current_page]);
	}

	public function getViewSitePage($page_id)
	{
		return $this->sendRequest('get-view-site-page', ['id'=>$page_id]);
	}

	public function getSiteMapPages($site_id, $level=null, $parent_id=null, $search_string=null)
	{
		return $this->sendRequest('get-site-map-pages', ['site_id'=>$site_id, 'level'=>$level, 'parent_id'=>$parent_id, 'search_string'=>$search_string]);
	}

	public function getViewSitePageParents($page_id, $current_page)
	{
		return $this->sendRequest('get-view-site-page-parents', ['page_id'=>$page_id, 'current_page'=>$current_page]);
	}

	public function getViewSitePageChildren($page_id)
	{
		return $this->sendRequest('get-view-site-page-children', ['page_id'=>$page_id]);
	}

	public function getViewSitePageKeywords($page_id, $current_page)
	{
		return $this->sendRequest('get-view-site-page-keywords', ['page_id'=>$page_id, 'current_page'=>$current_page]);
	}

	public function getViewExternalLink($link_id)
	{
		return $this->sendRequest('get-view-external-link', ['id'=>$link_id]);
	}

	public function getViewSiteKeywords($site_id, $search_string, $current_page)
	{
		return $this->sendRequest('get-view-site-keywords', ['site_id'=>$site_id, 'search_string'=>$search_string, 'current_page'=>$current_page]);
	}

	public function getViewSitePageKeywordsByKeyword($site_id, $keyword_id, $current_page)
	{
		return $this->sendRequest('get-view-site-page-keywords-by-keyword', ['site_id'=>$site_id, 'keyword_id'=>$keyword_id, 'current_page'=>$current_page]);
	}

	public function getDiagramData($site_id, $search_date=null)
	{
		return $this->sendRequest('get-diagram-data', ['id'=>$site_id, 'search_date'=>$search_date]);
	}

	public function getDataSitePageKeywordDiagram($site_id, $page_id, $keyword_id)
	{
		return $this->sendRequest('get-data-site-page-keyword-diagram', ['site_id'=>$site_id, 'page_id'=>$page_id, 'keyword_id'=>$keyword_id]);
	}

	public function getProxy()
	{
		return $this->sendRequest('get-proxy');
	}

	public function startCrawlSite($site_id, $data)
	{
		return $this->sendRequest('start-crawl-site', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function stopCrawlSite($site_id, $data)
	{
		return $this->sendRequest('stop-crawl-site', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function startCrawlSiteGaData($site_id, $data)
	{
		return $this->sendRequest('start-crawl-site-ga-data', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function stopCrawlSiteGaData($site_id, $data)
	{
		return $this->sendRequest('stop-crawl-site-ga-data', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function startCrawlSerpKeyword($keyword_id, $data)
	{
		return $this->sendRequest('start-crawl-serp-keyword', ['keyword_id'=>$keyword_id, 'data'=>$data]);
	}

	public function stopCrawlSerpKeyword($keyword_id, $data)
	{
		return $this->sendRequest('stop-crawl-serp-keyword', ['keyword_id'=>$keyword_id, 'data'=>$data]);
	}

	public function getKeywordSerp($keyword_id)
	{
		return $this->sendRequest('get-keyword-serp', ['keyword_id'=>$keyword_id]);
	}
}