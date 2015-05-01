# Screenshot Generator

Screenshot Generator is a WordPress plugin that takes screenshots posts for social media etc. when they are updated.
It uses [PhantomJS](http://phantomjs.org/) to do this.

The screenshot will look something like this when shared on social media:

![Screenshot](https://raw.githubusercontent.com/lassebunk/screenshot-generator/master/screenshot-1.png)

## Installation

1. Download the plugin to `wp-content/plugins`.
2. Install PhantomJS.
   * **Mac:**

     ```bash
     brew install phantomjs
     ```

     Or if this fails, [download from here](https://github.com/eugene1g/phantomjs/releases)

   * **Linux:**

     ```bash
     $ apt-get install phantomjs fontconfig freetype*
     ```
3. Make sure the `phantomjs` binary is in your PHP's `PATH`.
4. Update a post.

## Usage

When you update a post, a screenshot is automatically taken in the background.
After a few seconds, you can retrieve the screenshot from the post's meta key
`_scrgen_screenshot`.

An `og:image` and `twitter:image:src` is automatically added to the meta tags
when viewing the post. These are only added if the post doesn't have a
thumbnail, in which case the plugin expects the thumbnail to be added by
you or another plugin.

## Credits

The screenshot functionality is adapted from Microweber's
[Screen](https://github.com/microweber/screen) code.

## Contributing

Contributions are very welcome as I'm not very experienced in developing
WordPress plugins. Please contribute!