# Topocentras Product Import Module

This module imports products from Facebook product feed CSV files into Magento.

## Features

- Imports products from CSV feed
- Creates/updates products based on SKU
- Automatically creates categories from `custom_label_0` field
- Handles pricing (regular and sale prices)
- Sets stock status based on availability
- Processes product attributes (brand, description, etc.)
- Batch processing for large files
- Detailed import statistics

## CSV Format

The module expects a CSV file with the following columns:
- `id` - Product ID (required)
- `title` - Product name (required)
- `description` - Product description
- `link` - Product URL
- `image_link` - Product image URL
- `condition` - Product condition
- `availability` - Stock status ("in stock" or other)
- `sale_price` - Sale price (optional)
- `price` - Regular price
- `brand` - Brand/manufacturer
- `member_price` - Member price (optional)
- `custom_label_0` - Category name
- `custom_label_1` - Additional category/label

## Installation

1. Enable the module:
```bash
php bin/magento module:enable Topocentras_ProductImport
php bin/magento setup:upgrade
php bin/magento cache:flush
```

2. Compile if in production mode:
```bash
php bin/magento setup:di:compile
```

## Usage

### Import Products from CSV

```bash
php bin/magento topocentras:product:import /path/to/your/feed.csv
```

### Import with Custom Batch Size

```bash
php bin/magento topocentras:product:import /path/to/your/feed.csv --batch-size=50
```

### Example with the provided CSV

```bash
php bin/magento topocentras:product:import /Users/wali/Sites/magento/src/saltibarsciu-festivalis-fb.csv
```

## How It Works

1. **SKU Generation**: Products are assigned SKUs in format `FB-{id}` where `{id}` is from the CSV
2. **Product Creation/Update**: Checks if product exists by SKU, creates new or updates existing
3. **Category Assignment**: Creates categories from `custom_label_0` field if they don't exist
4. **Price Handling**: Parses EUR prices, sets regular price and special price (sale price)
5. **Stock Management**: Sets products as in stock (qty: 100) or out of stock based on availability
6. **URL Keys**: Generates SEO-friendly URL keys from product titles

## Product Mapping

| CSV Field | Magento Field |
|-----------|---------------|
| id | SKU (as FB-{id}) |
| title | Name |
| description | Description & Short Description |
| price | Price |
| sale_price | Special Price |
| brand | Manufacturer attribute |
| availability | Stock Status |
| custom_label_0 | Category |

## Notes

- Products are created as simple products
- Default attribute set (ID: 4) is used
- Products are enabled and visible in catalog and search
- Categories are created under the root category
- Large CSV files (like the 51MB feed) are processed in batches to avoid memory issues
- Errors are logged to Magento's system log

## Troubleshooting

If you encounter issues:

1. Check Magento logs:
   - `var/log/system.log`
   - `var/log/exception.log`

2. Ensure proper permissions on the CSV file

3. Verify the module is enabled:
   ```bash
   php bin/magento module:status Topocentras_ProductImport
   ```

4. Clear cache after installation:
   ```bash
   php bin/magento cache:flush
   ```
