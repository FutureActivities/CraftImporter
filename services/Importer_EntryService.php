<?php
namespace Craft;

class Importer_EntryService extends BaseApplicationComponent
{
    private $fields = null;

    /**
     * Get a entry by its slug
     * 
     * @param string $slug
     * @return Entry
     */
    public function getBySlug($slug)
    {
        $criteria = \Craft\craft()->elements->getCriteria(\Craft\ElementType::Entry);
        $criteria->slug = $slug;
        return $criteria->first();
    }
    
    /**
     * Create a new entry
     * 
     * @param string $slug
     * @param int $sectionId
     * @return Entry
     */
    public function createEntry($slug, $sectionId)
    {
        $entry = new \Craft\EntryModel();
        $entry->sectionId = $sectionId;
        $entry->slug = $slug;
        
        return $entry;
    }
    
    /**
     * Save an entry
     * 
     * @parem Entry $entry
     */
    public function save($entry)
    {
        if (\Craft\craft()->entries->saveEntry($entry))
            return true;
        
        $errors = $entry->getAllErrors();
        throw new \Exception(implode(' // ', $errors));
    }
    
        
    /**
     * Loop through an array of field => value
     * and process the field as required.
     * This will create Cateogries and Entries automatically if they don't exist.
     */
    public function parseFields($entry, $fields)
    {
        foreach ($fields AS $key=>$value) {
            if (empty($value) || !$field = $this->getFieldByName($key)) continue;
         
            $attributes = $field->getAttributes();
            $handle = $attributes['handle'];
            
            switch($attributes['type']) {
                case 'Categories':
                    $group = explode(':',$attributes['settings']['source']);
                    $groupId = $group[1];
                    $values = explode(';', $value);
                    $valueIds = [];
                    foreach ($values AS $val) {
                        $val = DbHelper::escapeParam(trim($val));
                        if (empty($val)) continue;
                        
                        $category = craft()->importer_category->findOrCreate($groupId, ucwords(trim($val)));
                        $valueIds[] = $category->id;
                    }
                    
                    $entry->getContent()->$handle = $valueIds;
                    break;
                    
                case 'Entries':
                    $source = $attributes['settings']['sources'][0];
                    $section = explode(':',$source);
                    $sectionId = $section[1];
                    $values = explode(';', $value);
                    $valueIds = [];
                    foreach ($values AS $val) {
                        $slug = StringHelper::toKebabCase($val);
                        $criteria = \Craft\craft()->elements->getCriteria(\Craft\ElementType::Entry);
                        $criteria->title = DbHelper::escapeParam($val);
                        $additionalEntry = $criteria->first();
                        if ($additionalEntry) {
                            $additionalEntry = craft()->importer_entry->createEntry($slug, $sectionId);
                            $additionalEntry->getContent()->title = DbHelper::escapeParam($val);
                            craft()->importer_entry->save($additionalEntry);
                        }
                        
                        $valueIds[] = $additionalEntry->id;
                    }
                    
                    $entry->getContent()->$handle = $valueIds;
                    break;
                    
                case 'Commerce_Products':
                    $values = explode(';', $value);
                    $relatedIds = [];
                    foreach ($values AS $val) {
                        $sku = trim($val);
                        $variant = craft()->importer_product->getVariantBySku($sku);
                        if ($variant)
                            $relatedIds[] = $variant->getProduct()->id;
                    }
                    
                    $entry->getContent()->$handle = $relatedIds;
                    break;
                    
                case 'Assets':
                    $filenames = explode(';', rtrim($value,';'));
                    $assetSourceId = (int)$attributes['settings']['defaultUploadLocationSource'];
                    $imageIds = craft()->importer_image->getImages($filenames, $assetSourceId);
                    
                    $entry->getContent()->$handle = $imageIds;
                    break;
                    
                default:
                    $entry->getContent()->$handle = $value;
            }
        }
        
        return $entry;
    }
    
    private function getFieldByName($name)
    {
        if ($this->fields && isset($this->fields[$name]))
            return $this->fields[$name];
        
        $fields = craft()->fields->getAllFields();
        $result = [];
        foreach ($fields AS $field)
            $result[$field->name] = $field;
        
        $this->fields = $result;
        
        return $this->fields[$name];
    }
}
