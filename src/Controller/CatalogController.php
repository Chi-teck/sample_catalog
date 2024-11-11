<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Controller;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Render\FormattableMarkup as FM;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\StringTranslation\TranslatableMarkup as TM;
use Drupal\sample_catalog\ProductPaneBuilderPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Builds product catalog.
 */
final readonly class CatalogController implements ContainerInjectionInterface {

  /**
   * {@selfdoc}
   */
  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private ProductPaneBuilderPluginManager $pluginManager,
    private ConfigFactoryInterface $configFactory,
    private KillSwitch $killSwitch,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.product_pane_builder'),
      $container->get('config.factory'),
      $container->get('page_cache_kill_switch'),
    );
  }

  /**
   * {@selfdoc}
   */
  public function __invoke(string $builder_type): array {
    try {
      $pane_builder = $this->pluginManager->createInstance($builder_type);
    }
    catch (PluginNotFoundException) {
      throw new NotFoundHttpException();
    }

    $product_storage = $this->entityTypeManager->getStorage('sample_catalog_product');
    $pane_count = $this->configFactory->get('sample_catalog.settings')->get('pane_count');
    $product_ids = $product_storage->getQuery()
      ->accessCheck()
      ->range(1, $pane_count)
      ->execute();
    $products = $product_storage->loadMultiple($product_ids);

    $start = \microtime(TRUE);
    $content = $pane_builder->buildMultiple($products);
    $finish = \microtime(TRUE);

    $build['catalog'] = [
      '#attached' => ['library' => ['sample_catalog/catalog']],
      '#attributes' => ['class' => ['sc-catalog']],
      '#type' => 'container',
      // Ensure that the items are never cached.
      '#cache' => ['max-age' => 0],
      'content' => $content,
    ];

    $time = $finish - $start;
    $summary_content = new FM(
      'Build time: <time datetime="@datetime">@time ms</time>',
      ['@time' => \number_format(1_000 * $time, 3, thousands_separator: ' '), '@datetime' => \round($time, 6) . 'S'],
    );
    $build['summary'] = [
      '#type' => 'container',
      'content' => ['#markup' => $summary_content],
      '#weight' => -10,
      '#attributes' => ['class' => ['sc-summary']],
    ];

    // Mark this page as being uncacheable.
    $this->killSwitch->trigger();

    return $build;
  }

  /**
   * Title callback.
   */
  public function title(string $builder_type): string {
    return (string) new TM('Catalog (@type)', ['@type' => $builder_type]);
  }

}
