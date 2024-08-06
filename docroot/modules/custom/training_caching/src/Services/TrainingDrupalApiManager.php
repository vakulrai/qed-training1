<?php

namespace Drupal\training_caching\Services;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Service manager for QED training.
 *
 * Provides a Space for implementing Generic.
 * Functionalities for the Platform.
 */
class TrainingDrupalApiManager {

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity Display Repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a VeoliaPlatformManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Config Factory.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entityDisplayRepository
   *   The entity Display Repository service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The Logger Service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    EntityDisplayRepositoryInterface $entityDisplayRepository,
    LoggerChannelFactoryInterface $loggerFactory,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->configFactory = $configFactory;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->entityDisplayRepository = $entityDisplayRepository;
    $this->loggerFactory = $loggerFactory;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Helper method to get top N nodes.
   *
   * @param string $bundle
   *   The Entity Bundle.
   * @param integer $count
   *   The range for the query.
   */
  public function getTopNNodes(string $bundle = NULL, int $count = 3 , $sort_type = "DESC"): array {
    $entities = [];
    $storage = $this->entityTypeManager->getStorage('node');
    $query_result = $storage->getQuery();
    $query_result->accessCheck(FALSE);
    if ($bundle) {
      $query_result->condition('bundle', $bundle);
    }
    $query_result->sort('changed', $sort_type);
    $query_result->range(0, $count);
    $entities = $query_result->execute();
    if ($entities) {
      foreach ($storage->loadMultiple($entities) as $key => $entity) {
        $list[$entity->id()] = $entity->getTitle();
      }
    }
    return $list;
  }

  /**
   * Helper method to invalidate cache of top N nodes.
   */
  public function invalidateCacheofTopNNodes(): bool {
    $node_list = $this->getTopNNodes(NULL, 3);
    $cache_tags = [];
    if ($node_list) {
      foreach ($node_list as $entity_id => $entity_title) {
        $cache_tags[] = "node:" . $entity_id;
      }
      return Cache::invalidateTags($cache_tags) ? TRUE : FALSE;
    }
    return FALSE;
  }

}
