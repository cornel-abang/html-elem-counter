<?php
namespace ElementCounter\Model;

use PDO;
use DateTime;
use ElementCounter\Enum\ReqComponentsEnum;

/**
 * This class interacts with the 'requests' table
 * 
 * @property int $id
 * @property int $domain_id
 * @property int $url_id
 * @property int $element_id
 * @property DateTime $request_time
 * @property int $response_time
 * @property int $element_count
 */
class Request extends BaseModel {

    public function __construct(
        public $domain_id, public $url_id,  
        public $element_id, public $request_time, 
        public $response_time, public $element_count,
        public $id = 0
    ) {}

    /**
     * Fetch a Request record from database
     * 
     * @return array
    */
    private static function find(int $id)
    {
        $result = self::handleRequestModelQueries(
            ReqComponentsEnum::REQUEST, 
            ['id' => $id], 
            'find',
            PDO::FETCH_ASSOC
        );
        
        return $result;
    }

    /**
     * Register a new request
     * 
     * @param array $data
     * 
     * @return array
    */
    public static function create(array $data) 
    {
        self::handleRequestModelQueries(
            ReqComponentsEnum::REQUEST, 
            $data,
            "create"
        );

        $lastInsertId = self::$pdo->lastInsertId();
        $justCreatedRequest = self::find($lastInsertId);

        /**
         * Return the record as a Request model object
         */
        return new Request(
            $justCreatedRequest['id'],
            $justCreatedRequest['domain_id'],
            $justCreatedRequest['url_id'],
            $justCreatedRequest['element_id'],
            $justCreatedRequest['request_time'],
            $justCreatedRequest['response_time'],
            $justCreatedRequest['element_count']
        );
    }

    /**
     * Get current request statistics from the database 
     * 
     * @param int $domainId
     * @param int $elementId
     * 
     * @return array
    */
    public function generateStatistics($domainId, $elementId) 
    {
        $stats = [];

        /**
         * Count how many URLs of that domain 
         * have been checked till now
         */
        $sqlStatement = "url_count";
        $result = $this->handleRequestModelQueries(
            ReqComponentsEnum::REQUEST, 
            ['domain_id' => $domainId],
            $sqlStatement
        );
        $stats["url_count"] = $result[$sqlStatement];

        /**
         * Average page fetch time from that domain 
         * during the last 24 hours
         */
        $sqlStatement = "avg_response_time";
        $result = $this->handleRequestModelQueries(
            ReqComponentsEnum::REQUEST, 
            ['domain_id' => $domainId],
            $sqlStatement
        );
        $stats["avg_response_time"] = $result[$sqlStatement];

        /**
         * Total count of this element from this domain
         */
        $sqlStatement = "total_element_count_domain";
        $result = $this->handleRequestModelQueries(
            ReqComponentsEnum::REQUEST, 
            ['domain_id' => $domainId, 'element_id' => $elementId],
            $sqlStatement
        );
        $stats["total_element_count_domain"] = $result[$sqlStatement];

        /**
         * Total count of this element from ALL requests ever made
         */
        $sqlStatement = "total_element_count_all";
        $result = $this->handleRequestModelQueries(
            ReqComponentsEnum::REQUEST, 
            ['element_id' => $elementId],
            $sqlStatement
        );
        $stats["total_element_count_all"] = $result[$sqlStatement];


        return $stats;
    }
}