<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Plugin\ProductPaneBuilder;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Render\FormattableMarkup as FM;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup as TM;
use Drupal\sample_catalog\Entity\Product;
use Drupal\sample_catalog\ProductPaneBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for pane builders.
 */
abstract class AbstractPaneBuilder extends PluginBase implements ProductPaneBuilderInterface, ContainerFactoryPluginInterface {

  /**
   * {@selfdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly ConfigFactoryInterface $configFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      configuration: $configuration,
      plugin_id: $plugin_id,
      plugin_definition: $plugin_definition,
      entityTypeManager: $container->get('entity_type.manager'),
      configFactory: $container->get('config.factory'),
    );
  }

  /**
   * Builds content for a given product.
   */
  final public function build(Product $product): array {
    $started = new \DateTimeImmutable();
    $build_cost = $this->configFactory->get('sample_catalog.settings')->get('build_cost');
    $this->wasteSystemResources($build_cost);
    $build = $this->entityTypeManager
      ->getViewBuilder(Product::ID)
      ->view($product);
    $finished = new \DateTimeImmutable();

    $build['started']['#markup'] = new FM(
      'Started at: <time datetime="@datetime">@time</time>',
      ['@time' => $started->format('H:i:s.v'), '@datetime' => $started->format('Y-m-d\TH:i:s\Z')],
    );
    $build['finished']['#markup'] = new FM(
      'Finished at: <time datetime="@datetime">@time</time>',
      ['@time' => $finished->format('H:i:s.v'), '@datetime' => $finished->format('Y-m-d\TH:i:s\Z')],
    );

    $time = $finished->format('U.u') - $started->format('U.u');
    $build['time']['#markup'] = new FM(
      'Build time: <time datetime="@datetime">@time ms</time>',
      [
        '@time' => \number_format(1_000 * $time, 3, thousands_separator: ' '),
        '@datetime' => \round((float) $time, 6) . 'S'
      ],
    );

    return $build;
  }

  /**
   * {@selfdoc}
   */
  private static function wasteSystemResources(int $build_cost): void {
    $cpu_waster = static fn (int $input): string => \sin($input) . \cos($input) . \sqrt($input);
    $file = \tmpfile();
    for ($i = 1; $i <= $build_cost; $i++) {
      $data = \json_encode(\array_map($cpu_waster, \range(1, 5_000)));
      \fwrite($file, \md5($data));
      // This should stress IO as well.
      \fsync($file);
    }
    \fclose($file);
  }

  /**
   * {@selfdoc}
   */
  final protected function buildError(\Stringable|string $message = new TM('Content not available')): array {
    return [
      '#type' => 'container',
      '#markup' => $message,
      '#attributes' => ['class' => ['sc-catalog__error']],
    ];
  }

}
