<?php
/**
 * Plugin Name: Ceat Product Parser
 * Plugin URI: https://github.com/XTishka/wp-ceat-product-parser
 * Description: Bootstrap for the Ceat Product Parser plugin built with an object-oriented architecture.
 * Version: 0.1.0
 * Author: XTF studio | Takhir Berdyiev
 * Author URI: https://xtf.com.ua
 * Text Domain: ceat-product-parser
 * Domain Path: /languages
 *
 * @package CeatProductParser
 */

declare(strict_types=1);

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! defined('CEAT_PP_VERSION')) {
    define('CEAT_PP_VERSION', '0.1.0');
}

if (! defined('CEAT_PP_PLUGIN_FILE')) {
    define('CEAT_PP_PLUGIN_FILE', __FILE__);
}

if (! defined('CEAT_PP_PLUGIN_DIR')) {
    define('CEAT_PP_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (! defined('CEAT_PP_PLUGIN_URL')) {
    define('CEAT_PP_PLUGIN_URL', plugin_dir_url(__FILE__));
}

require_once CEAT_PP_PLUGIN_DIR . 'includes/autoloader.php';

use CeatProductParser\Plugin;

if (! function_exists('ceat_product_parser')) {
    /**
     * Retrieve the Ceat Product Parser plugin instance.
     */
    function ceat_product_parser()
    {
        return Plugin::instance();
    }
}

ceat_product_parser()->boot();

register_activation_hook(__FILE__, [Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);
register_uninstall_hook(__FILE__, ['CeatProductParser\\Plugin', 'uninstall']);
