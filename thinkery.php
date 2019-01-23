<?php
/**
 * Plugin name: Thinkery
 * Plugin author: Alex Kirk
 * Plugin URI: https://github.com/akirk/thinkery
 * Version: 0.1
 *
 * Description: The new home for thinkery.me
 *
 * License: GPL2
 * Text Domain: thinkery
 * Domain Path: /languages/
 *
 * @package Thinkery
 */

/**
 * This file loads all the dependencies the Thinkery plugin.
 */

defined( 'ABSPATH' ) || exit;

include __DIR__ . '/class-thinkery-admin.php';
include __DIR__ . '/class-thinkery-frontend.php';
include __DIR__ . '/class-thinkery-importer.php';
include __DIR__ . '/class-thinkery-xml-parser.php';
include __DIR__ . '/class-thinkery-things.php';
include __DIR__ . '/class-thinkery.php';

add_action( 'plugins_loaded', array( 'Thinkery', 'init' ) );
