<?php
/**
 * This class modifies the breadcrumbs from the WordPress SEO plugin by Yoast to use Schema.org markup instead of RDFa.
 * Include and instantiate it in your theme or plugin like this:
 * <code>$schema_breadcrumbs = null;
 * 
 * function yourtheme_instantiate_class()
 * {
 * 		global $schema_breadcrumbs;
 *		if( function_exists( 'yoast_breadcrumb' ) )
 *		{
 *			$schema_breadcrumbs = new Schema_Breadcrumbs();
 *		}
 * }
 * add_action( 'after_setup_theme', 'yourtheme_instantiate_class' );</code>
 * 
 * The content is modified using the following plugin filters:
 * - 'wpseo_breadcrumb_single_link_wrapper'
 * - 'wpseo_breadcrumb_output_wrapper'
 * - 'wpseo_breadcrumb_single_link'
 * - 'wpseo_breadcrumb_output'
 * 
 * @link http://yoast.com/wordpress/seo/
 * @author Felix Arntz <felix-arntz@leaves-and-love.net>
 */
class Schema_Breadcrumbs
{
	private $breadcrumb_link_counter = 0;
	
	private $breadcrumb_element_wrapper = 'span';
	private $breadcrumb_output_wrapper = 'span';
	
	/**
	 * Constructor of the class
	 * 
	 * Adds the modifying functions to the four WordPress SEO plugin filters.
	 */
	public function __construct()
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
		if( $this->breadcrumb_link_counter > 0 )
		{
			$output .= '<' . $this->breadcrumb_element_wrapper . ' itemprop="child" itemscope="itemscope" itemtype="http://schema.org/Breadcrumb">';
		}
		
		if( isset( $link['url'] ) && substr_count( $link_output, 'rel="v:url"' ) > 0 )
		{
			$output .= '<a href="' . esc_attr( $link['url'] ) . '" itemprop="url"><span itemprop="title">' . $link['text'] . '</span></a>';
		}
		else
		{
			$opt = get_wpseo_options();
			if( isset( $opt['breadcrumbs-boldlast'] ) && $opt['breadcrumbs-boldlast'] )
			{
				$output .= '<strong class="breadcrumb_last" itemprop="title">' . $link['text'] . '</strong>';
			}
			else
			{
				$output .= '<span class="breadcrumb_last" itemprop="title">' . $link['text'] . '</span>';
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
		$full_output = str_replace( ' xmlns:v="http://rdf.data-vocabulary.org/#"', ' itemprop="breadcrumb" itemscope="itemscope" itemtype="http://schema.org/Breadcrumb"', $full_output );
		
		$end_offset = strlen( $this->breadcrumb_output_wrapper ) + 3;
		$offset = strlen( $full_output ) - $end_offset;
		
		$output = substr( $full_output, 0, $offset );
		
		for( $i = 0; $i < $this->breadcrumb_link_counter - 1; $i++ )
		{
			$output .= '</' . $this->breadcrumb_element_wrapper . '>';
		}
		
		$output .= substr( $full_output, $offset, $end_offset );
		
		return $output;
	}
}
?>