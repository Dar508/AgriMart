-- Run once against the existing shop_db database to enable farmer seasonal listings.
ALTER TABLE `products`
  ADD COLUMN `is_seasonal` TINYINT(1) NOT NULL DEFAULT 0 AFTER `min_supplier_qty`;
