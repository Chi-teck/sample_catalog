<?php

declare(strict_types=1);

/**
 * @file
 * A sample worker for Road Runner.
 *
 * @see \Drupal\sample_catalog\Plugin\ProductPaneBuilder\Http
 */

use Drupal\Core\DrupalKernel;
use Drupal\sample_catalog\Entity\Product;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;
use Symfony\Component\HttpFoundation\Request;

// -- Bootstrap Drupal
$drupal_root = $argv[1] ?? throw new \InvalidArgumentException('Missing Drupal root');
\chdir($drupal_root);
$autoloader = require_once 'autoload.php';
$kernel = DrupalKernel::createFromRequest(Request::createFromGlobals(), $autoloader, 'prod');
$kernel->boot();
$request = Request::createFromGlobals();
$kernel->preHandle($request);

$worker = Worker::create();
$factory = new Psr17Factory();
$psr7 = new PSR7Worker($worker, $factory, $factory, $factory);
while (TRUE) {
  try {
    $request = $psr7->waitRequest();
    if ($request === NULL) {
      break;
    }
  }
  catch (\Throwable $exception) {
    $psr7->respond(new Response(400, body: $exception->getMessage()));
    continue;
  }

  try {
    $path = $request->getUri()->getPath();
    if (!\preg_match('#^/product/(\d+)/pane$#', $path, $matches)) {
      $headers['Content-Type'] = 'application/x-php-serialized; charset=UTF-8';
      $psr7->respond(new Response(404, body: 'Resource not found'));
    }

    $product_id = $matches[1];
    $product = Product::load($product_id);
    $builder = \Drupal::service('plugin.manager.product_pane_builder')->createInstance('simple');
    $build = $builder->build($product);
    $psr7->respond(new Response(200, body: \serialize($build)));
  }
  catch (\Throwable $exception) {
    echo $exception->getMessage();
    $psr7->respond(new Response(500));
    $psr7->getWorker()->error((string) $exception);
  }
}
