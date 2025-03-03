<?php

namespace Drupal\training_caching\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\training_caching\Services\TrainingDrupalApiManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Caching block with cache contexts.
 *
 * @Block(
 *  id = "training_caching_ex2",
 *  admin_label = @Translation("Cache context"),
 *  context_definitions = {
 *    "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *  }
 * )
 */
class TrainingCachingContexts extends BlockBase implements ContainerFactoryPluginInterface {

 /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\training_caching\Services\TrainingDrupalApiManager definition.
   *
   * @var \Drupal\training_caching\Services\TrainingDrupalApiManager
   */
  protected $trainingDrupalApiManager;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal\Core\Entity\EntityTypeManagerInterface.
   * @param Drupal\training_caching\Services\TrainingDrupalApiManager $api_manager
   *   Drupal\training_caching\Services\TrainingDrupalApiManager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, TrainingDrupalApiManager $api_manager, AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->trainingDrupalApiManager = $api_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('training_caching.manager'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $roles = $this->currentUser->getRoles();

    if (in_array('administrator', $roles)) {
      $message = "Hello, Admin!";
    } elseif (in_array('content_editor', $roles)) {
      $message = "Welcome back, valued member!";
    } else {
      $message = "Welcome, Guest!";
    }

    return [
      '#markup' => $this->t($message),
      '#cache' => [
        'contexts' => ['user.roles'], // This ensures cache varies by user role.
      ],
    ];
  }
}
