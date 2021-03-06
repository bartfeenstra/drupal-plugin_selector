<?php

/**
 * @file
 * Contains \Drupal\plugin_selector\Tests\Plugin\PluginSelector\PluginSelector\RadiosWebTest.
 */

namespace Drupal\plugin_selector\Tests\Plugin\PluginSelector\PluginSelector;

use Drupal\simpletest\WebTestBase;

/**
 * \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\Radios web test.
 *
 * @group Plugin Selector
 */
class RadiosWebTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array('filter', 'plugin_selector_test');

  /**
   * Tests the element.
   */
  protected function testElement() {
    $this->doTestElement(FALSE);
    $this->doTestElement(TRUE);
  }

  protected function buildFormPath(array $allowed_selectable_plugin_ids, $tree) {
    return sprintf('plugin_selector_test-plugin_selector-advanced_plugin_selector_base/%s/plugin_selector_radios/%d', implode(',', $allowed_selectable_plugin_ids), (int) $tree);
  }

  /**
   * Tests the element.
   *
   * @param bool $tree
   *   Whether to test the element with #tree = TRUE or not.
   */
  protected function doTestElement($tree) {
    $name_prefix = $tree ? 'tree[plugin][container]' : 'container';

    // Test the presence of default elements without available plugins.
    $path = $this->buildFormPath(['none'], $tree);
    $this->drupalGet($path);
    $this->assertNoFieldByName($name_prefix . '[select][container][container][plugin_id]');
    $this->assertNoFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertText(t('There are no available options.'));

    // Test the presence of default elements with one available plugin.
    $path = $this->buildFormPath(['plugin_selector_configurable'], $tree);
    $this->drupalGet($path);
    $this->assertNoFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertNoFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertNoText(t('There are no available options.'));

    // Test the presence of default elements with multiple available plugins.
    $path = $this->buildFormPath(['plugin_selector_configurable', 'plugin_selector_non_configurable'], $tree);
    $this->drupalGet($path);
    $this->assertFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertFieldByName($name_prefix . '[select][container][change]', t('Choose'));
    $this->assertNoText(t('There are no available options.'));

    // Choose a plugin.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][container][plugin_id]' => 'plugin_selector_non_configurable',
    ), t('Choose'));
    $this->assertFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertFieldByName($name_prefix . '[select][container][change]', t('Choose'));

    // Change the plugin.
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][container][plugin_id]' => 'plugin_selector_configurable',
    ), t('Choose'));
    $this->assertFieldByName($name_prefix . '[select][container][plugin_id]');
    $this->assertFieldByName($name_prefix . '[select][container][change]', t('Choose'));

    // Submit the form.
    $foo = $this->randomString();
    $this->drupalPostForm(NULL, array(
      $name_prefix . '[select][container][plugin_id]' => 'plugin_selector_configurable',
      $name_prefix . '[plugin_form][foo]' => $foo,

    ), t('Submit'));

    $state = \Drupal::state();
    /** @var \Drupal\Component\Plugin\PluginInspectionInterface|\Drupal\Component\Plugin\ConfigurablePluginInterface $selected_plugin */
    $selected_plugin = $state->get('plugin_selector_test_advanced_plugin_selector_base');
    $this->assertEqual($selected_plugin->getPluginId(), 'plugin_selector_configurable');
    $this->assertEqual($selected_plugin->getConfiguration(), [
      'foo' => $foo,
    ]);
  }
}
