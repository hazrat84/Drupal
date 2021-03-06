<?php

namespace Drupal\stripe_payment\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\stripe_payment\StripeApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StripeApiAdminForm.
 *
 * Contains admin form functionality for the Stripe API.
 */
class StripeApiAdminForm extends ConfigFormBase {

  /**
   * @var \Drupal\stripe_payment\StripeApiService*/
  protected $stripeApi;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\stripe_api\StripeApiService $stripe_api
   */
  public function __construct(ConfigFactoryInterface $config_factory, StripeApiService $stripe_api) {
    $this->stripeApi = $stripe_api;

    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('stripe_payment.stripe_payment')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'stripe_api_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'stripe_api.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('stripe_api.settings');

    // @see https://www.drupal.org/docs/7/api/localization-api/dynamic-or-static-links-and-html-in-translatable-strings
    $form['link'] = [
      '#markup' => $this->t('Stripe links: <a href="@stripe-dashboard" target="_blank">Dashboard</a> | <a href="@stripe-keys" target="_blank">API Keys</a> | <a href="@stripe-docs" target="_blank">Docs</a><br /><br />', [
        '@stripe-dashboard' => Url::fromUri('https://dashboard.stripe.com', ['attributes' => ['target' => '_blank']])->toString(),
        '@stripe-keys' => Url::fromUri('https://dashboard.stripe.com/account/apikeys', ['attributes' => ['target' => '_blank']])->toString(),
        '@stripe-docs' => Url::fromUri('https://stripe.com/docs/api', ['attributes' => ['target' => '_blank']])->toString(),
      ]),
    ];
    $form['test_secret_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('Stripe Secret Key (test)'),
      '#default_value' => $config->get('test_secret_key'),
    ];
    $form['test_public_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('Stripe Public Key (test)'),
      '#default_value' => $config->get('test_public_key'),
    ];
    $form['live_secret_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('Stripe Secret Key (live)'),
      '#default_value' => $config->get('live_secret_key'),
    ];
    $form['live_public_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('Stripe Public Key (live)'),
      '#default_value' => $config->get('live_public_key'),
    ];
    $form['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Mode'),
      '#options' => [
        'test' => $this->t('Test'),
        'live' => $this->t('Live'),
      ],
      '#default_value' => $config->get('mode'),
    ];

    $form['log_webhooks'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Log incoming webhooks'),
      '#default_value' => $config->get('log_webhooks'),
    ];

    if ($this->stripeApi->getApiKey()) {
      $form['stripe_test'] = [
        '#type' => 'button',
        '#value' => $this->t('Test Stripe Connection'),
        '#ajax' => [
          'callback' => [$this, 'testStripeConnection'],
          'wrapper' => 'stripe-connect-results',
          'method' => 'append',
        ],
        '#suffix' => '<div id="stripe-connect-results"></div>',
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * AJAX callback to test the Stripe connection.
   */
  public function testStripeConnection(array &$form, FormStateInterface $form_state) {
    try {
      $account = $this->stripeApi->call('account', 'retrieve');
    }
    catch (\Exception $e) {
      $account = NULL;
    }
    if ($account && $account->email) {
      return ['#markup' => $this->t('Success! Account email: %email', ['%email' => $account->email])];
    }
    else {
      return ['#markup' => $this->t('Error! Could not connect! See error log.')];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('stripe_api.settings')
      ->set('mode', $form_state->getValue('mode'))
      ->set('log_webhooks', $form_state->getValue('log_webhooks'))
      ->set('test_secret_key', $form_state->getValue('test_secret_key'))
      ->set('test_public_key', $form_state->getValue('test_public_key'))
      ->set('live_secret_key', $form_state->getValue('live_secret_key'))
      ->set('live_public_key', $form_state->getValue('live_public_key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
