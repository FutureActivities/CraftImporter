<?php
namespace Craft;

class Importer_ImageService extends BaseApplicationComponent
{
    public function getImage($filename, $sourceId = 1)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Asset);
        $criteria->filename = $filename;
        $criteria->sourceId = $sourceId;
        $file = craft()->assets->findFile($criteria);
        
        if ($file)
            return (int)$file->id;
            
        return null;
    }
    
    public function getImages(array $filenames, $sourceId = 1)
    {
        $result = [];
        foreach ($filenames AS $filename) {
            if ($id = $this->getImage($filename, $sourceId))
                $result[] = $id;
        }
        
        return $result;
    }
}