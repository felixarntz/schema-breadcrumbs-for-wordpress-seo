<?php
/*
 * Script Name:   Schema.org Breadcrumbs for WordPress SEO
 * Contributors:  Felix Arntz (@felixarntz / leaves-and-love.net)
 * Description:   This class modifies the WordPress SEO plugin by Yoast to use valid Schema.org markup for breadcrumbs instead of the RDFa.
 * Version:       1.3.0
 * License:       GNU General Public License
 * License URI:   http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 */
 
/**
 * This class modifies the breadcrumbs from the WordPress SEO plugin by Yoast to use Schema.org markup instead of RDFa.
 * It uses a singleton pattern so that it can only be instantiated once.
 * Simply include this file in your plugin or theme and enable the class, for example like this:
 * <code>function yourtheme_instantiate_class()
 * {
 *    if( function_exists( 'yoast_breadcrumb' ) )
 *    {
 *      Schema_Breadcrumbs::instance();
 *    }
 * }
 * add_action( 'after_setup_theme', 'yourtheme_instantiate_class' );</code>
 * 
 * The content is modified using the following plugin filters:
 * - 'wpseo_breadcrumb_single_link_wrapper'
 * - 'wpseo_breadcrumb_output_wrapper'
 * - 'wpseo_breadcrumb_single_link'
 * - 'wpseo_breadcrumb_output'
 * 
 * This class will not do anything if the WordPress SEO plugin is not installed:
 * http://yoast.com/wordpress/seo/
 * 
 * @package WPSEO_SchemaBreadcrumbs
 * @version 1.3.0
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 * 
 */
class Schema_Breadcrumbs
{
  private static $instance = null;

  private $breadcrumb_link_counter = 0;
  
  private $breadcrumb_element_wrapper = 'span';
  private $breadcrumb_output_wrapper = 'span';

  /**
   * Singleton Pattern
   * 
   * @return Schema_Breadcrumbs instance of the class
   */
  public static function instance()
  {
    if( self::$instance === null )
    {
      self::$instance = new self;
    }
    return self::$instance;
  }
  
  /**
   * Constructor of the class
   * 
   * Adds the modifying functions to the four WordPress SEO plugin filters.
   */
  private function __construct()
  {
    add_filter( 'wpseo_breadcrumb_single_link_wrapper', array( $this, 'breadcrumb_element_wrapper' ), 95 );
    add_filter( 'wpseo_breadcrumb_output_wrapper', array( $this, 'breadcrumb_output_wrapper' ), 95 );
    add_filter( 'wpseo_breadcrumb_single_link', array( $this, 'modify_breadcrumb_element' ), 10, 2 );
    add_filter( 'wpseo_breadcrumb_output', array( $this, 'modify_breadcrumb_output' ) );
  }
  
  /**
   * This function stores the element wrapper in a class variable so it can be used outside this method.
   * 
   * @param string $element an HTML tag like 'span', 'div' or similar
   * @return string the unmodified element wrapper
   */
  public function breadcrumb_element_wrapper( $element )
  {
    $this->breadcrumb_element_wrapper = $element;
    return $element;
  }
  
  /**
   * This function stores the output wrapper in a class variable so it can be used outside this method.
   * 
   * @param string $wrapper an HTML tag like 'span', 'div' or similar
   * @return string the unmodified output wrapper
   */
  public function breadcrumb_output_wrapper( $wrapper )
  {
    $this->breadcrumb_output_wrapper = $wrapper;
    return $wrapper;
  }
  
  /**
   * This function modifies the output for a single breadcrumb.
   * 
   * The default output is not modified, instead a completely new output is generated.
   * If the default link output contains a rel attribute with the value 'v:url' (which is added by WordPress SEO for every but the last link), URL and text are output.
   * Otherwise it is the current page for which only the text is printed out.
   * 
   * In this method the Schema.org markup for a single breadcrumb is added, and the link counter (class variable) is increased by 1 (for each link).
   * This number needs to be saved in the class variable $breadcrumb_link_counter since the Schema.org breadcrumbs have to be hierarchically nested.
   * For each link an HTML tag (specified by the element wrapper) is opened. The number of links has to be clear so all these tags will be closed later.
   * 
   * @param string $link_output the default output created by the WordPress SEO plugin
   * @param array $link an array containing data for the breadcrumb link (with fields 'url' and 'text')
   * @return string the output for a single breadcrumb link
   */
  public function modify_breadcrumb_element( $link_output, $link )
  {
    $output = '';
    
    if( isset( $link['url'] ) && substr_count( $link_output, 'rel="v:url"' ) > 0 )
    {
      $output .= '<a href="' . esc_attr( $link['url'] ) . '"><span itemprop="itemListElement">' . $link['text'] . '</span></a>';
    }
    else
    {
      $opt = array();
      if( class_exists( 'WPSEO_Options' ) ) // WPSEO >= 1.5
      {
        $opt = WPSEO_Options::get_all();
      }
      else // WPSEO < 1.5
      {
        $opt = get_wpseo_options();
      }
      if( isset( $opt['breadcrumbs-boldlast'] ) && $opt['breadcrumbs-boldlast'] )
      {
        $output .= '<strong class="breadcrumb_last" itemprop="itemListElement">' . $link['text'] . '</strong>';
      }
      else
      {
        $output .= '<span class="breadcrumb_last" itemprop="itemListElement">' . $link['text'] . '</span>';
      }
    }

    $this->breadcrumb_link_counter++;
    
    return $output;
  }
  
  /**
   * This function modifies the overall breadcrumbs output.
   * 
   * The default output is directly modified: The RDFa markup is replaced by equivalent Schema.org markup.
   * Furthermore for each breadcrumb, the HTML tag specified by the element wrapper is closed (which has been opened in the 'modify_breadcrumb_element' function).
   * 
   * To retrieve the number of breadcrumb links contained, the class variable $breadcrumb_link_counter is used.
   * 
   * @param string $full_output the default output created by the WordPress SEO plugin
   * @return string the overall breadcrumbs output
   */
  public function modify_breadcrumb_output( $full_output )
  {
    $string_to_replace = ' prefix="v: http://rdf.data-vocabulary.org/#"';
    if( version_compare( WPSEO_VERSION, '1.5.3.3', '<' ) )
    {
      $string_to_replace = ' xmlns:v="http://rdf.data-vocabulary.org/#"';
    }
    $output = str_replace( $string_to_replace, ' itemprop="breadcrumb" itemscope="itemscope" itemtype="http://schema.org/BreadcrumbList"', $full_output );
    
    return $output;
  }
}
