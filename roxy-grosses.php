<?php
/**
 * Plugin Name: Roxy Grosses (WooCommerce)
 * Description: Pulls ticket sales from Square, generates a grosses sheet, and emails scheduled reports to Comscore and Lori.
 * Version: 0.2.0
 * Author: Newport Roxy (AI Team)
 */

if (!defined('ABSPATH')) exit;

define('ROXY_GROSSES_VER', '0.2.0');
define('ROXY_GROSSES_PATH', plugin_dir_path(__FILE__));
define('ROXY_GROSSES_URL', plugin_dir_url(__FILE__));

require_once ROXY_GROSSES_PATH . 'includes/class-roxy-grosses-settings.php';
require_once ROXY_GROSSES_PATH . 'includes/class-roxy-grosses-square.php';
require_once ROXY_GROSSES_PATH . 'includes/class-roxy-grosses-store.php';
require_once ROXY_GROSSES_PATH . 'includes/class-roxy-grosses-reporter.php';
require_once ROXY_GROSSES_PATH . 'includes/class-roxy-grosses-scheduler.php';

register_activation_hook(__FILE__, function () {
  if (!class_exists('WooCommerce')) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die('Roxy Grosses requires WooCommerce to be installed and active.');
  }

  \RoxyGrosses\Settings::ensure_defaults();
  \RoxyGrosses\Store::install_schema();
  \RoxyGrosses\Scheduler::sync_schedule();
});

register_deactivation_hook(__FILE__, function () {
  \RoxyGrosses\Scheduler::clear_schedule();
});

add_action('plugins_loaded', function () {
  if (!class_exists('WooCommerce')) {
    return;
  }

  \RoxyGrosses\Store::maybe_upgrade_schema();
  \RoxyGrosses\Settings::init();
  \RoxyGrosses\Scheduler::init();
  \RoxyGrosses\Reporter::init();
});
