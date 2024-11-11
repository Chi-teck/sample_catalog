<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Plugin\ProductPaneBuilder;

use Drupal\Component\Utility\Xss;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\sample_catalog\Attribute\ProductPaneBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Plugin implementation of the product pane builder.
 */
#[ProductPaneBuilder(
  id: self::ID,
  label: new TranslatableMarkup('HTTP'),
)]
final class Http extends AbstractPaneBuilder {

  public const string ID = 'http';

  /**
   * {@inheritdoc}
   */
  public function buildMultiple(array $products): array {
    if (empty($this->configuration['base_uri'])) {
      throw new \InvalidArgumentException('HTTP builder requires base URI to be configured');
    }
    $client = \Drupal::httpClient();

    // Ensure ending slash.
    $options = ['base_uri' => \rtrim($this->configuration['base_uri'], '/') . '/'];
    $promises = [];
    foreach ($products as $product) {
      $promises[] = $client->getAsync('product/' . $product->id() . '/pane', $options);
    }

    $decode = static fn (ResponseInterface $response): array =>
      unserialize((string) $response->getBody());
    try {
      return \array_map($decode, Utils::unwrap($promises));
    }
    catch (ClientExceptionInterface $exception) {
      return self::buildError('Network error: ' . Xss::filter($exception->getMessage()));
    }
  }

  /**
   * {@selfdoc}
   */
  private static function getHttpClient(): Client {
    return \Drupal::httpClient();
  }

}
