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
	public $testPartnerIds;

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
		'get-site-ga-credentials' => [
			'url' => 'sites/get-site-ga-credentials',
			'type' => 'get'
		],
		'update-site-ga-credentials' => [
			'url' => 'sites/update-site-ga-credentials',
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
			'url' => 'sites/stop-crawl-site-ga-data',
			'type' => 'post'
		],
		'start-crawl-site-domain-keywords-sr-data' => [
			'url' => 'sites/start-crawl-site-domain-keywords-sr-data',
			'type' => 'post'
		],
		'stop-crawl-site-domain-keywords-sr-data' => [
			'url' => 'sites/stop-crawl-site-domain-keywords-sr-data',
			'type' => 'post'
		],
		'start-crawl-site-domain-competitors-sr-data' => [
			'url' => 'sites/start-crawl-site-domain-competitors-sr-data',
			'type' => 'post'
		],
		'stop-crawl-site-domain-competitors-sr-data' => [
			'url' => 'sites/stop-crawl-site-domain-competitors-sr-data',
			'type' => 'post'
		],
		'start-crawl-site-competitors-data' => [
			'url' => 'sites/start-crawl-site-competitors-data',
			'type' => 'post'
		],
		'stop-crawl-site-competitors-data' => [
			'url' => 'sites/stop-crawl-site-competitors-data',
			'type' => 'post'
		],
		'check-site-available' => [
			'url' => 'sites/check-site-available',
			'type' => 'get'
		],
		'get-site-rules' => [
			'url' => 'sites/get-site-rules',
			'type' => 'get'
		],
		'update-rules' => [
			'url' => 'sites/update-rules',
			'type' => 'get'
		],

		// Keywords
		'add-keywords' => [
			'url' => 'site-keywords/add-keywords',
			'type' => 'post'
		],
		'add-locality-keywords' => [
			'url' => 'site-keywords/add-locality-keywords',
			'type' => 'post'
		],
		'get-site-keyword' => [
			'url' => 'site-keywords/get-site-keyword',
			'type' => 'get'
		],
		'get-site-keywords' => [
			'url' => 'site-keywords/get-site-keywords',
			'type' => 'get'
		],
		'get-used-keyword-localities' => [
			'url' => 'site-keywords/get-used-keyword-localities',
			'type' => 'get'
		],
		'count-sites-keywords' => [
			'url' => 'site-keywords/count-sites-keywords',
			'type' => 'get'
		],
		'count-sites-pages' => [
			'url' => 'sites/count-sites-pages',
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
		'start-crawl-keyword-keywords-sr-data' => [
			'url' => 'site-keywords/start-crawl-keyword-keywords-sr-data',
			'type' => 'put'
		],
		'stop-crawl-keyword-keywords-sr-data' => [
			'url' => 'site-keywords/stop-crawl-keyword-keywords-sr-data',
			'type' => 'put'
		],
		'set-site-keywords-queue-status' => [
			'url' => 'site-keywords/set-site-keywords-queue-status',
			'type' => 'get'
		],
		'get-stop-words' => [
			'url' => 'site-keywords/get-stop-words',
			'type' => 'get'
		],

		// Site keywords localities
		'get-site-keyword-localities' => [
			'url' => 'site-keyword-localities/get-site-keyword-localities',
			'type' => 'get'
		],
		'add-site-keyword-localities' => [
			'url' => 'site-keyword-localities/add-site-keyword-localities',
			'type' => 'post'
		],
		'delete-site-keyword-locality' => [
			'url' => 'site-keyword-localities/delete-site-keyword-locality',
			'type' => 'post'
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
		'get-view-site-page-diagram' => [
			'url' => 'view-site-pages/get-view-site-page-diagram',
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
		'get-data-site-page-keyword-diagram' => [
			'url' => 'data-site-page-keywords/get-data-site-page-keywords-diagram',
			'type' => 'get'
		],

		// View Site GA Data
		'get-view-site-ga-data' => [
			'url' => 'view-site-ga-data/get-site-data',
			'type' => 'get'
		],

		// View Content Effective Pages
		'get-view-content-effective-pages' => [
			'url' => 'view-content-effective-pages/get-view-content-effective-pages',
			'type' => 'get'
		],

		// Data Content Effective Pages
		'get-data-content-effective-pages' => [
			'url' => 'view-content-effective-pages/get-data-content-effective-pages',
			'type' => 'get'
		],

		// View Site Page Keywords
		'get-view-site-page-keywords-by-keyword' => [
			'url' => 'view-site-page-keywords/get-view-site-page-keywords-by-keyword',
			'type' => 'get'
		],

		// Data Sites
		'get-errors-diagram-data' => [
			'url' => 'data-sites/get-errors-diagram-data',
			'type' => 'get'
		],
		'get-data-site-page-grades' => [
			'url' => 'data-sites/get-data-site-page-grades',
			'type' => 'get'
		],
		'get-data-site-top-keywords' => [
			'url' => 'data-sites/get-data-site-top-keywords',
			'type' => 'get'
		],

		//Keywords Suggest
		'get-keywords-suggest-data' => [
			'url' => 'keyword-keywords-suggest/get-site-keywords-suggest-data',
			'type' => 'get'
		],
		'hide-keyword-suggest-data' => [
			'url' => 'keyword-keywords-suggest/hide-site-keyword-suggest',
			'type' => 'post'
		],
		'unhide-keyword-suggest-data' => [
			'url' => 'keyword-keywords-suggest/unhide-site-keyword-suggest',
			'type' => 'post'
		],

		//Competitors
		'get-site-competitors-data' => [
			'url' => 'view-site-competitors/get-site-competitors-data',
			'type' => 'get'
		],

		// Data Site GA Data
		'get-data-site-ga-data' => [
			'url' => 'data-site-ga-data/get-site-data',
			'type' => 'get'
		],

		// Proxy
		'get-proxy' => [
			'url' => 'proxy/get-proxy',
			'type' => 'get'
		],

		// Seomoz data
		'get-seomoz-data' => [
			'url' => 'seomoz-data/get-seomoz-data',
			'type' => 'get'
		],
		'add-seomoz-data' => [
			'url' => 'seomoz-data/add-seomoz-data',
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

	public function addSite($site_id, $url)
	{
		return $this->sendRequest('add-site', ['site_id'=>$site_id, 'url'=>$url]);
	}

	public function getSitesGaCredentials($site_id)
	{
		return $this->sendRequest('get-site-ga-credentials', ['site_id'=>$site_id]);
	}

	public function updateSitesGaCredentials($site_id, $ga_profile_id=null, $ga_access_token=null)
	{
		return $this->sendRequest('update-site-ga-credentials', ['site_id'=>$site_id, 'ga_profile_id'=>$ga_profile_id, 'ga_access_token'=>$ga_access_token]);
	}

	public function deleteSite($site_id)
	{
		return $this->sendRequest('delete-site', ['site_id'=>$site_id]);
	}

	public function hardDeleteSite($site_id)
	{
		return $this->sendRequest('hard-delete-site', ['site_id'=>$site_id]);
	}

	public function getSiteRules($site_id)
	{
		return $this->sendRequest('get-site-rules', ['site_id'=>$site_id]);
	}

	public function updateRules($site_id, $rules)
	{
		return $this->sendRequest('update-rules', ['site_id'=>$site_id, 'rules'=>$rules]);
	}

	public function addKeywords($site_id, $keywords=[])
	{
		return $this->sendRequest('add-keywords', ['site_id'=>$site_id, 'keywords'=>$keywords]);
	}

	public function addLocalityKeywords($site_id, $keyword_id, $locality_ids=[])
	{
		return $this->sendRequest('add-locality-keywords', ['site_id'=>$site_id, 'keyword_id'=>$keyword_id, 'locality_ids'=>$locality_ids]);
	}

	public function getSiteKeyword($site_id, $keyword_id)
	{
		return $this->sendRequest('get-site-keyword', ['site_id'=>$site_id, 'keyword_id'=>$keyword_id]);
	}

	public function getSiteKeywords($site_id)
	{
		return $this->sendRequest('get-site-keywords', ['site_id'=>$site_id]);
	}

	public function getUsedKeywordLocalities($keyword_id, $site_id=null)
	{
		return $this->sendRequest('get-used-keyword-localities', ['keyword_id'=>$keyword_id, 'site_id'=>$site_id]);
	}

	public function deleteSiteKeyword($id, $site_id)
	{
		return $this->sendRequest('delete-site-keyword', ['id'=>$id, 'site_id'=>$site_id]);
	}

	public function countSitesKeywords($site_ids)
	{
		return $this->sendRequest('count-sites-keywords', ['site_ids'=>$site_ids]);
	}

	public function countSitesPages($site_ids)
	{
		return $this->sendRequest('count-sites-pages', ['site_ids'=>$site_ids]);
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

	public function getSiteMapPages($site_id, $level=null, $search_string=null, $current_page=0)
	{
		return $this->sendRequest('get-site-map-pages', ['site_id'=>$site_id, 'level'=>$level, 'search_string'=>$search_string, 'current_page'=>$current_page]);
	}

	public function getViewSitePageParents($page_id, $current_page)
	{
		return $this->sendRequest('get-view-site-page-parents', ['page_id'=>$page_id, 'current_page'=>$current_page]);
	}

	public function getViewSitePageChildren($page_id, $page=0, $filter=null)
	{
		return $this->sendRequest('get-view-site-page-children', ['page_id'=>$page_id, 'current_page'=>$page, 'filter'=>$filter]);
	}

	public function getViewSitePageKeywords($page_id, $current_page)
	{
		return $this->sendRequest('get-view-site-page-keywords', ['page_id'=>$page_id, 'current_page'=>$current_page]);
	}

	public function getViewExternalLink($link_id)
	{
		return $this->sendRequest('get-view-external-link', ['id'=>$link_id]);
	}

	public function getViewSiteKeywords($site_id, $search_string=null, $current_page=0, $sort=null, $grades = null, $queues = null, $difficulties = null)
	{
                return $this->sendRequest('get-view-site-keywords', ['site_id'=>$site_id, 'search_string'=>$search_string, 'current_page'=>$current_page, 'sort'=>$sort, 'grades'=>$grades, 'queues'=>$queues, 'difficulties'=>$difficulties]);
	}

	public function getViewSitePageKeywordsByKeyword($site_id, $keyword_id, $current_page)
	{
		return $this->sendRequest('get-view-site-page-keywords-by-keyword', ['site_id'=>$site_id, 'keyword_id'=>$keyword_id, 'current_page'=>$current_page]);
	}

	public function getViewSiteGaData($site_id)
	{
		return $this->sendRequest('get-view-site-ga-data', ['site_id'=>$site_id]);
	}

	public function getViewContentEffectivePages($site_id)
	{                
                return $this->sendRequest('get-view-content-effective-pages', ['site_id'=>$site_id]);
	}

	public function getDataContentEffectivePages($site_id, $period=1, $current_page=0)
	{                
                return $this->sendRequest('get-data-content-effective-pages', ['site_id'=>$site_id, 'period'=>$period, 'current_page'=>$current_page]);
	}

	public function getErrorsDiagramData($site_id, $search_date=null)
	{
		return $this->sendRequest('get-errors-diagram-data', ['id'=>$site_id, 'search_date'=>$search_date]);
	}

	public function getDataSitePageGrades($site_id, $search_date=null)
	{
		return $this->sendRequest('get-data-site-page-grades', ['id'=>$site_id, 'search_date'=>$search_date]);
	}

	public function getDataSiteTopKeywords($site_id, $search_date=null)
	{
		return $this->sendRequest('get-data-site-top-keywords', ['id'=>$site_id, 'search_date'=>$search_date]);
	}

	public function getDataSitePageKeywordDiagram($site_id, $page_id, $keyword_id)
	{
		return $this->sendRequest('get-data-site-page-keyword-diagram', ['site_id'=>$site_id, 'page_id'=>$page_id, 'keyword_id'=>$keyword_id]);
	}

	public function getViewSitePageDiagram($site_id, $page_id)
	{
		return $this->sendRequest('get-view-site-page-diagram', ['site_id'=>$site_id, 'page_id'=>$page_id]);
	}

	public function getDataSiteGaData($site_id)
	{
		return $this->sendRequest('get-data-site-ga-data', ['site_id'=>$site_id]);
	}

	public function getKeywordsSuggestData($site_id, $type, $keywords_ids, $search_string=null, $current_page=0, $sort=null, $volume_greater=null, $volume_less=null, $competition=null)
	{
		return $this->sendRequest('get-keywords-suggest-data', ['site_id'=>$site_id, 'type'=>$type,  'keywords_ids'=>$keywords_ids, 'search_string'=>$search_string, 'current_page'=>$current_page, 'sort'=>$sort, 'volume_greater'=>$volume_greater, 'volume_less'=>$volume_less, 'competition'=>$competition]);
	}

	public function hideKeywordSuggest($site_id, $keyword_id)
	{
		return $this->sendRequest('hide-keyword-suggest-data', ['site_id'=>$site_id, 'keyword_id'=>$keyword_id]);
	}

	public function unhideKeywordSuggest($site_id, $keyword_id)
	{
		return $this->sendRequest('unhide-keyword-suggest-data', ['site_id'=>$site_id, 'keyword_id'=>$keyword_id]);
	}

	public function getSiteCompetitorsData($site_id, $current_page=0, $sort=null)
	{
		return $this->sendRequest('get-site-competitors-data', ['site_id'=>$site_id, 'current_page'=>$current_page, 'sort'=>$sort]);
	}

	public function startCrawlSite($site_id, $data)
	{
		return $this->sendRequest('start-crawl-site', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function stopCrawlSite($site_id, $crawler_name, $data)
	{
		return $this->sendRequest('stop-crawl-site', ['site_id'=>$site_id, 'crawler_name'=>$crawler_name, 'data'=>$data]);
	}

	public function startCrawlSiteGaData($site_id, $data)
	{
		return $this->sendRequest('start-crawl-site-ga-data', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function stopCrawlSiteGaData($site_id, $crawler_name, $data)
	{
		return $this->sendRequest('stop-crawl-site-ga-data', ['site_id'=>$site_id, 'crawler_name'=>$crawler_name, 'data'=>$data]);
	}

	public function startCrawlSiteDomainKeywordsSrData($site_id, $data)
	{
		return $this->sendRequest('start-crawl-site-domain-keywords-sr-data', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function stopCrawlSiteDomainKeywordsSrData($site_id, $crawler_name, $data)
	{
		return $this->sendRequest('stop-crawl-site-domain-keywords-sr-data', ['site_id'=>$site_id, 'crawler_name'=>$crawler_name, 'data'=>$data]);
	}

	public function startCrawlSiteDomainCompetitorsSrData($site_id, $data)
	{
		return $this->sendRequest('start-crawl-site-domain-competitors-sr-data', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function stopCrawlSiteDomainCompetitorsSrData($site_id, $crawler_name, $data)
	{
		return $this->sendRequest('stop-crawl-site-domain-competitors-sr-data', ['site_id'=>$site_id, 'crawler_name'=>$crawler_name, 'data'=>$data]);
	}

	public function startCrawlSiteCompetitorsData($site_id, $data)
	{
		return $this->sendRequest('start-crawl-site-competitors-data', ['site_id'=>$site_id, 'data'=>$data]);
	}

	public function stopCrawlSiteCompetitorsData($site_id, $crawler_name, $data)
	{
		return $this->sendRequest('stop-crawl-site-competitors-data', ['site_id'=>$site_id, 'crawler_name'=>$crawler_name, 'data'=>$data]);
	}

	public function startCrawlSerpKeyword($keyword_id, $data)
	{
		return $this->sendRequest('start-crawl-serp-keyword', ['keyword_id'=>$keyword_id, 'data'=>$data]);
	}

	public function stopCrawlSerpKeyword($keyword_id, $crawler_name, $data)
	{
		return $this->sendRequest('stop-crawl-serp-keyword', ['keyword_id'=>$keyword_id, 'crawler_name'=>$crawler_name, 'data'=>$data]);
	}

	public function getKeywordSerp($keyword_id)
	{
		return $this->sendRequest('get-keyword-serp', ['keyword_id'=>$keyword_id]);
	}

	public function startCrawlKeywordKeywordsSrData($keyword_id, $data)
	{
		return $this->sendRequest('start-crawl-keyword-keywords-sr-data', ['keyword_id'=>$keyword_id, 'data'=>$data]);
	}

	public function stopCrawlKeywordKeywordsSrData($keyword_id, $crawler_name, $data)
	{
		return $this->sendRequest('stop-crawl-keyword-keywords-sr-data', ['keyword_id'=>$keyword_id, 'crawler_name'=>$crawler_name, 'data'=>$data]);
	}

	public function getProxy()
	{
		return $this->sendRequest('get-proxy');
	}

	public function getSeomozData($urls_hash, $last_update_date=null)
	{
		return $this->sendRequest('get-seomoz-data', ['urls_hash'=>$urls_hash, 'last_update_date'=>$last_update_date]);
	}

	public function addSeomozData($url, $data, $last_update_date)
	{
		return $this->sendRequest('add-seomoz-data', ['url'=>$url, 'data'=>$data, 'last_update_date'=>$last_update_date]);
	}

	public function getSiteKeywordLocalities($site_id, $search_word=null, $current_page=0, $sort=null)
	{
		return $this->sendRequest('get-site-keyword-localities', ['site_id'=>$site_id, 'search_word'=>$search_word, 'current_page'=>$current_page, 'sort'=>$sort]);
	}

	public function addSiteKeywordLocalities($site_id, $localities=[])
	{
		return $this->sendRequest('add-site-keyword-localities', ['site_id'=>$site_id, 'localities'=>$localities]);
	}

	public function deleteSiteKeywordLocality($id, $site_id)
	{
		return $this->sendRequest('delete-site-keyword-locality', ['id'=>$id, 'site_id'=>$site_id]);
	}
        
	public function setSiteKeywordsQueueStatus($site_id, $keyword_id, $status)
	{
		return $this->sendRequest('set-site-keywords-queue-status', ['site_id'=>$site_id, 'keyword_id'=>$keyword_id, 'status'=>$status]);
	}

	public function getStopWords()
	{            
		return $this->sendRequest('get-stop-words', []);
	}
}