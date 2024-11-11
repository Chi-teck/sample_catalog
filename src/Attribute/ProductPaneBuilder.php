<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Attribute;

use Drupal\Component\Plugin\Attribute\AttributeBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * {@selfdoc}
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class ProductPaneBuilder extends AttributeBase {

  /**
   * {@selfdoc}
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
    public readonly ?string $deriver = NULL,
  ) {}

}
