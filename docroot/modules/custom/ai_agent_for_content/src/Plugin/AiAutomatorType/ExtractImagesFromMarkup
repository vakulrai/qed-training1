<?php

namespace Drupal\ai_agent_for_content\Plugin\AiAutomatorType;

use Drupal\ai_automators\Attribute\AiAutomatorType;
use Drupal\ai_automators\PluginBaseClasses\ComplexTextChat;
use Drupal\ai_automators\PluginInterfaces\AiAutomatorTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Masterminds\HTML5;
use Drupal\ai_automators\PluginBaseClasses\Link;

/**
 * Strips the tags of a body.
 */
 #[AiAutomatorType(
  id: 'ai_automator_extract_images_string_long',
  label: new TranslatableMarkup('LLM : Extract images from DOM'),
  field_rule: 'link',
  target: '',
)]
class ExtractImagesFromMarkup extends Link implements AiAutomatorTypeInterface {

  /**
   * {@inheritDoc}
   */
  public $title = 'LLM : Extract images from DOM';

  /**
   * The entity type manager.
   */
  public EntityTypeManagerInterface $entityManager;


  /**
   * {@inheritDoc}
   */
  public function needsPrompt() {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function advancedMode() {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function placeholderText() {
    return "";
  }

  /**
   * {@inheritDoc}
   */
  public function extraAdvancedFormFields(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, FormStateInterface $formState, array $defaultValues = []) {
    $form = parent::extraAdvancedFormFields($entity, $fieldDefinition, $formState, $defaultValues);

    $form['automator_use_text_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Use text format'),
      '#description' => $this->t('If you want to use a specific text format, select it here. Otherwise a text format will be used based on user rights. Always pick one for cron jobs since the cron job runs anonymous.'),
      '#options' => $this->getGeneralHelper()->getTextFormatsOptions(),
      '#default_value' => $defaultValues['automator_use_text_format'] ?? NULL,
    ];

    return $form;
  }

  /**
   * Run a chat message.
   *
   * @param string $prompt
   *   The prompt.
   * @param array $automatorConfig
   *   The automator configuration.
   * @param \Drupal\ai\Plugin\ProviderProxy $instance
   *   The LLM instance.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   *
   * @return array
   *   The response.
   */
  public function runChatMessage(string $prompt, array $automatorConfig, $instance, ?ContentEntityInterface $entity = NULL) {
    $text = $this->runRawChatMessage($prompt, $automatorConfig, $instance, $entity);

    // Normalize the response.
    $json = $this->promptJsonDecoder->decode($text);
    if (!is_array($json)) {
      throw new AiAutomatorResponseErrorException('The response was not a valid JSON response. The response was: ' . $text->getText());
    }
    return $this->decodeValueArray($this->promptJsonDecoder->decode($text));
  }

  /**
   * {@inheritDoc}
   */
  public function generate(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    $prompts = parent::generate($entity, $fieldDefinition, $automatorConfig);
    return $prompts;
  }

  /**
   * {@inheritDoc}
   */
  public function verifyValue(ContentEntityInterface $entity, $value, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    return TRUE;
  }

  /**
   * Creates a DOMDocument with UTF-8 encoding.
   *
   * @param string $contents
   *   The contents to load.
   *
   * @return \DOMDocument
   *   The created document.
   */
  public static function createDomDocument(string $contents): \DOMDocument {
    $dom = new \DOMDocument('1.0', 'UTF-8');
    if ($contents) {
      $html5 = new HTML5([
        'encoding' => 'UTF-8',
        'target_document' => $dom,
        'disable_html_ns' => TRUE,
      ]);
      $html5->loadHTML($contents);
    }
    return $dom;
  }

  /**
   * {@inheritDoc}
   */
  public function storeValues(ContentEntityInterface $entity, array $values, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Get text format.
    $config = $fieldDefinition->getConfig($entity->bundle())->getSettings();
    foreach ($values as $key => $value) {
      if ($config['title'] == 0) {
        $value['title'] = '';
      }
      $value['title'] = sprintf("%s - (Modified)", $value['title']);
      $values[$key] = $value;
    }
    $entity->set($fieldDefinition->getName(), $values);
    return TRUE;
  }

}
