Schema.org Breadcrumbs for WordPress SEO
========================================

With this class the WordPress SEO breadcrumbs will use valid Schema.org markup.

This class will not have any function if the plugin WordPress SEO by Yoast is not activated on the current WordPress installation.
Furthermore breadcrumbs have to be enabled in the plugin settings.

However, adding this class won't break anything since all the changes are made using filters from the WordPress SEO plugin.
The filters used by the class are:
* 'wpseo_breadcrumb_single_link_wrapper'
* 'wpseo_breadcrumb_output_wrapper'
* 'wpseo_breadcrumb_single_link'
* 'wpseo_breadcrumb_output'

Usage of the class
==================

Simply include the class in your theme or plugin. You should then instantiate a global object like this:
```php
$schema_breadcrumbs = null;

function yourtheme_instantiate_class()
{
	global $schema_breadcrumbs;
	
	// only instantiate the class if Yoast breadcrumbs are used
	if( function_exists( 'yoast_breadcrumb' ) )
	{
		$schema_breadcrumbs = new Schema_Breadcrumbs();
	}
}
add_action( 'after_setup_theme', 'yourtheme_instantiate_class' );
```

Additional Information
======================

Read this short article to find out something more about this class:
* [How To Modify WP SEO Breadcrumbs for Schema.org](http://leaves-and-love.net/how-to-modify-wp-seo-breadcrumbs-for-schema-org/)

Check out the WordPress SEO plugin repository on GitHub:
* [Yoast/WordPress SEO](https://github.com/Yoast/wordpress-seo)

Find out more about Schema.org markup:
* [schema.org](http://schema.org/)

Join the discussion about using Schema.org markup in the WordPress SEO plugin:
* [Add schema.org markup](https://github.com/Yoast/wordpress-seo/issues/179)
