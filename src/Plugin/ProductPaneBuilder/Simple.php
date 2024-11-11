<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Plugin\ProductPaneBuilder;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\sample_catalog\Attribute\ProductPaneBuilder;

/**
 * Plugin implementation of the product pane builder.
 */
#[ProductPaneBuilder(
  id: self::ID,
  label: new TranslatableMarkup('Simple'),
)]
final class Simple extends AbstractPaneBuilder {

  public const string ID = 'simple';

  /**
   * {@inheritdoc}
   */
  public function buildMultiple(array $products): array {
    return \array_map($this->build(...), $products);
  }

}
