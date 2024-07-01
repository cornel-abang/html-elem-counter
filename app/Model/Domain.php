<?php
namespace ElementCounter\Model;

use ElementCounter\Enum\ReqComponentsEnum;

/**
 * This class interacts with the 'domains' table
 * 
 * @property int $id
 * @property string $name
 */
class Domain extends BaseModel 
{
    /**
     * Create: a Domain in the database 
     * Or: return already existing Domain Id
     *
     * @param string $name 
     * 
     * @return int 
     */
    public function findOrCreate(string $name) 
    {
        $domainId = $this->prepareAndExecuteQuery(
            ReqComponentsEnum::DOMAIN, 
            ['name' => $name]
        );

        return $domainId;
    }
}