<?php

declare(strict_types=1);

namespace Drupal\sample_catalog;

use Drupal\sample_catalog\Entity\Product;

/**
 * Interface for 'product_pane_builder' plugins.
 */
interface ProductPaneBuilderInterface {

  /**
   * Builds content for a given product.
   */
  public function build(Product $product): array;

  /**
   * Builds the content for the provided products.
   *
   * @param \Drupal\sample_catalog\Entity\Product[] $products
   */
  public function buildMultiple(array $products): array;

}
