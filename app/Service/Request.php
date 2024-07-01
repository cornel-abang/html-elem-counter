<?php

namespace ElementCounter\Service;

use DOMDocument;
use RuntimeException;
use ElementCounter\Model\Url;
use ElementCounter\Model\Domain;
use ElementCounter\Model\Element;
use ElementCounter\Model\Request as RequestModel;

class Request
{
    private RequestModel $currentRequest;
    private string|bool $domainName = '';
    private int $domainId = 0;
    private string|bool $url = '';
    private int $urlId = 0;
    private string|bool $element = '';
    private int $elementId = 0;
    private int $elementCount = 0;
    private string|bool $pageHtml = '';
    private float $responseTime = 0.0;

    /**
     * Validate the incoming user request.
     * 
     * @param array $request
     *
     * @return bool
     */
    public function validateRequest(array $request)
    {
        $url = $this->validateUrl($request['url'] ?? null);
        $element = $this->validateElement($request['element'] ?? null);

        if (!$url || !$element) {
            return false;
        }

        $this->url = $url;
        $this->element = $element;

        return true;
    }

    /**
     * Create or find: the request components:
     *  (Domain, Url and Element)
     * And: bind their respective data 
     * To: the Request service  class.
     * 
     * @return void
     */
    public function registerNewReqComponents()
    {
        $this->domainName = parse_url($this->url, PHP_URL_HOST);
        
        $domain = new Domain();
        $this->domainId = $domain->findOrCreate($this->domainName);

        $urlModel = new Url();
        $this->urlId = $urlModel->findOrCreate($this->url, $this->domainId);

        $elementModel = new Element();
        $this->elementId = $elementModel->findOrCreate($this->element);
    }

    /**
     * Using cURL: load the request url 
     * And: bind the success result 
     * To: the Request  service class
     * As: HTML a string.
     * 
     * @throws RuntimeException 
     * To: be handled further up the execution
     * By: \ElementCounter\Controller\RequestController
     * 
     * @return string
     */
    public function fetchUrlContent()
    {
        $ch = curl_init();

        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL session');
        }

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        /**
         * In an attempt to solve the issue 
         * Of: getting blocked due to odd/suspicious requests
         * I choose: a browser imitation approach
         * To: imitate a browser request
         */
        $browserAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        curl_setopt($ch, CURLOPT_USERAGENT, $browserAgent);
        
