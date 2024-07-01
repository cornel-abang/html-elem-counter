<?php
namespace ElementCounter\Model;

use ElementCounter\Enum\ReqComponentsEnum;

/**
 * This class interacts with the 'urls' table
 * 
 * @property int $id
 * @property int $domain_id
 * @property string $name
 */
class Url extends BaseModel 
{
    /**
     * Create: a Url in the database 
     * Or: return already existing Url Id
     *
     * @param string $url 
     * @param int $domainId 
     * 
     * @return int 
     */
    public function findOrCreate(string $url, int $domainId) 
    {
        $urlId = $this->prepareAndExecuteQuery(
            ReqComponentsEnum::URL, 
            ['name' => $url, 'domain_id' => $domainId]
        );

        return $urlId;
    }
}