<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Plugin\ProductPaneBuilder;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\sample_catalog\Attribute\ProductPaneBuilder;
use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Exceptions\ConnectException;
use hollodotme\FastCGI\Requests\PostRequest;
use hollodotme\FastCGI\SocketConnections\NetworkSocket;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;

/**
 * Plugin implementation of the product pane builder'.
 */
#[ProductPaneBuilder(
  id: self::ID,
  label: new TranslatableMarkup('FPM'),
)]
final class Fpm extends AbstractPaneBuilder {

  public const string ID = 'fpm';
  private const int CONNECT_TIMEOUT = 5_000;
  private const int READ_WRITE_TIMEOUT = 5_000;

  /**
   * {@inheritdoc}
   */
  public function buildMultiple(array $products): array {

    if (empty($this->configuration['server_address'])) {
      throw new \InvalidArgumentException('FPM Builder requires server address to be configured');
    }

    $parsed_url = \parse_url($this->configuration['server_address']);

    if ($parsed_url && $parsed_url['scheme'] === 'tcp') {
      $connection = new NetworkSocket(
        host: $parsed_url['host'],
        port: $parsed_url['port'],
        connectTimeout: self::CONNECT_TIMEOUT,
        readWriteTimeout: self::READ_WRITE_TIMEOUT,
      );
    }
    else {
      [$scheme, $path] = \explode('://', $this->configuration['server_address']);
      if ($scheme !== 'unix') {
        throw new \InvalidArgumentException('Wrong server address');
      }
      $connection = new UnixDomainSocket(
        socketPath: $path,
        connectTimeout: self::CONNECT_TIMEOUT,
        readWriteTimeout: self::READ_WRITE_TIMEOUT,
      );
    }

    $client = new Client();

    $socket_ids = [];
    foreach ($products as $product) {
      $data = [
        'drupal_root' => \DRUPAL_ROOT,
        'product_id' => $product->id(),
      ];
      $content = \http_build_query($data);
      // @see sample_catalog/pane-builder.php
      $file = \realpath(__DIR__ . '/../../../pane-builder.php');
      $request = new PostRequest($file, $content);
      try {
        $socket_ids[] = $client->sendAsyncRequest($connection, $request);
      }
      catch (ConnectException $exception) {
        return self::buildError('FPM error: ' . $exception->getMessage());
      }
    }

    $build = [];
    foreach ($client->readResponses(100_000, ...$socket_ids) as $response) {
      $content = @\unserialize($response->getBody());
      if ($content === FALSE) {
        return self::buildError();
      }
      $build[] = $content;
    }
    if (\count($build) !== \count($products)) {
      return self::buildError('FPM error: Content is not delivered in time');
    }
    return $build;
  }

}
