<?php
namespace Craft;

class Importer_ProductService extends BaseApplicationComponent
{
    /**
     * Get a product by its slug
     * 
     * @param string $slug
     * @param boolean $newModelIfNotExists
     * @return Commerce_ProductModel
     */
    public function getBySlug($slug, $clearVariants = true)
    {
        $criteria = \Craft\craft()->elements->getCriteria('Commerce_Product');
        $criteria->slug = $slug;
        
        $product = $criteria->first();
        
        if (!is_null($product) && $clearVariants)
            $product->setVariants([]);
        
        return $product;
    }
    
    /**
     * Create a new product
     * 
     * @param string $slug
     * @param int $typeId
     * @param boolean $setDefaultVariant
     * @return Commerce_ProductModel
     */
    public function createProduct($slug, $typeId, $setDefaultVariant = true)
    {
        $product = new \Craft\Commerce_ProductModel();
        $product->typeId = $typeId;
        $product->slug = $slug;
        $product->postDate = date('Y-m-d H:i:s', time());
        
        if ($setDefaultVariant) {
            $variant = $this->createVariant($product, $slug.'-expl', 'Example', 1);
            $product->setVariants([$variant]);
        }
        
        return $product;
    }
    
    /**
     * Create a new variant from the import row data
     * 
     * @param array $row Row from the CSV
     * @param ProductModel $product Product this variant is being added to
     * @return Commerce_VariantModel
     */
    public function createVariant(\Craft\Commerce_ProductModel $product, $sku, $name, $price)
    {
        $variant = new \Craft\Commerce_VariantModel();
        $variant->setProduct($product);
        $variant->sortOrder = 1;
        $variant->getContent()->title = $name;
        $variant->sku = $sku;
        $variant->price = $price;
        $variant->unlimitedStock = true;
        
        return $variant;
    }
        
    /**
     * Get a variant by the SKU
     * 
     * @param string $sku
     */
    public function getVariantBySku($sku)
    {
        $criteria = \Craft\craft()->elements->getCriteria('Commerce_Variant');
        $criteria->sku = $sku;
        return $criteria->first();
    }
    
    /**
     * Save a product
     * 
     * @parem ProductModel $product
     */
    public function save(\Craft\Commerce_ProductModel $product)
    {
        CommerceDbHelper::beginStackedTransaction();

        if (\Craft\craft()->commerce_products->saveProduct($product))
        {
            CommerceDbHelper::commitStackedTransaction();
            return true;
        }

        CommerceDbHelper::rollbackStackedTransaction();
        
        $errors = $product->getAllErrors();
        
        foreach ($product->getVariants() as $variant) {
            $variant_errors = $variant->getAllErrors();
            $errors = array_merge($errors, $variant_errors);
        }
        
        throw new \Exception(implode(' // ', $errors));
    }
}