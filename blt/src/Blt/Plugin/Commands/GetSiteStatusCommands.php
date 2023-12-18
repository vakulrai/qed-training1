<?php

namespace Example\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "custom" namespace.
 */
class GetSiteStatusCommands extends BltTasks {

  /**
   * Set admin name and password.
   *
   * @command custom:create-user
   * @description Create a random user by name and password and return its uli
   */
  public function createUser($name, $pass) {

    // Need to clear cache after changing name of admin.
    $this->taskDrush()
      ->drush('cr')
      ->run();

    // Sets password.
    $user_created_success = $this->taskDrush()
      ->drush("user:create --password={$pass}")
      ->arg($name)
      ->run();
    if ($user_created_success->wasSuccessful()) {
      $this->say("User created successfully !, generating login link.");
      // Sets password.
      $uli = $this->taskDrush()
        ->drush("user:login --name={$name}")
        ->run();
      $this->say("ULI is : \n\n" . $uli->getOutputData());
    }
  }

}
