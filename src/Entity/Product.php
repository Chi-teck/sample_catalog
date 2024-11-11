<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the product entity class.
 *
 * @ContentEntityType(
 *   id = "sample_catalog_product",
 *   label = @Translation("Product"),
 *   label_collection = @Translation("Products"),
 *   label_singular = @Translation("product"),
 *   label_plural = @Translation("products"),
 *   label_count = @PluralTranslation(
 *     singular = "@count products",
 *     plural = "@count products",
 *   ),
 *   handlers = {},
 *   base_table = "sample_catalog_product",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {},
 * )
 */
final class Product extends ContentEntityBase {

  public const string ID = 'sample_catalog_product';

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 32)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => -100,
      ]);

    $fields['description'] = BaseFieldDefinition::create('text')
      ->setLabel(t('Description'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 96)
      ->setDisplayOptions('view', ['label' => 'hidden']);

    return $fields;
  }

}
