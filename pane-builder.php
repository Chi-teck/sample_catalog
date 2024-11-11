<?php

declare(strict_types=1);

/**
 * @file
 * A renderer for a single product pane.
 *
 * @see \Drupal\sample_catalog\Plugin\ProductPaneBuilder\Process
 * @see \Drupal\sample_catalog\Plugin\ProductPaneBuilder\Fpm
 */

namespace Drupal\sample_catalog;

use Drupal\Core\DrupalKernel;
use Drupal\sample_catalog\Entity\Product;
use Symfony\Component\HttpFoundation\Request;

if (\PHP_SAPI === 'cli') {
  $drupal_root = $argv[1] ?? throw new \InvalidArgumentException('Missing Drupal root');
  $product_id = $argv[2] ?? throw new \InvalidArgumentException('Missing product ID');
}
elseif (\PHP_SAPI === 'fpm-fcgi') {
  // @phpcs:ignore DrupalPractice.Variables.GetRequestData.SuperglobalAccessedWithVar
  $drupal_root = $_POST['drupal_root'] ?? throw new \InvalidArgumentException('Missing Drupal root');
  // @phpcs:ignore DrupalPractice.Variables.GetRequestData.SuperglobalAccessedWithVar
  $product_id = $_POST['product_id'] ?? throw new \InvalidArgumentException('Missing product ID');
}
else {
  throw new \InvalidArgumentException('Unsupported SAPI');
}

// -- Bootstrap Drupal
\chdir($drupal_root);
$autoloader = require_once 'autoload.php';
$kernel = DrupalKernel::createFromRequest(Request::createFromGlobals(), $autoloader, 'prod');
$kernel->boot();
$request = Request::createFromGlobals();
$kernel->preHandle($request);

$product = Product::load($product_id);

$build = \Drupal::service('plugin.manager.product_pane_builder')
  ->createInstance('simple')
  ->build($product);

echo \serialize($build);
