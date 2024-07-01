<?php
namespace ElementCounter\Model;

use ElementCounter\Enum\ReqComponentsEnum;

/**
 * This class interacts with the 'elements' table
 * 
 * @property int $id
 * @property string $name
 */
class Element extends BaseModel 
{
    /**
     * Create: an Element in the database 
     * Or: return already existing Element Id
     *
     * @param string $name 
     * 
     * @return int 
     */
    public function findOrCreate(string $name) 
    {
        $elementId = $this->prepareAndExecuteQuery(
            ReqComponentsEnum::ELEMENT, 
            ['name' => $name]
        );

        return $elementId;
    }
}