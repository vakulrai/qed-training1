<?php

namespace Drupal\ai_agent_for_content\Plugin\Action;


use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Parses a JSON string and stores the result in a token.
 *
 * @Action(
 *   id = "eca_json_parser_json_parse",
 *   label = @Translation("Parse JSON String"),
 *   description = @Translation("Parses a JSON string and stores the result in a token.")
 * )
 */
class EcaJsonParser extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $token_value = trim($this->tokenService->getTokenData($this->configuration['json_string']));
    // Get the JSON string from the configured token.
    $json_string = $this->tokenService->replaceClear($token_value);

    // Decode the JSON string.
    $decoded_data = json_decode($json_string, TRUE);
    if (json_last_error() === JSON_ERROR_NONE) {
      // Store the decoded data in a token for use in the workflow.
      $token_name = $this->configuration['token_name'];
      $this->tokenService->addTokenData($token_name, $decoded_data);
    }
    else {
      // Log an error if the JSON string is invalid.
      \Drupal::logger('eca_json_parser')->error('Invalid JSON string provided: @json', ['@json' => $json_string]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getOperationType(): string {
    return 'json_date';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'json_string' => '',
      'token_name' => 'parsed_json',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['json_string'] = [
      '#type' => 'textfield',
      '#title' => $this->t('JSON String'),
      '#description' => $this->t('The JSON string to parse. You can use tokens here.'),
      '#default_value' => $this->configuration['json_string'],
      '#required' => TRUE,
    ];

    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token Name'),
      '#description' => $this->t('The name of the token where the parsed JSON data will be stored.'),
      '#default_value' => $this->configuration['token_name'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void  {
    $this->configuration['json_string'] = $form_state->getValue('json_string');
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    parent::submitConfigurationForm($form, $form_state);
  }

}
