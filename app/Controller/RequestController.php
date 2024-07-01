<?php
namespace ElementCounter\Controller;

use Throwable;
use RuntimeException;
use ElementCounter\Service\Request as RequestService;

class RequestController {

    /**
     * The Request Engine (as I'd like to call it)
     * @param RequestService $service
    */
    public function __construct(private RequestService $service) 
    {
        /**
         * To store and fetch cache data
         * Start: the session 
         * If: not already started
         * (Only when a request is made)
         */
        if (session_status() == PHP_SESSION_NONE) {
            /**
             * Regular gabage collection after 6mins
             */
            ini_set('session.gc_maxlifetime', 360);

            session_start();
        }
    }

    /**
     * Handles all incoming requests
     * 
     * @throws RuntimeException - To be handled further down the execution (here)
     *
     * @return void
     */
    public function handleRequest() 
    {
        try {
            /**
             * Server-side input validation
             */
            if ($this->service->validateRequest($_POST) === false) {
                throw new RuntimeException('Invalid input provided');
            }

            /**
             * Check cache for existing response
             */
            $cachedResponse = $this->service->checkRequestRespInCache();
            if (count($cachedResponse) > 0) {
                echo json_encode($cachedResponse);
                return;
            }

            $response = $this->buildNewReqAndRespond();

            echo json_encode($response);

        } catch (Throwable $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Builds: and registers every facet (model) of a new request:
     *  (Domain, Url, Element, Request)
     * To: the Request service class
     * While: recording and also setting the 
     *  request url fetch time
     * And finally: genrates and return appropriate response
     * 
     * @throws RuntimeException - To be handled further down the execution (here)
     *
     * @return array
     */
    private function buildNewReqAndRespond()
    {
        $this->service->registerNewReqComponents();

        $startTime = microtime(true); // Start recording the request time

        $pageHtml = $this->service->fetchUrlContent();
        if (!$pageHtml) {
            throw new RuntimeException('Failed to fetch content of the URL');
        }

        $endTime = microtime(true); // End request time recording
        $this->service->setResponseTime($startTime, $endTime);

        /**
         * Load: and bind the data from the fetched HTML string 
         * And then: register the new request
         */
        $this->service->loadNewReqUrlContent();

        $response = $this->service->generateResponse();

        return $response;
    }
}