<?php

namespace Drupal\datastore\SqlEndpoint\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DkanSqlEndpointSettingsForm.
 *
 * @package Drupal\sql_endpoint\Form
 * @codeCoverageIgnore
 */
class DkanSqlEndpointSettingsForm extends ConfigFormBase {

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sql_endpoint_settings_form';
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'sql_endpoint.settings',
    ];
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('sql_endpoint.settings');
    $form['rows_limit'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 9999,
      '#title' => $this->t('Rows limit'),
      '#default_value' => $config->get('rows_limit'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('sql_endpoint.settings')
      ->set('rows_limit', $form_state->getValue('rows_limit'))
      ->save();
  }

}
