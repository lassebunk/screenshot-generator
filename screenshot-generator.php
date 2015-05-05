<?php
/**
 * Plugin Name: Screenshot Generator
 * Plugin URI: https://github.com/lassebunk/screenshot-generator
 * Description: Take automatic screenshots of posts when they are updated.
 * Version: 0.1.7
 * Author: Lasse Bunk
 * Author URI: https://github.com/lassebunk
 * License: GPLv2 or later
 */

function scrgen_defaults() {
  return array(
    'width'           => '1024',
    'height'          => '768',
    'enable_cropping' => 1,
    'crop_left'       => 0,
    'crop_top'        => 0,
    'crop_width'      => 1024,
    'crop_height'     => 768,
    'social_strategy' => 'missing'
  );
}

function scrgen_setting($key) {
  return get_option("scrgen_{$key}", scrgen_defaults()[$key]);
}

function scrgen_plugin_basename() {
  return plugin_basename(__FILE__);
}

function scrgen_generate_post_screenshot($post_id) {
  // Workaround bug: http://premium.wpmudev.org/forums/topic/snapshot-faltal-error-on-get_home_path
  if (!function_exists('get_home_path' )) require_once( dirname(__FILE__) . '/../../../wp-admin/includes/file.php' );

  $url = get_permalink($post_id);
  $url_segs = parse_url($url);

  $phantomjs = scrgen_phantomjs();
  if (empty($phantomjs)) {
    error_log('The phantomjs binary was not found. Make sure it is in your PHP\'s PATH or set the PHANTOMJS constant to its path.');
    return;
  }

  $relative_path = 'wp-content/screenshots/' . $post_id . '.jpg';
  $image_path = get_home_path() . $relative_path;
  $image_url = get_home_url() . '/' . $relative_path;

  $job_id = uniqid();
  $job_path = "/tmp/{$job_id}.js";
  $tmp_path = "/tmp/{$job_id}.jpg";

  $width           = scrgen_setting('width');
  $height          = scrgen_setting('height');
  $enable_cropping = scrgen_setting('enable_cropping');
  $crop_left       = scrgen_setting('crop_left');
  $crop_top        = scrgen_setting('crop_top');
  $crop_width      = scrgen_setting('crop_width');
  $crop_height     = scrgen_setting('crop_height');

  $url = strip_tags($url);
  $url = str_replace(';', '', $url);
  $url = str_replace('"', '', $url);
  $url = str_replace('\'', '/', $url);
  $url = str_replace('<?', '', $url);
  $url = str_replace('<?', '', $url);
  $url = str_replace('\077', ' ', $url);

  $url = escapeshellcmd($url);
  $src = "

  var page = require('webpage').create();

  page.viewportSize = { width: {$width}, height: {$height} };

  ";

  if ($enable_cropping) {
      $src .= "page.clipRect = { top: {$crop_top}, left: {$crop_left}, width: {$crop_width}, height: {$crop_height} };";
  }

  $src .= "

  page.open('{$url}', function () {
      page.render('{$tmp_path}');
      phantom.exit();
  });


  ";

  file_put_contents($job_path, $src);

  $exec = $phantomjs . ' ' . $job_path;
  $escaped_command = escapeshellcmd($exec);

  exec($escaped_command);

  unlink($job_path);

  if (is_file($tmp_path)) {
      rename($tmp_path, $image_path);
      do_action('scrgen_post_screenshot_generated', $post_id, $image_url);
  }
}

function scrgen_queue_post_update($post_id) {
  wp_schedule_single_event(time(), 'scrgen_update_post_screenshot', array($post_id));
}

function scrgen_update_post_meta($post_id, $screenshot_url) {
  update_post_meta($post_id, '_scrgen_screenshot', $screenshot_url);
}

function scrgen_phantomjs() {
  $path = exec('which phantomjs');

  if (defined('PHANTOMJS')) {
    return PHANTOMJS;
  } elseif (!empty($path)) {
    return $path;
  }
}

function scrgen_screenshot() {
  $post_id = get_the_ID();
  $screenshot = get_post_meta($post_id, '_scrgen_screenshot', true);
  return $screenshot;
}

function scrgen_screenshot_if_needed() {
  $thumbnail_id = get_post_thumbnail_id();
  $screenshot = scrgen_screenshot();

  if (empty($thumbnail_id) && !empty($screenshot)) {
    return $screenshot;
  }
}

function scrgen_opengraph() {
  $screenshot = scrgen_screenshot();
  if (!empty($screenshot)) {
    echo '<meta property="og:image" content="' . $screenshot . '" />';
  }
}

function scrgen_twitter() {
  $screenshot = scrgen_screenshot();
  if (!empty($screenshot)) {
    echo '<meta name="twitter:image:src" content="' . $screenshot . '"/>';
  }
}

function scrgen_head() {
  $strategy = scrgen_setting('social_strategy');
  if ($strategy == '') return;

  $thumbnail_id = get_post_thumbnail_id();

  if ($strategy == 'always' || ($strategy == 'missing' && empty($thumbnail_id))) {
    scrgen_opengraph();
    scrgen_twitter();
  }
}

add_action('scrgen_update_post_screenshot', 'scrgen_generate_post_screenshot');
add_action('scrgen_post_screenshot_generated', 'scrgen_update_post_meta', 10, 2);
add_action('post_updated', 'scrgen_queue_post_update');

add_action('wp_head', 'scrgen_head', 1);

if (is_admin()) {
  require_once(dirname(__FILE__) . '/admin.php');
}