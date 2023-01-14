<?php

declare(strict_types=1);

namespace Core;

if (!extension_loaded('curl')) {
    dl('curl.so');
}

class News {

    private const APIKEY = '8977c2ae40f64c59bdaa72598ace2d2b';
    private const COUNTRIES = [
            'ae', 'ar', 'at', 'au', 'be', 'bg', 'br', 'ca', 'ch', 'cn', 'co', 'cu', 'cz', 'de', 'eg', 'fr', 'gb', 'gr',
            'hk', 'hu', 'id', 'ie', 'il', 'in', 'it', 'jp', 'kr', 'lt', 'lv', 'ma', 'mx', 'my', 'ng', 'nl', 'no', 'nz', 'ph', 'pl', 'pt',
            'ro', 'rs', 'ru', 'sa', 'se', 'sg', 'si', 'sk', 'th', 'tr', 'tw', 'ua', 'us', 've', 'za'],
            LANGUAGES = ['ar', 'en', 'cn', 'de', 'es', 'fr', 'he', 'it', 'nl', 'no', 'pt', 'ru', 'sv', 'ud'],
            CATEGORIES = ['business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology'],
            SORT = [ 'relevancy', 'popularity', 'publishedAt'];

    protected $newsData;
    protected string $url = 'https://newsapi.org/v2/';
    protected $routes = [
        'topHeadLines' => 'top-headlines',
        'everyThing' => 'everything',
        'sourceUrl' => 'sources',
    ];
    protected \CurlHandle $curl;

    public function __construct() {
        $this->curl = curl_init();
    }

    /**
     * 
     * @param string|null $q  Search keyword
     * @param string|null $sources
     * @param string|null $country in|us|cn|au etc 
     * @param string|null $category business|sports|general
     * @param int|null $page_size
     * @param int|null $page
     * @return type
     * @throws NewsException
     */
    public function getTopHeadLines(?string $q = null, ?string $sources = null, ?string $country = null, ?string $category = null, ?int $page_size = 100, ?int $page = 1) {
        $params = [];
        if (!is_null($q)) {
            $params['q'] = $q;
        }

        if (!is_null($sources) && (!is_null($country) || !is_null($category))) {
            throw new NewsException("You Cannot Use Sources with Country or Category at the same time.");
        }

        if (!is_null($sources)) {
            $params['sources'] = $sources;
        }

        if (!is_null($country)) {
            in_array($country, self::COUNTRIES) ?
                            $params['country'] = $country :
                            throw new NewsException("Invalid Country Identifier Provided");
        }

        if (!is_null($category)) {
            in_array($category, self::CATEGORIES) ?
                            $params['category'] = $category :
                            throw new NewsException("Invalid Category Identifier Provided");
        }

        if (!is_null($page_size)) {
            ($page_size >= 1 && $page_size <= 100) ?
                            $params['pageSize'] = $page_size :
                            throw new NewsException("Invalid Page_size Value Provided");
        }

        if (!is_null($page)) {
            $params['page'] = $page;
        }

        try {
            $this->_request('topHeadLines', $params);
            return $this->_statusInfo() == 200 ? $this->_decodeResult() : throw new NewsException($this->_decodeResult()->message);
        } catch (Exception $exc) {
            throw new NewsException($exc->getMessage());
        }
    }

    /**
     * Get News Sources
     * @param string|null $category
     * @param string|null $language
     * @param string|null $country
     * @return type
     * @throws NewsException
     */
    public function getSources(?string $category = null, ?string $language = 'en', ?string $country = 'in') {
        if (!is_null($category)) {
            in_array($category, self::CATEGORIES) ?
                            $params['category'] = $category :
                            throw new NewsException("Invalid Category Identifier Provided");
        }

        if (!is_null($language)) {
            in_array($language, self::LANGUAGES) ?
                            $params['language'] = $language :
                            throw new NewsException("Invalid Language Identifier Provided ");
        }

        if (!is_null($country)) {
            in_array($country, self::COUNTRIES) ?
                            $params['country'] = $country :
                            throw new NewsApiException("Invalid Country Identifier Provided");
        }
        try {
            $this->_request('', $params);
            return $this->_statusInfo() == 200 ? $this->_decodeResult() : throw new NewsException($this->_decodeResult()->message);
        } catch (Exception $e) {
            throw new NewsException($e->getMessage());
        }
    }

