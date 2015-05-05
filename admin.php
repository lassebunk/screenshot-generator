<?php
add_action( 'admin_menu', 'scrgen_add_admin_menu' );
add_action( 'admin_init', 'scrgen_settings_init' );
add_action( 'admin_action_scrgen-regenerate', 'scrgen_admin_regenerate' );
add_filter( 'plugin_action_links_' . scrgen_plugin_basename(), 'scrgen_plugin_action_links' );

function scrgen_plugin_action_links ( $links ) {
  $mylinks = array(
    '<a href="' . admin_url( 'options-general.php?page=screenshot-generator' ) . '">Settings</a>',
  );
  return array_merge( $links, $mylinks );
}

function scrgen_add_admin_menu() {
  add_options_page( 'Screenshot Generator', 'Screenshot Generator', 'manage_options', 'screenshot-generator', 'scrgen_options_page' );
}

function scrgen_admin_regenerate() {
  query_posts( 'post_status=publish&post_type=any&posts_per_page=-1' );

  while ( have_posts() ) : the_post();
    $id = get_the_ID();
    scrgen_queue_post_update($id);
  endwhile;

  wp_reset_query();
  wp_redirect( $_SERVER['HTTP_REFERER'] . '&msg=regenerating' );
  exit();
}

function scrgen_settings_init() {
  register_setting( 'scrgen-settings', 'scrgen_width' );
  register_setting( 'scrgen-settings', 'scrgen_height' );
  register_setting( 'scrgen-settings', 'scrgen_enable_cropping' );
  register_setting( 'scrgen-settings', 'scrgen_crop_left' );
  register_setting( 'scrgen-settings', 'scrgen_crop_top' );
  register_setting( 'scrgen-settings', 'scrgen_crop_width' );
  register_setting( 'scrgen-settings', 'scrgen_crop_height' );
  register_setting( 'scrgen-settings', 'scrgen_social_strategy' );

  // Size section

  add_settings_section(
    'scrgen_size_section',
    __( 'Screenshot size', 'scrgen' ),
    '',
    'scrgen-settings'
  );

  add_settings_field(
    'scrgen_width',
    __( 'Width', 'scrgen' ),
    'scrgen_width_render',
    'scrgen-settings',
    'scrgen_size_section'
  );

  add_settings_field(
    'scrgen_height',
    __( 'Height', 'scrgen' ),
    'scrgen_height_render',
    'scrgen-settings',
    'scrgen_size_section'
  );

  // Cropping section

  add_settings_section(
    'scrgen_cropping_section',
    __( 'Cropping', 'scrgen' ),
    '',
    'scrgen-settings'
  );

  add_settings_field(
    'scrgen_enable_cropping',
    __( 'Enable cropping', 'scrgen' ),
    'scrgen_enable_cropping_render',
    'scrgen-settings',
    'scrgen_cropping_section'
  );

  add_settings_field(
    'scrgen_crop_left',
    __( 'Left', 'scrgen' ),
    'scrgen_crop_left_render',
    'scrgen-settings',
    'scrgen_cropping_section'
  );

  add_settings_field(
    'scrgen_crop_top',
    __( 'Top', 'scrgen' ),
    'scrgen_crop_top_render',
    'scrgen-settings',
    'scrgen_cropping_section'
  );

  add_settings_field(
    'scrgen_crop_width',
    __( 'Width', 'scrgen' ),
    'scrgen_crop_width_render',
    'scrgen-settings',
    'scrgen_cropping_section'
  );

  add_settings_field(
    'scrgen_crop_height',
    __( 'Height', 'scrgen' ),
    'scrgen_crop_height_render',
    'scrgen-settings',
    'scrgen_cropping_section'
  );

  // Social section

  add_settings_section(
    'scrgen_social_section',
    __( 'Social settings', 'scrgen' ),
    'scrgen_social_section',
    'scrgen-settings'
  );

  add_settings_field(
    'scrgen_social_strategy',
    __( 'Enable social screenshots', 'scrgen' ),
    'scrgen_social_strategy_render',
    'scrgen-settings',
    'scrgen_social_section'
  );

  // Manual section

  add_settings_section(
    'scrgen_manual_section',
    __( 'Displaying screenshots manually', 'scrgen' ),
    'scrgen_manual_section',
    'scrgen-settings'
  );

  // Regenerate section

  add_settings_section(
    'scrgen_regenerate_section',
    __( 'Regenerate screenshots', 'scrgen' ),
    'scrgen_regenerate_section',
    'scrgen-settings'
  );
}


