<?php
namespace Craft;

class Importer_CategoryService extends BaseApplicationComponent
{
    /**
     * Get a Craft category
     * 
     * @param int $categoryId
     * @param string $title
     * @return CategoryModel
     */ 
    public function find($categoryId, $title)
    {
        $criteria = \Craft\craft()->elements->getCriteria(\Craft\ElementType::Category);
        $criteria->groupId = $categoryId;
        $criteria->title = $title;
        
        return $criteria->first();
    }
    
    /**
     * Creates a new Craft category
     * 
     * @param int $categoryId
     * @param string $title
     * @param CategoryModel $parent
     * @return CategoryModel
     */
    public function create($categoryId, $title, $parent = null)
    {
        $model = new \Craft\CategoryModel();
        $model->groupId = $categoryId;
        $model->getContent()->title = $title;
        
        if (!is_null($parent))
            $model->newParentId = $parent->id;
        
        \Craft\craft()->categories->saveCategory($model);
        
        return $model;
    }
    
    /**
     * Get a CategoryModel or create if it does not exist
     * 
     * @param int $categoryId
     * @param string $title
     * @param CategoryModel $parent
     * @return CategoryModel
     */
    public function findOrCreate($categoryId, $title, $parent = null)
    {
        $result = $this->find($categoryId, $title);
        
        if (is_null($result))
            $result = $this->create($categoryId, $title, $parent);
        
        return $result;
    }
}