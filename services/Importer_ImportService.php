<?php
namespace Craft;

class Importer_ImportService extends BaseApplicationComponent
{
    public function getUnprocessedRows()
    {
        return \Craft\craft()->db->createCommand()
            ->from('importer')
            ->where(array(
                'processed' => 0
            ))
            ->queryAll();
    }

    public function markAsProcessed($sku, $primaryKey = 'Product Code')
    {
        \Craft\craft()->db->createCommand()
                ->update('importer', array('processed' => 1), '`'.$primaryKey.'` = :sku', array(':sku' => $sku));
    }
}
