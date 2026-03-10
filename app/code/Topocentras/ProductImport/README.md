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

### Two-Step Import Process (Recommended for Large Feeds)

For large CSV files with many products, it's recommended to split the import into two steps:

**Step 1: Import Products Only (Fast)**
```bash
php bin/magento topocentras:product:import saltibarsciu-festivalis-fb.csv --skip-images --batch-size=200
```

**Step 2: Import Images Separately (Slower)**
```bash
php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --batch-size=50
```

### Single-Step Import (Products + Images)

For smaller feeds or if you prefer a single command:

```bash
php bin/magento topocentras:product:import /path/to/your/feed.csv --batch-size=50
```

### Command Options

**Product Import (`topocentras:product:import`)**
- `--batch-size` or `-b`: Number of products per batch (default: 100)
- `--skip-images` or `-s`: Skip image downloads, products only

**Image Import (`topocentras:product:import-images`)**
- `--batch-size` or `-b`: Number of images per batch (default: 50)
- `--force` or `-f`: Re-download images even if product already has images
- `--offset` or `-o`: Skip first N products (resume from specific position)
- `--limit` or `-l`: Process only N products (0 = all)

### Examples

```bash
# Fast product import without images
php bin/magento topocentras:product:import saltibarsciu-festivalis-fb.csv -s -b 200

# Import images for products that don't have images yet
php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv -b 50

# Force re-download all images
php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv -f -b 50

# Resume from product 5,000 (if import failed)
php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --offset=5000

# Process only first 1,000 products (testing)
php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --limit=1000

# Process products 10,000 to 15,000 (chunk processing)
php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --offset=10000 --limit=5000

# Process in chunks of 10,000 products
php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --limit=10000
php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --offset=10000 --limit=10000
php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --offset=20000 --limit=10000
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

## Best Practices for Large Imports

### For ~105,000 Products

**Recommended Workflow:**

1. **Import products first (fast - ~10-15 minutes)**
   ```bash
   php bin/magento topocentras:product:import saltibarsciu-festivalis-fb.csv --skip-images --batch-size=200
   ```

2. **Import images in chunks (can run in background)**
   ```bash
   # Process 10,000 at a time
   php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --limit=10000 --batch-size=50
   php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --offset=10000 --limit=10000 --batch-size=50
   # Continue for remaining products...
   ```

3. **If import fails, resume from where it stopped**
   ```bash
   # If it failed at product 25,000
   php bin/magento topocentras:product:import-images saltibarsciu-festivalis-fb.csv --offset=25000
   ```

### Performance Tips

- Use `--skip-images` for initial product import (10-20x faster)
- Process images separately during off-peak hours
- Use smaller batch sizes (25-50) for image imports to avoid timeouts
- Use larger batch sizes (200-500) for product-only imports
- Process images in chunks using `--offset` and `--limit` to manage server load
- Run multiple image import processes in parallel for different ranges

## Notes

- Products are created as simple products
- Default attribute set (ID: 4) is used
- Products are enabled and visible in catalog and search
- Categories are created under the root category
- Large CSV files (like the 51MB feed) are processed in batches to avoid memory issues
- Errors are logged to Magento's system log
- Image imports skip products that already have images (unless `--force` is used)
- Use `--offset` to resume interrupted imports without re-processing completed products

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
