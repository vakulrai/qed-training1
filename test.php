<?php

$config = [
  "max_tokens" => 4096,
  "temperature" => 1,
  "frequency_penalty" => 0,
  "presence_penalty" => 0,
  "top_p" => 1,
];

$input = new \Drupal\ai\OperationType\Chat\ChatInput([
  new \Drupal\ai\OperationType\Chat\ChatMessage("user", "get me a list of nodes"),
]);

$ai_provider = \Drupal::service('ai.provider')->createInstance('openai');
$ai_provider->setConfiguration($config);
// Normalized $response will be a ChatMessage object.
$response = $ai_provider->chat($input, 'gpt-4o', ["content_agent"])->getNormalized();

print_r($response);