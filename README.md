Schema.org Breadcrumbs for WordPress SEO
========================================

[![endorse](https://coderwall-assets-0.s3.amazonaws.com/uploads/user/avatar/105962/IMG_4758quadrat.jpg)](https://coderwall.com/felixarntz)

With this class the WordPress SEO breadcrumbs will use valid Schema.org markup.

This class will not have any function if the plugin WordPress SEO by Yoast is not activated on the current WordPress installation.
Furthermore breadcrumbs have to be enabled in the plugin settings.

However, adding this class won't break anything since all the changes are made using filters from the WordPress SEO plugin.
The filters used by the class are:
* 'wpseo_breadcrumb_single_link'
* 'wpseo_breadcrumb_output'

Usage of the class
==================

As of version 1.2.0, the class uses a singleton pattern so that it can only be instantiated once. Simply include the class file in your theme or plugin. You can then enable it, for example, like this:
```php
function yourtheme_instantiate_class()
{
  // only instantiate the class if Yoast breadcrumbs are used
  if( function_exists( 'yoast_breadcrumb' ) )
  {
    Schema_Breadcrumbs::instance();
  }
}
add_action( 'after_setup_theme', 'yourtheme_instantiate_class' );
```
You do not need to do anything more than instantiating the class, it will then work by itself.

Additional Information
======================

Read this short article to find out something more about this class:
* [How To Modify WP SEO Breadcrumbs for Schema.org](http://leaves-and-love.net/how-to-modify-wp-seo-breadcrumbs-for-schema-org/)

Check out the WordPress SEO plugin repository on GitHub:
* [Yoast/WordPress SEO](https://github.com/Yoast/wordpress-seo)

Find out more about Schema.org markup:
* [schema.org](http://schema.org/)

This project is now on Packagist, require it with Composer:
* [Packagist Package](https://packagist.org/packages/felixarntz/schema-breadcrumbs-for-wordpress-seo)
