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
use Drupal\training_caching\Services\TrainingDrupalApiManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Caching block with cache tags.
 *
 * @Block(
 *  id = "training_caching_ex1",
 *  admin_label = @Translation("Cache tags"),
 *  context_definitions = {
 *    "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *  }
 * )
 */
class TrainingCaching extends BlockBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, TrainingDrupalApiManager $api_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->trainingDrupalApiManager = $api_manager;
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->getContextValue('node');
    $node_list = $this->trainingDrupalApiManager->getTopNNodes(NULL, 3);
    $content = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#title' => 'Demonstrating cache tags: Top 3 nodes',
      '#items' => $node_list,
    ]; 
    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $node = $this->getContextValue('node');
    $node_list = $this->trainingDrupalApiManager->getTopNNodes(NULL, 3);
    $list = [];
    $cache_tags = $node->getCacheTags();
    if ($node_list) {
      foreach ($node_list as $entity_id => $entity_title) {
        $cache_tags[] = "node:" . $entity_id;
      }
    }
    return Cache::mergeTags(
      parent::getCacheTags(),
      $cache_tags
    );
  }

}
