<?php

namespace ElementCounter\Enum;

/**
 * This enum class: manages all the request components/model:
 *  (Domain, Url, Element, Request)
 * From: a singel point (Here)
 * 
 * The value: property of each enum case 
 * Represents: the component/model's table 
 * On: the database
 */
enum ReqComponentsEnum: string
{
    case DOMAIN = "domains";
    case URL = "urls";
    case ELEMENT = "elements";
    case REQUEST = "requests";

    /**
     * @return string (the SQL SELECT statement).
     */
    public function getSqlSelectStatement()
    {
        return match($this) 
        {
            self::DOMAIN => "SELECT id FROM ". self::DOMAIN->value ." WHERE name = :name",
            self::URL => "SELECT id FROM ". self::URL->value ." WHERE name = :name AND domain_id = :domain_id",
            self::ELEMENT => "SELECT id FROM ". self::ELEMENT->value ." WHERE name = :name",
            default => "",
        };
    }

    /**
     * @return string (the SQL INSERT statement).
     */
    public function getMainSqlInsertStatement()
    {
        return match($this) 
        {
            self::DOMAIN => "INSERT INTO ". self::DOMAIN->value ." (name) VALUES (:name)",
            self::URL => "INSERT INTO ". self::URL->value ." (name, domain_id) VALUES (:name, :domain_id)",
            self::ELEMENT => "INSERT INTO ". self::ELEMENT->value ." (name) VALUES (:name)",
            self::REQUEST => "INSERT INTO " . self::REQUEST->value . " (domain_id, url_id, element_id, request_time, response_time, element_count)
                VALUES (:domain_id, :url_id, :element_id, :request_time, :response_time, :element_count)",
            default => "",
        };
    }

    /**
     * Ruturns SQL query 
     * That: retrieves a single Request record
     *
     * @return string
     */
    public function getFindRequestStatement()
    {
        return "SELECT * FROM " . self::REQUEST->value . " WHERE id = :id";
    }

    /**
     * 
     * THIS section down..
     * Covers: all Request statistics SQL query statements
     * 
     * Abbr: calcs == calculates
     * 
    */


    /**
     * Ruturns SQL query 
     * That: counts how many URLs of a domain 
     * have been checked till now
     *
     * @return string
     */
    public function getDomainUrlsCheckedStatement()
    {
        return "SELECT COUNT(DISTINCT url_id) AS url_count FROM " . self::REQUEST->value . " WHERE domain_id = :domain_id";
    }

    /**
     * Ruturns SQL query 
     * That: calcs average page fetch time (request_time) from a domain 
     * during the last 24 hours - (request_time >= NOW() - INTERVAL 1 DAY)
     *
     * @return string
     */
    public function getAvgCheckTimeStatement()
    {
        return "SELECT AVG(response_time) AS avg_response_time
            FROM " . self::REQUEST->value .
            " WHERE domain_id = :domain_id AND request_time >= NOW() - INTERVAL 1 DAY";
    }

    /**
     * Ruturns SQL query 
     * That: calcs total count of an element from a domain
     *
     * @return string
     */
    public function getTotalElemCountStatement()
    {
        return "SELECT SUM(element_count) AS total_element_count_domain FROM "
            . self::REQUEST->value .
            " WHERE domain_id = :domain_id AND element_id = :element_id";
    }

    /**
     * Ruturns SQL query 
     * That: calcs total count of this element from ALL requests ever made
     *
     * @return string
     */
    public function getTotalElemCountFromAllStatement()
    {
        return "SELECT SUM(element_count) AS total_element_count_all
            FROM "   . self::REQUEST->value ." WHERE element_id = :element_id";
    }
}