        $html = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new RuntimeException("Connection error: $error");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {

            if (!$this->htmlIsValid($html)) {
                throw new RuntimeException("Invalid HTML content received");
            }
            return $this->pageHtml = $html; // harmless 'return' (useful for code testing)
        } elseif ($httpCode == 404) {
            throw new RuntimeException("The URL does not exist");
        } else {
            throw new RuntimeException("Failed to fetch URL - HTTP code: $httpCode");
        }
    }

    /**
     * Calculate & set: the amount of time it took in msec
     * To: visit the request url
     * And: bind the success reponse 
     * To: the Request service class
     * 
     * @param float $startTime
     * @param float $endTime
     * 
     * @return void
     */
    public function setResponseTime(float $startTime, float $endTime)
    {
        $this->responseTime = ($endTime - $startTime) * 1000;
    }

    /**
     * Using PHP DOMDocument: load the fetched html string 
     * From: the request url
     * And: count & bind the no. of the request element 
     * To: the Request class
     * And: then register the new Request (model)
     * And finally: bind the new Request model
     * To: the Request service class
     * As: the currentRequest
     * 
     * @return void
     */
    public function loadNewReqUrlContent()
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($this->pageHtml);
        $elements = $dom->getElementsByTagName($this->element);
        $this->elementCount = $elements->length;

        $this->currentRequest = RequestModel::create([
            'domain_id' => $this->domainId,
            'url_id' => $this->urlId,
            'element_id' => $this->elementId,
            'request_time' => date('Y-m-d H:i:s'),
            'response_time' => $this->responseTime,
            'element_count' => $this->elementCount
        ]);
    }

    /**
     * Generate: appropriate response
     * Using: the current Request state
     * And then caches it.
     * 
     * @return array
     */
    public function generateResponse()
    {
        $stats = $this->fetchRequestStatistics();
        $element = $this->getSearchedElement();

        $response = [
            'request_results' => sprintf(
                "URL: %s Fetched on %s, took %dmsec.\nElement: <%s> appeared %d times in page.\n",
                $this->url,
                date('d/m/Y H:i'),
                $this->responseTime,
                $element,
                $this->elementCount
            ),
            'statistics' => sprintf(
                "General Statistics:\n%d different URLs from %s have been fetched\n
                Average fetch time from %s during the last 24 hours is %d ms\n
                There was a total of %d <%s> elements from %s\nTotal of %d <%s> elements counted in all requests ever made.",
                $stats['url_count'],
                $this->domainName,
                $this->domainName,
                $stats['avg_response_time'],
                $stats['total_element_count_domain'],
                $element,
                $this->domainName,
                $stats['total_element_count_all'],
                $element
            )
        ];

        $this->cacheRequestResponse($response);

        return $response;
    }

    /**
     * Using: PHP sessions for simplicity
     * Find: the current Request response
     * In: cache
     * And return if not expired (less than 5mins ago)
     * 
     * For a more robust: I'd use Redis
     * 
     * @return array
     */
    public function checkRequestRespInCache()
    {
        $cacheKey = $this->getRequestCacheKey();
        $cachedResponse = [];
        
        if (isset($_SESSION['request_cache'][$cacheKey])) 
        {
            $cached = $_SESSION['request_cache'][$cacheKey];
            /**
             * Is it still valid (< 5mins) ?
             * 
             * 300 seconds == 5 minutes
             */
            $cachedTime = $cached['timestamp'];
            $currentTime = time();
            if ($currentTime - $cachedTime <= 300) 
            { 
                $cachedResponse = $cached['data'];
            }
        }

        return $cachedResponse;
    }

    /**
     * Generate: cache key 
     * From: the current reuest Url and Element
     * With: md5() each unique combination 
     * Gets: its own cache entry
     * 
     * @return string
     */
    private function getRequestCacheKey()
    {
        return md5($this->url . $this->element);
    }

    /**
     * Using: htmlspecialchars()
     * Cleanup: the request element 
     * And: return a safe HTML text for browser display.
     * 
     * @return string
     */
    private function getSearchedElement()
    {
        return htmlspecialchars($this->element, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Fetch the current request statistics
     * 
     * @return array
     */
    private function fetchRequestStatistics()
    {
        return $this->currentRequest->generateStatistics(
            $this->domainId,
            $this->elementId
        );
    }

    /**
     * Validates and sanitizes a request Url
     * ONLY: valid format Urls returned
     * Or: null
     * 
     * @param mixed $url
     * 
     * @return string|null
     */
    private function validateUrl(mixed $url)
    {
        $validatedUrl = filter_var($url, FILTER_VALIDATE_URL);

        return $validatedUrl ? filter_var($validatedUrl, FILTER_SANITIZE_URL) : null;
    }

    /**
     * Validates and sanitizes a request Element
     * 
     * @param mixed $element
     * 
     * @return string|null
     */
    private function validateElement(mixed $element)
    {
        /**
         * Check: for non-empty string
         * And: return sanitized 
         * Or: null
         */
        if (is_string($element) && !empty($element)) 
        {
            $validElement = strip_tags($element);

            return $validElement;
        }

        return null;
    }

    /**
     * Cache the current request response
     * 
     * @param array $response
     * 
     * @return void
     */
    private function cacheRequestResponse(array $response)
    {
        $cacheKey = $this->getRequestCacheKey();
        $_SESSION['request_cache'][$cacheKey] = [
            'timestamp' => time(),
            'data' => $response,
        ];
    }

    /**
     * Check: if the given HTML string
     * Is: a valid HTML page
     *  
     * @param string $html 
     * 
     * @return bool
     */
    private function htmlIsValid(string $html)
    {
        /**
         * Simple check for valid HTML 
         * (Task Guideline 10 assumption)
         */
        return stripos($html, '<html') !== false;
    }
}