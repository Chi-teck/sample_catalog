<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Plugin\Menu\LocalTask\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\sample_catalog\Plugin\ProductPaneBuilder\Fpm;
use Drupal\sample_catalog\Plugin\ProductPaneBuilder\Http;
use Drupal\sample_catalog\Plugin\ProductPaneBuilder\Process;
use Drupal\sample_catalog\Plugin\ProductPaneBuilder\Simple;
use Drupal\sample_catalog\ProductPaneBuilderPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@selfdoc}
 */
final class ProductPaneBuilderLocalTask extends DeriverBase implements ContainerDeriverInterface {

  /**
   * {@selfdoc}
   */
  private function __construct(private readonly ProductPaneBuilderPluginManager $pluginManager) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id): self {
    return new self(
      $container->get('plugin.manager.product_pane_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    foreach ($this->pluginManager->getDefinitions() as $build_definition) {
      $this->derivatives['sample_catalog.' . $build_definition['id']] = [
        'title' => $build_definition['label'],
        'route_name' => 'sample_catalog',
        'route_parameters' => ['builder_type' => $build_definition['id']],
        'base_route' => 'sample_catalog',
        // @todo Add weight to plugin definition.
        'weight' => match ($build_definition['id']) {
          Simple::ID => 10,
          Fpm::ID => 20,
          Process::ID => 30,
          Http::ID => 40,
          default => 50,
        }
      ];
    }
    return $this->derivatives;
  }

}
