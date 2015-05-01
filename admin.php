<?php
add_action( 'admin_menu', 'scrgen_add_admin_menu' );
add_action( 'admin_init', 'scrgen_settings_init' );


function scrgen_add_admin_menu(  ) { 

  add_options_page( 'Screenshot Generator', 'Screenshot Generator', 'manage_options', 'screenshot-generator', 'scrgen_options_page' );

}


function scrgen_settings_init(  ) { 

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
    'scrgen_enable_social', 
    __( 'Enable social screenshots', 'scrgen' ), 
    'scrgen_enable_social_render', 
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

}


function scrgen_width_render(  ) { 
  ?>
  <input type='number' name='scrgen_width' value='<?php echo scrgen_setting('width'); ?>'> px
  <?php
}


function scrgen_height_render(  ) { 
  ?>
  <input type='number' name='scrgen_height' value='<?php echo scrgen_setting('height'); ?>'> px
  <?php
}

function scrgen_enable_cropping_render(  ) { 
  ?>
  <label>
    <input type='checkbox' name='scrgen_enable_cropping' <?php checked(scrgen_setting('enable_cropping'), 1); ?> value='1'>
    Crop screenshots using the below settings.
  </label>
  <?php
}


function scrgen_crop_left_render(  ) { 
  ?>
  <input type='number' name='scrgen_crop_left' value='<?php echo scrgen_setting('crop_left'); ?>'> px
  <?php
}


function scrgen_crop_top_render(  ) { 
  ?>
  <input type='number' name='scrgen_crop_top' value='<?php echo scrgen_setting('crop_top'); ?>'> px
  <?php
}


function scrgen_crop_width_render(  ) { 
  ?>
  <input type='number' name='scrgen_crop_width' value='<?php echo scrgen_setting('crop_width'); ?>'> px
  <?php
}


function scrgen_crop_height_render(  ) { 
  ?>
  <input type='number' name='scrgen_crop_height' value='<?php echo scrgen_setting('crop_height'); ?>'> px
  <?php
}


function scrgen_enable_social_render(  ) { 
  ?>
  <label>
    <input type='radio' name='scrgen_social_strategy' <?php checked(scrgen_setting('social_strategy'), 'missing'); ?> value='missing'>
    Add screenshots to social media when no post thumbnail / featured image is available.
  </label>
  <br />
  <label>
    <input type='radio' name='scrgen_social_strategy' <?php checked(scrgen_setting('social_strategy'), 'always'); ?> value='always'>
    Always add screenshots to social media.
  </label>
  <br />
  <label>
    <input type='radio' name='scrgen_social_strategy' <?php checked(scrgen_setting('social_strategy'), ''); ?> value=''>
    Never add screenshots to social media.
  </label>
  <?php
}


function scrgen_social_section() {
  echo __( 'Screenshot Generator can add <code>og:image</code> and <code>twitter:image:src</code> meta tags if a post thumbnail doesn\'t exist.', 'scrgen' );
}


function scrgen_manual_section(  ) { 

  echo __( 'You can display screenshots manually inside posts by inserting: <pre>&lt;?php echo scrgen_screenshot(); ?&gt;</pre>', 'scrgen' );

}


function scrgen_options_page(  ) { 

  ?>
  <form action='options.php' method='post'>
    
    <h2>Screenshot Generator</h2>

    <p>
      Screenshot Generator takes automatic screenshots from posts when they are updated.<br />
      These are saved to <code>wp-content/screenshots</code>.
    </p>

    <?php if (empty(scrgen_phantomjs())) { ?>
    <div style="background: #faa; color: #700; padding: 10px 15px; border: 1px solid #c00; margin-right: 20px;">
      The PhantomJS binary (<code>phantomjs</code>) could not be found. Screenshot Generator uses PhantomJS to generate screenshots.<br />
      Please see the <a href="https://github.com/lassebunk/screenshot-generator#installation" target="_blank">installation instructions</a> for information on how to fix this.
    </div>
    <? } ?>
    
    <?php
    settings_fields( 'scrgen-settings' );
    do_settings_sections( 'scrgen-settings' );
    submit_button();
    ?>
    
  </form>
  <?php

}

?>