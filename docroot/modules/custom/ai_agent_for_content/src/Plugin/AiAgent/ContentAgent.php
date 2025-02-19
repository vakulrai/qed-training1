<?php

namespace Drupal\ai_agent_for_content\Plugin\AiAgent;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\ai_agents\Attribute\AiAgent;
use Drupal\ai_agents\PluginBase\AiAgentBase;
use Drupal\ai_agents\PluginInterfaces\AiAgentInterface;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Plugin implementation of the Content Agent.
 */
#[AiAgent(
  id: 'content_agent',
  label: new TranslatableMarkup('Content Agent'),
)]
class ContentAgent extends AiAgentBase {

  use DependencySerializationTrait;

  /**
   * Questions to ask.
   *
   * @var array
   */
  protected $questions;

  /**
   * Information to inform.
   *
   * @var string
   */
  protected $information;

  /**
   * The full data of the initial task.
   *
   * @var array
   */
  protected $data;

  /**
   * Task type.
   *
   * @var string
   */
  protected $taskType;

  /**
   * The result.
   *
   * @var array
   */
  protected array $result = [];

  /**
   * The created terms.
   *
   * @var array
   */
  protected $createdTerms = [];

  /**
   * The edited terms.
   *
   * @var array
   */
  protected $editedNodes = [];

  /**
   * {@inheritDoc}
   */
  public function getId() {
    return 'content_agent';
  }

