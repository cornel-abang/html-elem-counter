<?php
namespace ElementCounter\Model;

use PDO;
use RuntimeException;
use ElementCounter\Service\Database;
use ElementCounter\Enum\ReqComponentsEnum;

class BaseModel {
    /**
     * @var PDO $pdo - database instance
     */
    protected static PDO $pdo;
    
    public function __construct() {
        self::$pdo = Database::buildAndReturnDbInstance();
    }

    /**
     * Prepare: and execute SQL statements.
     * EXCEPT: from Request model
     * That is handled by: self::handleRequestModelQueries()
     *
     * @param ReqComponentsEnum $compEnum - (An ELEMENT, DOMAIN or URL.)
     * @param array $params
     * @param int $fetchMode = PDO::FETCH_DEFAULT
     * 
     * @return int 
     */
    protected static function prepareAndExecuteQuery(
        ReqComponentsEnum $compEnum, 
        array $params, 
        int $fetchMode = PDO::FETCH_DEFAULT
    )
    {
        $stmt = self::$pdo->prepare($compEnum->getSqlSelectStatement());
        $stmt->execute($params);
        $component = $stmt->fetch($fetchMode);

        if ($component) {
            return $component['id'];
        } else {
            /**
             * Doesnt exist: run the component's insert query 
             * And: return its Id
             */
            $stmt = self::$pdo->prepare(
                $compEnum->getMainSqlInsertStatement()
            );
            $stmt->execute($params);

            return self::$pdo->lastInsertId();
        }
    }

    /**
     * Prepare and execute Request model SQL statements.
     * (Exclusively)
     *
     * @param ReqComponentsEnum $compEnum (REQUEST only)
     * @param array $params
     * @param array $statementType = ""
     * @param int $fetchMode = PDO::FETCH_DEFAULT
     * 
     * @throws RuntimeException
     * To: be handled further up the execution
     * By: \ElementCounter\Controller\RequestController
     * 
     * @return mixed 
     */
    protected static function handleRequestModelQueries(
        ReqComponentsEnum $compEnum,
        array $params,
        string $statementType = "",
        int $fetchMode = PDO::FETCH_DEFAULT
    ) 
    {
        $sqlStatement = match ($statementType) {
            'url_count' => $compEnum->getDomainUrlsCheckedStatement(),
            'avg_response_time' => $compEnum->getAvgCheckTimeStatement(),
            'total_element_count_domain' => $compEnum->getTotalElemCountStatement(),
            'total_element_count_all' => $compEnum->getTotalElemCountFromAllStatement(),
            'find' => $compEnum->getFindRequestStatement(),
            'create' => $compEnum->getMainSqlInsertStatement(),
            default => throw new RuntimeException('Runtime error - Invalid statistic type encountered'),
        };

        $stmt = self::$pdo->prepare($sqlStatement);
        $stmt->execute($params);

        return $stmt->fetch($fetchMode);
    }

}