<?php
namespace Craft;

class ImporterCommand extends BaseCommand
{
    const SECTION = 1;
    
    public function actionRun()
    {
        // Get the unprocessed rows
        $rows = craft()->importer_import->getUnprocessedRows();
        $total = count($rows);
        
        // Initialise a progress bar
        $progressBar = new \ProgressBar\Manager(0, $total);
        
        // Loop through each row
        $count = 0;
        foreach($rows AS $row)
        {
            $this->processRow($row);
            
            craft()->importer_import->markAsProcessed($row['Slug']);
            
            $progressBar->update($count);
            $count++;
        }
    }
    
    private function processRow($row)
    {
        $entry = craft()->importer_entry->getBySlug($row['Slug']);
        if (is_null($entry))
            $entry = craft()->importer_entry->createEntry($row['Slug'], self::SECTION);
            
        $entry->getContent()->title = $row['Name'];
        
        // Remove columns not required
        unset($row['Slug'], $row['Name']);
        
        // Loop through remaining columns - our custom fields
        craft()->importer_entry->parseFields($entry, $row);
        
        craft()->importer_entry->save($entry);
    }
}