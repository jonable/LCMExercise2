<?php
/**
 * @file
 * Contains \Drupal\exercise2\Form\PaywallSettingsForm
 */

namespace Drupal\exercise2\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form to configure Exercise2's Paywall settings
 */
class PaywallSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'exercise2_admin_settings';
  }
   /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['exercise2.settings'];
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {      
    $config = $this->config('exercise2.settings');
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Settings for the Exercise2 Module'),
    ];
    $form['paywall_notice'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Paywall notice'),
      '#default_value' => $config->get('paywall_notice'),
      '#description' => $this->t('Paywall notice displayed to user when viewing premium content.'),
    ];
    $form['paywall_link'] = [
      '#type' => 'url',
      '#title' => $this->t('Paywall link'),
      '#default_value' => $config->get('paywall_link'),
      '#description' => $this->t('Enter a url to redirect paywalled users to register for premium. Leave empty to hide.'),
    ];
    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $paywall_link = $form_state->getValue('paywall_link');
    $this->config('exercise2.settings')
      ->set('paywall_link', $paywall_link)
      ->save();
    $paywall_notice = $form_state->getValue('paywall_notice');
    $this->config('exercise2.settings')
      ->set('paywall_notice', $paywall_notice)
      ->save();      
    parent::submitForm($form, $form_state);
  }

}