    /**
     * 
     * @param string|null $q Search keyword
     * @param string|null $sources Sources
     * @param string|null $domains Domains
     * @param string|null $exclude_domains Exclude Domains
     * @param string|Null $from Date From must be YYYY-MM-DD (string leanth must not be less or greater than 10)
     * @param String|Null $to Date To must be YYYY-MM-DD (string leanth must not be less or greater than 10)
     * @param string $language
     * @param string|null $sort_by
     * @param int|null $page_size
     * @param int|null $page
     * @return type
     * @throws NewsException
     */
    public function getEverything(?string $q = null, ?string $sources = null, ?string $domains = null, ?string $exclude_domains = null, ?string $from = null, ?string $to = null, string $language = 'en', ?string $sort_by = self::SORT[0], ?int $page_size = 100, ?int $page = 1) {
        $params = [];
        if (!is_null($q)) {
            $params['q'] = $q;
        }

        if (!is_null($sources)) {
            $params['sources'] = $sources;
        }

        if (!is_null($domains)) {
            $params['domains'] = $domains;
        }

        if (!is_null($exclude_domains)) {
            $params['excludeDomains'] = $exclude_domains;
        }

        if (!is_null($from)) {
            (strlen($from) == 10) ?
                            $params['from'] = $from :
                            throw new NewsException('from argument must be YYYY-MM-DD format');
        }

        if (!is_null($to)) {
            (strlen($to) == 10) ?
                            $params['to'] = $from :
                            throw new NewsException('from argument must be YYYY-MM-DD format');
        }

        if (!is_null($language)) {
            in_array($language, self::LANGUAGES) ?
                            $params['language'] = $language :
                            throw new NewsException("Invalid Language Identifier Provided ");
        }

        if (!is_null($sort_by)) {
            in_array($sort_by, self::SORT) ?
                            $params['sortBy'] = $sort_by :
                            throw new NewsException("Invalid SortBy Identifier Provided ");
        }

        if (!is_null($page_size)) {
            ($page_size >= 1 && $page_size <= 100) ?
                            $params['pageSize'] = $page_size :
                            throw new NewsException("Invalid Page_size Value Provided");
        }

        if (!is_null($page)) {
            $params['page'] = $page;
        }

        try {
            $this->_request('everyThing', $params);
            return $this->_statusInfo() == 200 ? $this->_decodeResult() : throw new NewsException($this->_decodeResult()->message);
        } catch (Exception $e) {
            throw new NewsException($e->getMessage());
        }
    }
    
    public function limitedString(string $string, int $len=101){
       return strlen($string ) <= $len ? $string : substr($string,0, $len);
    }

    public function date(string $date) {
        return date('d-m-Y h:i', strtotime($date));
    }

    /**
     * 
     * @param string $route
     * @param array|null $param
     */
    private function _request(string $route, ?array $param = null) {
        $this->_setOption(CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
        $this->_setOption(CURLOPT_URL, $this->_setUrl($route, $param));
        $this->_setOption(CURLOPT_HTTPHEADER, [
            'Content-Type:application/json',
            'Authorization: Bearer ' . self::APIKEY]);
        $this->_setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_exec();
    }

    /**
     * 
     * @param type $routes
     * @param array|null $param
     * @return type
     */
    private function _setUrl($routes, ?array $param = null) {
        if (!is_null($param) && is_array($param)) {
            return $this->url . $this->routes[$routes] . '?' . http_build_query($param);
        }
        return $this->url . $this->routes[$routes];
    }

    private function _exec() {
        $this->newsData = curl_exec($this->curl);
    }

    private function _statusInfo() {
        return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    }

    /**
     * 
     * @param int $options
     * @param mixed $value
     * @return bool
     */
    private function _setOption(int $options, mixed $value): bool {
        return curl_setopt($this->curl, $options, $value);
    }

    private function _decodeResult() {
        return json_decode($this->newsData);
    }

    public function __toString() {
        
    }

}
