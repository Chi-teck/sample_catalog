<?php

declare(strict_types=1);

namespace Drupal\sample_catalog\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup as TM;

/**
 * Configure Sample Catalog settings.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'sample_catalog_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['sample_catalog.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['pane_count'] = [
      '#type' => 'number',
      '#title' => new TM('Number of panes to display'),
      '#default_value' => $this->config('sample_catalog.settings')->get('pane_count'),
      '#min' => 0,
      '#max' => 100,
    ];

    $form['build_cost'] = [
      '#type' => 'number',
      '#title' => new TM('Build cost'),
      '#default_value' => $this->config('sample_catalog.settings')->get('build_cost'),
      '#description' => new TM('Abstract value that defines how much resources should be taken to render a single product pane.'),
      '#min' => 0,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('sample_catalog.settings')
      ->set('pane_count', $form_state->getValue('pane_count'))
      ->set('build_cost', $form_state->getValue('build_cost'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