  /**
   * {@inheritDoc}
   */
  public function agentsNames() {
    return [
      'TRA Content Agent',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function agentsCapabilities() {
    return [
      'content_agent' => [
        'name' => 'TRA Content Agent',
        'description' => "This agent is capable of creating new vocabularies, editing & renaming nodes.",
        'inputs' => [
          'free_text' => [
            'name' => 'Prompt',
            'type' => 'string',
            'description' => 'The prompt to create, edit, delete or ask questions about nodes',
            'default_value' => '',
          ],
        ],
        'outputs' => [
          'answers' => [
            'description' => 'The answers to the questions asked about the node',
            'type' => 'string',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function setData($data) {
    $this->data[] = $data;
  }

  /**
   * {@inheritDoc}
   */
  public function isAvailable() {
    // Check if taxonomy module is installed.
    return $this->agentHelper->isModuleEnabled('node');
  }

  /**
   * {@inheritDoc}
   */
  public function isNotAvailableMessage() {
    return $this->t('You need to enable the node module to use this.');
  }

  /**
   * {@inheritDoc}
   */
  public function getRetries() {
    return 2;
  }

  /**
   * {@inheritDoc}
   */
  public function getData() {
    return $this->data;
  }

  /**
   * {@inheritDoc}
   */
  public function answerQuestion() {
    if (isset($this->data[0]['information'])) {
      return $this->data[0]['information'];
    }
    $data = $this->agentHelper->runSubAgent('replyToPrompt', [
      'All currently available content types' => $this->getVerboseNodeTypesAsString(),
      'All currently available nodes' => $this->getVerboseNodeAsString(),
    ]);

    $answer = "";
    if (isset($data[0]['information'])) {
      foreach ($data as $dataPoint) {
        $answer .= $dataPoint['information'] . "\n";
      }
      return $answer;
    }

    return $this->t("Sorry, I got no answers for you.");
  }

  /**
   * {@inheritDoc}
   */
  public function askQuestion() {
    return implode("\n", $this->questions);
  }

  /**
   * {@inheritDoc}
   */
  public function inform() {
    return $this->information;
  }

  /**
   * {@inheritDoc}
   */
  public function getHelp() {
    $help = $this->t("This agent can answer questions about nodes.");
    return $help;
  }

  /**
   * {@inheritDoc}
   */
  public function hasAccess() {
    // Check for permissions.
    if (!$this->currentUser->hasPermission('administer node')) {
      return AccessResult::forbidden();
    }
    return parent::hasAccess();
  }

  /**
   * {@inheritDoc}
   */
  public function determineSolvability() {
    parent::determineSolvability();
    $this->taskType = $this->determineTypeOfTask();
    switch ($this->taskType) {
      case 'create_node':
        return AiAgentInterface::JOB_SOLVABLE;

      case 'edit_node':
        return AiAgentInterface::JOB_SOLVABLE;

      case 'edit_node_with_route_context':
        return AiAgentInterface::JOB_SOLVABLE;

      case 'fail':
        return AiAgentInterface::JOB_INFORMS;

      case 'exported_entity_list':
      case 'information':
        return AiAgentInterface::JOB_SHOULD_ANSWER_QUESTION;
    }

    return AiAgentInterface::JOB_NOT_SOLVABLE;
  }

  /**
   * {@inheritDoc}
   */
  public function solve() {
    $messages = [];
    $messages[] = '';
    foreach ($this->data as $data) {
      switch ($data['action']) {
        case 'create_node':
          try {
//            $this->createNode($data);
          }
          catch (\Exception $e) {
            $messages[] = 'There was an error creating the node type: ' . $e->getMessage();
          }
          break;

        case 'edit_node':
          try {
//            $this->editNode($data);
          }
          catch (\Exception $e) {
            $messages[] = 'There was an error editing the node type: ' . $e->getMessage();
          }
          break;

        case 'edit_node_with_route_context':
          try {
//            $this->editNode($data, TRUE);
          }
          catch (\Exception $e) {
            $messages[] = 'There was an error editing the node type: ' . $e->getMessage();
          }
          break;
      }
    }
    $message = '';
    if (count($messages)) {
      $message .= "The following are errors:\n" . implode("\n", $messages);
    }
    // if (count($this->result)) {
    //   $message .= "The following are results:\n" . implode("\n", $this->result) . "\n";
    // }
    // $listTermUrls = [];
    // if (count($this->createdTerms)) {
    //   $message .= "The following terms were created:\n";
    //   $i = 0;
    //   foreach ($this->createdTerms as $term) {
    //     if ($i > 10) {
    //       $message .= "And more...\n\n";
    //       break;
    //     }
    //     $i++;
    //     $message .= $term['name'] . ' - ' . $term['id'] . ' - ' . $term['vid'] . "\n";
    //     $listTermUrls[$term['vid']] = $term['vid'];
    //   }
    // }
    // if (count($this->editedTerms)) {
    //   $message .= "The following terms were edited:\n";
    //   $i = 0;
    //   foreach ($this->editedTerms as $term) {
    //     if ($i > 10) {
    //       $message .= "And more...\n\n";
    //       break;
    //     }
    //     $message .= $term['name'] . ' - ' . $term['id'] . ' - ' . $term['vid'] . "\n";
    //   }
    //   $listTermUrls[$term['vid']] = $term['vid'];
    // }
    // if (count($listTermUrls)) {
    //   $message .= "You can see the terms here:\n";
    //   foreach ($listTermUrls as $vid) {
    //     $url = Url::fromRoute('entity.taxonomy_vocabulary.overview_form', [
    //       'taxonomy_vocabulary' => $vid,
    //     ])->toString();
    //     $message .= $url . "\n";
    //   }
    // }
    return $message;
  }

  /**
   * {@inheritDoc}
   */
  public function approveSolution() {
    $this->data[0]['action'] = 'create';
  }

  /**
   * Check so all requirements are there.
   *
   * @return bool
   *   If all requirements are there.
   */
  public function checkRequirements() {
    return TRUE;
  }

  /**
   * Determine if the context is asking a question or wants a audit done.
   *
   * @return string
   *   The context.
   */
  public function determineTypeOfTask() {
    $data = $this->agentHelper->runSubAgent('replyToPrompt', [
      'All currently available content types' => $this->getVerboseNodeTypesAsString(),
      'All currently available nodes' => $this->getVerboseNodeAsString(),
    ]);
    // Return error early.
    if (!isset($data[0]['action'])) {
      $this->information = 'Sorry, we could not understand what you wanted to do, please try again.';
      return [
        ['action' => 'fail'],
      ];
    }
    $this->data = $data;
    return $data[0]['action'];
  }

  /**
   * Edit a Node.
   *
   * @param array $data
   *   The data to edit the vocabulary.
   * @param bool $route_context
   *   To use the route context.
   *
   * @throws \Exception
   *   If the vocabulary could not be edited.
   */
  public function editNode($data, $route_context = FALSE) {
    $node = Node::load($data['data_name']);
    $this->setOriginalEntity($node);
    foreach ([
      'title' => 'readable_name',
      'body' => 'body',
    ] as $key => $change) {
      if (isset($data[$change])) {
        $vocabulary->set($key, $data[$change]);
      }
    }
    if ($node->save()) {
      $this->structuredResultData->setOriginalEntity($node, $diff);
      $url = Url::fromRoute('entity.node.canonical', [
        'node' => $data['data_name'],
      ])->toString();
      $this->result[] = $this->t('The vocabulary %name has been edited, check out the settings here %url.', [
        '%name' => $node->getTitle(),
        '%url' => $url,
      ]);
    }
    else {
      throw new \Exception('The vocabulary could not be edited.');
    }
  }

  /**
   * Get all the vocabularies.
   *
   * @return array
   *   The vocabularies.
   */
  public function getNodetypes() {
    $node_types = NodeType::loadMultiple();
    $typeList = [];
    foreach ($node_types as $node_type) {
      $typeList[$node_type->id()] = $node_type->label();
    }
    return $typeList;
  }

  /**
   * Get all the vocabularies as a string with verbose information.
   *
   * @return string
   *   The vocabularies as a string.
   */
  public function getVerboseNodeTypesAsString($vid = NULL) {
    $vocabularies = $this->getNodetypes();
    $list = "";
    foreach ($vocabularies as $dataName => $vocabulary) {
      // Load the vocabulary.
      $nodeType = NodeType::load($dataName);

      // Show all the configurations.
      $list .= $nodeType->label() . ' - dataname: ' . $dataName . "\n";
      $list .= 'Node type Language: ' . $nodeType->get('langcode') . "\n";
      $list .= "\n";
    }
    return $list;
  }

  /**
   * Get the current route name.
   *
   * @return string
   *   The route name.
   */
  public function getCurrentRoute() {
    $vocabularies = $this->getNodetypes();
    $list = "";
    foreach ($vocabularies as $dataName => $vocabulary) {
      // Load the vocabulary.
      $nodeType = NodeType::load($dataName);

      // Show all the configurations.
      $list .= $nodeType->label() . ' - dataname: ' . $dataName . "\n";
      $list .= 'Node type Language: ' . $nodeType->get('langcode') . "\n";
      $list .= "\n";
    }
    return $list;
  }

  /**
   * Get all the nodes as a string with verbose information.
   *
   * @return string
   *   The nodes as a string.
   */
  public function getVerboseNodeAsString() {
    $nodes = Node::loadMultiple();
    $list = "";
    foreach ($nodes as $node) {

      // Show all the configurations.
      $list .= $node->getTitle() . ' - dataname: ' . $node->id() . "\n";
      $list .= 'Node url: ' . $node->toUrl()->toString() . "\n";
    }
    return $list;
  }

}
