# Craft Data Importer Plugin

This is a small plugin to help importing data to *Craft 2*.

This plugin supports importing Entries, creating categories on the fly, and Commerce Products.

As every site will be different use this is as a starting point.
You may want to edit `consolecommands\ImporterCommand.php` and the `processRow` function for custom imports.

## Using

A basic example of usage is available at `consolecommands\ImporterCommand.php`.

Create a new database table called `craft_importer` with each custom field as a seperate column,
the column names should match the Field name text value, e.g. `Product Name`.

Also make sure your table has the following 2 required columns (case is important):

    - `processed` - Integer - default = 0
    - `dateUpdated` - DateTime

Run in the terminal with:

    php ./craft/app/etc/console/yiic importer run
    
For this to import entries out-of-the-box, your database table must have the following columns:

- Slug
- Name

You can also add additional columns that match the title of your custom fields.

## Data Format

The value in the column can have multiple values by seperating with a semi-colon.
E.g: If you have a field that selects a Category option then you would enter:

    Category Name 1;Category Name 2;Category Name 3
    
The categories will be created automatically if they do not exist.

*Note*: The data should use the title of categories, etc. and not the slugs. This is because
if importing a new category - that slug obviously won't yet exist.

    
## Parsing Fields

The importer will automatically detect the field type and process as required.
Currently the script supports the following field types:

- Categories = Creates new categories if they do not exist.
- Entries = Will create a new entry setting the title value only.
- Commerce_Products = Assigns already existing products to the entry - data should be semi-colon list of SKUs
- Assets = Adds already uploaded images to the entry

## Importing Assets

To import images make sure you have an Assets field with a default Asset Source set.

Upload your images to your asset source folder and then go to the Craft admin and click Update Indexes.

Add a column to your data import that matches the title of your field and seperate image filenames with a semi-colon.

## Importing Commerce Products

This can also import Commerce Products.

Adapt the Importer_ImportService processRow function to the following:

    $product = craft()->importer_product->getBySlug($row['Slug']);
    if (is_null($product))
        $product = craft()->importer_product->createProduct($row['Slug'], self::SECTION);
        
    $product->getContent()->title = $row['Name'];
    
    // Remove columns not required
    unset($row['Slug'], $row['Name']);
    
    // Loop through remaining columns
    craft()->importer_entry->parseFields($product, $row);
    
    craft()->importer_product->save($product);
    
You may also want to create variants depending on the data being imported:

    $variant = craft()->importer_product->getVariantBySku('my-sku');
    if (is_null($variant))
        $variant = craft()->importer_product->createVariant($product, 'my-sku', 'my-variant', 10);