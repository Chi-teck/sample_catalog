<?php

declare(strict_types=1);

namespace Drupal\sample_catalog;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\sample_catalog\Attribute\ProductPaneBuilder;

/**
 * Plugin manager for product pane builders.
 */
final class ProductPaneBuilderPluginManager extends DefaultPluginManager {

  /**
   * {@selfdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ProductPaneBuilder', $namespaces, $module_handler, ProductPaneBuilderInterface::class, ProductPaneBuilder::class);
    $this->alterInfo('product_pane_builder_info');
    $this->setCacheBackend($cache_backend, 'product_pane_builder_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []): ProductPaneBuilderInterface {
    if ($configuration === []) {
      // Use Drupal settings as there is no UI for plugin configuration.
      $settings = Settings::get('sample_catalog');
      $current_base_uri = (new Url('<front>', options: ['absolute' => TRUE]))->toString();
      $configuration = match ($plugin_id) {
        'fpm' => ['server_address' => $settings['fpm']['server_address'] ?? ''],
        'http' => ['base_uri' => $settings['http']['base_uri'] ?? $current_base_uri],
        default => [],
      };
    }
    return parent::createInstance($plugin_id, $configuration);
  }

}