function scrgen_width_render() {
  ?>
  <input type='number' name='scrgen_width' value='<?php echo scrgen_setting('width'); ?>'> px
  <?php
}


function scrgen_height_render() {
  ?>
  <input type='number' name='scrgen_height' value='<?php echo scrgen_setting('height'); ?>'> px
  <?php
}

function scrgen_enable_cropping_render() {
  ?>
  <label>
    <input type='checkbox' name='scrgen_enable_cropping' <?php checked(scrgen_setting('enable_cropping'), 1); ?> value='1'>
    Crop screenshots using the below settings.
  </label>
  <?php
}


function scrgen_crop_left_render() {
  ?>
  <input type='number' name='scrgen_crop_left' value='<?php echo scrgen_setting('crop_left'); ?>'> px
  <?php
}


function scrgen_crop_top_render() {
  ?>
  <input type='number' name='scrgen_crop_top' value='<?php echo scrgen_setting('crop_top'); ?>'> px
  <?php
}


function scrgen_crop_width_render() {
  ?>
  <input type='number' name='scrgen_crop_width' value='<?php echo scrgen_setting('crop_width'); ?>'> px
  <?php
}


function scrgen_crop_height_render() {
  ?>
  <input type='number' name='scrgen_crop_height' value='<?php echo scrgen_setting('crop_height'); ?>'> px
  <?php
}


function scrgen_social_strategy_render() {
  ?>
  <label>
    <input type='radio' name='scrgen_social_strategy' <?php checked(scrgen_setting('social_strategy'), 'missing'); ?> value='missing'>
    Add screenshots for social media when no featured image is available.
  </label>
  <br />
  <label>
    <input type='radio' name='scrgen_social_strategy' <?php checked(scrgen_setting('social_strategy'), 'always'); ?> value='always'>
    Always add screenshots for social media.
  </label>
  <br />
  <label>
    <input type='radio' name='scrgen_social_strategy' <?php checked(scrgen_setting('social_strategy'), ''); ?> value=''>
    Never add screenshots for social media.
  </label>
  <?php
}

function scrgen_social_section() {
  echo __( '<code>og:image</code> and <code>twitter:image:src</code> can be added automatically to the HTML <code>&lt;head&gt;</code>.', 'scrgen' );
}

function scrgen_manual_section() {

  echo __( 'You can display screenshots manually inside posts by inserting: <pre>&lt;?php echo scrgen_screenshot(); ?&gt;</pre>', 'scrgen' );

}

function scrgen_regenerate_section() {
  ?>
  <p>
    You can regenerate all screenshots if you have changed your settings, your site layout, or if you are using the plugin for the first time.
  </p>
  <p>
    <a href="<?php echo admin_url( 'admin.php'); ?>?action=scrgen-regenerate" onclick="return confirm('Are you sure you want to regenerate all screenshots?');">Regenerate all screenshots</a> <em>(you will be asked to confirm)</em>
  </p>
  <?php
}

function scrgen_options_page() {
  ?>
  <form action='options.php' method='post'>
   
    <h2>Screenshot Generator</h2>

    <p>
      Screenshot Generator takes automatic screenshots of posts when they are updated.<br />
      These are saved to <code>wp-content/screenshots</code>.
    </p>

    <?php if (scrgen_phantomjs() == false) { ?>
    <div style="background: #faa; color: #700; padding: 10px 15px; border: 1px solid #c00; margin-right: 20px;">
      The PhantomJS binary (<code>phantomjs</code>) could not be found. Screenshot Generator uses PhantomJS to generate screenshots.<br />
      Please see the <a href="https://github.com/lassebunk/screenshot-generator#installation" target="_blank">installation instructions</a> for information on how to fix this.
    </div>
    <?php } ?>
   
    <?php if (isset($_REQUEST['msg']) && $_REQUEST['msg'] == 'regenerating') { ?>
    <div style="background: #ada; color: #050; padding: 10px 15px; border: 1px solid #090; margin-right: 20px;">
      Screenshots are now being regenerated in the background, and will be available when done.<br >
      You are welcome to navigate away from this page.
    </div>
    <?php } ?>

    <?php
    settings_fields( 'scrgen-settings' );
    do_settings_sections( 'scrgen-settings' );
    submit_button();
    ?>
   
  </form>
  <?php

}