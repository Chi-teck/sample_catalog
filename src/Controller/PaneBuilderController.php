<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\sample_catalog\Entity\Product;
use Drupal\sample_catalog\Plugin\ProductPaneBuilder\Simple;
use Drupal\sample_catalog\ProductPaneBuilderPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * {@selfdoc}
 */
final readonly class PaneBuilderController implements ContainerInjectionInterface {

  /**
   * {@selfdoc}
   */
  public function __construct(
    private ProductPaneBuilderPluginManager $builderManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('plugin.manager.product_pane_builder'),
    );
  }

  /**
   * {@selfdoc}
   */
  public function __invoke(Product $sample_catalog_product): Response {
    $build = $this->builderManager
      ->createInstance(Simple::ID)
      ->build($sample_catalog_product);
    $headers['Content-Type'] = 'application/x-php-serialized; charset=UTF-8';
    return new Response(\serialize($build), headers: $headers);
  }

}
