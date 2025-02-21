**Varnish Variation with a cache tags + Cache contexts**
 - The content block is similar and displaying the content in Descending order
 - Block 1 is controlled by cache tags and ,Block 2 is handling Cache contexts
![Amet-Drush-Site-Install](https://github.com/user-attachments/assets/7de67538-1505-4c87-9091-19b784bc6c96)


**Configured Varnish configs:**
- Followed Blogs : https://www.varnish-software.com/developers/tutorials/configuring-varnish-drupal/
I was able to configure memcache on my drupal + ddev instance: 

- to test it i have used : ddev add-on get ddev/ddev-memcached
- Used composer require drupal/memcache and enabled
- Used the below settings in my settings.php
	$settings['memcache']['servers'] = ['127.0.0.1:11211' => 'default'];
	$settings['memcache']['bins'] = ['default' => 'default'];
	$settings['memcache']['key_prefix'] = 'drupal_';

	// Use Memcache as default backend for cache bins
	$settings['cache']['default'] = 'cache.backend.memcache';


- Ran below commands to vrify the cache backend bin is storing data: 
    drush ev "\Drupal::service('cache.default')->set('test_key', 'test_value');"
    drush ev "print_r(\Drupal::service('cache.default')->get('test_key'));"


- echo "stats settings" | nc 127.0.0.1 11211 and Look for increasing cmd_get and cmd_set values.

- and did a drush cr and verified on page load cache tables remain empty.

- 
  ![Purge-Drush-Site-Install](https://github.com/user-attachments/assets/a5b3c73d-c58c-4f4e-be8b-24390e072fcd)

