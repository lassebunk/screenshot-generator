<?php
/**
 * Plugin Name: Screenshot Generator
 * Plugin URI: https://github.com/lassebunk/screenshot-generator
 * Description: Take automatic screenshots of posts when they are updated.
 * Version: 0.0.1
 * Author: Lasse Bunk
 * Author URI: https://github.com/lassebunk
 * License: MIT
 */

function scrgen_generate_post_screenshot($post_id) {
  // Workaround bug: http://premium.wpmudev.org/forums/topic/snapshot-faltal-error-on-get_home_path
  if (!function_exists('get_home_path' )) require_once( dirname(__FILE__) . '/../../../wp-admin/includes/file.php' );

  $url = get_permalink($post_id);
  $url_segs = parse_url($url);

  $relative_path = 'wp-content/screenshots/' . $post_id . '.jpg';
  $image_path = get_home_path() . $relative_path;
  $image_url = get_home_url() . '/' . $relative_path;

  $job_id = uniqid();
  $job_path = "/tmp/{$job_id}.js";
  $tmp_path = "/tmp/{$job_id}.jpg";

  $w = 1024;
  $h = 768;
  $clipw = 1024;
  $cliph = 640;

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

  page.viewportSize = { width: {$w}, height: {$h} };

  ";

  if (isset($clipw) && isset($cliph)) {
      $src .= "page.clipRect = { top: 0, left: 0, width: {$clipw}, height: {$cliph} };";
  }

  $src .= "

  page.open('{$url}', function () {
      page.render('{$tmp_path}');
      phantom.exit();
  });


  ";

  file_put_contents($job_path, $src);

  $exec = PHANTOMJS . ' ' . $job_path;
  $escaped_command = escapeshellcmd($exec);

  exec($escaped_command);

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

function scrgen_opengraph($original) {
  $screenshot = scrgen_screenshot_if_needed();
  if (!empty($screenshot)) {
    echo '<meta property="og:image" content="' . $screenshot . '" />';
  }
}

function scrgen_twitter() {
  $screenshot = scrgen_screenshot_if_needed();
  if (!empty($screenshot)) {
    echo '<meta name="twitter:image:src" content="' . $screenshot . '"/>';
  }
}

add_action('scrgen_update_post_screenshot', 'scrgen_generate_post_screenshot');
add_action('scrgen_post_screenshot_generated', 'scrgen_update_post_meta', 10, 2);
add_action('post_updated', 'scrgen_queue_post_update');

add_action('wp_head', 'scrgen_opengraph', 1);
add_action('wp_head', 'scrgen_twitter', 1);
