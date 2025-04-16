# ClothLoop API Documentation

## Image Handling

ClothLoop stores all images in the database (not in the filesystem). To display images in your application, use the image_display.php endpoint.

### Accessing Images

Use the following URL patterns to access various types of images:

1. **Shop Logo**:
   ```
   /backend/api/image_display.php?type=shop_logo&id=SELLER_ID
   ```
   Replace `SELLER_ID` with the ID of the seller whose logo you want to display.

2. **Product Images**:
   ```
   /backend/api/image_display.php?type=product&id=PRODUCT_ID
   ```
   This will return the first product image.

   To get a specific product image:
   ```
   /backend/api/image_display.php?type=product&id=PRODUCT_ID&image_id=IMAGE_ID
   ```
   Replace `PRODUCT_ID` with the product ID and `IMAGE_ID` with the specific image ID you want to retrieve.

3. **Cloth Images**:
   ```
   /backend/api/image_display.php?type=cloth&id=CLOTH_ID
   ```
   Replace `CLOTH_ID` with the ID of the cloth item whose image you want to display.

### Usage in HTML

Example usage in HTML:

```html
<!-- Display seller's logo -->
<img src="/backend/api/image_display.php?type=shop_logo&id=1" alt="Shop Logo">

<!-- Display a product image -->
<img src="/backend/api/image_display.php?type=product&id=5" alt="Product Image">

<!-- Display a specific product image -->
<img src="/backend/api/image_display.php?type=product&id=5&image_id=2" alt="Product Image 2">
```

### Error Handling

If an image is not found, a default placeholder image will be displayed. 