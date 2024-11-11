<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Plugin\ProductPaneBuilder;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\sample_catalog\Attribute\ProductPaneBuilder;
use Symfony\Component\Process\Process as SystemProcess;

/**
 * Plugin implementation of the product pane builder.
 */
#[ProductPaneBuilder(
  id: self::ID,
  label: new TranslatableMarkup('Process'),
)]
final class Process extends AbstractPaneBuilder {

  public const string ID = 'process';

  /**
   * {@inheritdoc}
   */
  public function buildMultiple(array $products): array {
    $processes = [];
    foreach ($products as $product) {
      // @see sample_catalog/pane-builder.php
      $file = \realpath(__DIR__ . '/../../../pane-builder.php');
      $process = new SystemProcess(['php', $file, \DRUPAL_ROOT, $product->id()]);
      $process->start();
      $processes[] = $process;
    }

    is_running:
    \usleep(1_000);
    foreach ($processes as $process) {
      if ($process->isRunning()) {
        goto is_running;
      }
    }

    $build = [];
    foreach ($processes as $process) {
      if (!$process->isSuccessful()) {
        return self::buildError();
      }
      $content = @\unserialize($process->getOutput());
      if ($content === FALSE) {
        return self::buildError();
      }
      $build[] = $content;
    }
    return $build;
  }

}
