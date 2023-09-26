<?php
remove_filter('the_content', 'wptexturize');
require_once(dirname(__FILE__).'/inmo-map.php');
require_once(dirname(__FILE__).'/Connection.php'); 
require_once(dirname(__FILE__).'/inmo-search.php');
require_once(dirname(__FILE__).'/inmo-setting.php');
require_once(dirname(__FILE__).'/inmo-result.php');
require_once(dirname(__FILE__).'/inmo-search-list.php');
require_once(dirname(__FILE__).'/inmo-theme-setting.php');
require_once(dirname(__FILE__).'/inmo-theme-setting-db.php');
require_once(dirname(__FILE__).'/inmolink-contact-form.php');
require_once(dirname(__FILE__).'/legacy.php');
add_action('wp_head','inmolink_og_fix',0,1);
function inmolink_og_fix(){
    global $wp; global $post;
    $thisUrl = home_url( $wp->request ); 

    $refs = get_query_var('ref_no');
    if(empty($refs))
      return;

    if(!isset($post))
      return;

    if(stripos($post->post_content,'[inmolink_property') === false)
      return;

    $results = new InmoLinkResults();
  
    $results->fetch_properties(array('ref_no'=>$refs));
    
    if($results->count != 1)
      echo '<script type="text/javascript">window.location.href="'.site_url().'";</script>';

  
    $property = $results->results[0];

    $title = $property->location_id->name . ' ' . $property->type_id->name;
    $description = $property->desc;
    $images = $property->images;

    $active_plugins = get_option('active_plugins');

    if(in_array('wordpress-seo/wp-seo.php', apply_filters('active_plugins', $active_plugins))  OR in_array('seo-by-rank-math/rank-math.php', apply_filters('active_plugins', $active_plugins))){  
        add_filter('wpseo_canonical',function(){return false;});
        add_filter('wpseo_opengraph_title',function() use (&$title){return $title;});
        add_filter('wpseo_opengraph_desc',function() use (&$description){return $description;});
        add_filter('wpseo_opengraph_url',function() {return false;});
        add_action('wpseo_add_opengraph_images', function($object) use (&$images){ foreach ($images as $image) { $object->add_image( $image->src ) ; } } );

        
    }
    else{
      remove_action( 'wp_head', 'rel_canonical' );
      echo "\n". '<link rel="canonical" href="' . esc_url( $thisUrl, null, 'other' ) . '" />' . "\n";
      // echo 'else '.$thisUrl;
      echo '<meta property="og:url" content="'.$thisUrl.'"/>' . "\n";
      echo '<meta property="og:title" content="'.$title.'"/>' . "\n";
      echo '<meta property="og:description" content="'.wp_trim_words( $description, 400 ).'"/>' . "\n";
    }
  
  echo '<meta property="og:image" content="'.$images[0]->src.'"/>' . "\n";
  echo '<meta property="og:image:src" content="'.$images[0]->src.'"/>' . "\n";
  echo '<meta property="og:image:width" content="640"/>' . "\n";
  echo '<meta property="og:image:height" content="480"/>' . "\n";
  echo '<meta name="twitter:card" content="summary_large_image"/>' . "\n";
  echo "\n".'<meta name="twitter:image" content="http://109.228.15.38/webkit_image.php?src='.$images[0]->src.'" />';   
}
add_action('admin_enqueue_scripts', 'addCustomScripts');
function addCustomScripts(){
     wp_register_script('ajax_option', plugins_url('assets/js/options.js', __FILE__), array('jquery'));
     wp_localize_script('ajax_option', 'ajaxoption', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));
     wp_enqueue_style('Stylesheet', plugins_url( '/assets/css/adminCss.css', __FILE__ ));
       wp_enqueue_script('ajax_option');
    }
    add_action('wp_enqueue_scripts','addFontendScripts' );
	function addFontendScripts(){
		wp_register_script('jquery', plugins_url( '/assets/js/jquery.js', __FILE__ ), array('jquery'));
		wp_register_script('jquery-min', plugins_url( '/assets/js/jquery-ui.min.js', __FILE__ ), array('jquery'));
		wp_register_style('multiselect_css', plugins_url( '/assets/css/jquery.multiselect.css', __FILE__ ));  
		wp_register_script('multiselect', plugins_url( '/assets/js/jquery.multiselect.js', __FILE__ ), array('jquery'));
		wp_register_script('multiselect_filter', plugins_url( '/assets/js/jquery.multiselect.filter.js', __FILE__ ), array('jquery'));
		wp_register_script('lightbox_min', plugins_url( '/assets/js/bootstraplightbox.js', __FILE__ ), array('jquery'));
		wp_register_script('lightbox_script', plugins_url( '/assets/js/bootstraplightbox.min.js', __FILE__ ), array('jquery'));
		wp_enqueue_style('lightbox_css', plugins_url( '/assets/css/bootstraplightbox.css', __FILE__ ));
		wp_enqueue_style('second_slider', plugins_url( '/assets/css/custom_second_slider.css', __FILE__ ));
		wp_enqueue_style('inmolink', plugins_url( '/assets/css/inmolink.css', __FILE__ ));
    
    wp_enqueue_script( 'script1', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js', array('jquery'), '1.0' );
    wp_enqueue_script( 'script2', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js', array('jquery'), '4.0' );
    wp_enqueue_script( 'script3', 'https://cdnjs.cloudflare.com/ajax/libs/node-waves/0.7.5/waves.min.js', array('jquery'), '0.7' );
		wp_enqueue_style('cm-sheet', plugins_url( '/assets/css/compile.css', __FILE__ ));
		wp_enqueue_style('fa-stylesheet', plugins_url( '/assets/css/fa-icon.css', __FILE__ ));
		wp_enqueue_script('search', plugins_url( '/assets/js/Search.js', __FILE__ ), array('jquery'));
		wp_register_style( 'lightslider', plugins_url( '/assets/css/lightslider.css', __FILE__ ));
		wp_register_style( 'magnificPopup', plugins_url( '/assets/css/magnific-popup.css', __FILE__ ));
		wp_register_script( 'fontawsome_sript', plugins_url( '/assets/js/fontawsome.css', __FILE__ ));
		wp_register_script( 'lightslider_script', plugins_url( '/assets/js/lightslider.js', __FILE__ ), array('jquery'));
		wp_register_script( 'magnific', plugins_url( '/assets/js/jquery.magnific-popup.js', __FILE__ ), array('jquery'));
		wp_enqueue_script('customscript', plugins_url( '/assets/js/Scripnewt.js', __FILE__ ), array('jquery'));
		wp_enqueue_script('inmolink-multilevel-locationfield', plugins_url( '/assets/js/inmolink-multilevel-locationfield.js', __FILE__ )); 
		wp_register_script('ajax_script', plugins_url('assets/js/Script.js', __FILE__), array('jquery'));
		wp_localize_script('ajax_script', 'ajaxScript', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))); 
		wp_localize_script( 'inmolink-multilevel-locationfield', 'inmolink_multilevel_location',array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
		wp_enqueue_script( 'validate_min', plugins_url( '/assets/js/jquery.validate.min.js', __FILE__ ), array() );
		wp_register_script( 'jscookie', plugins_url( '/assets/js/js.cookie.js', __FILE__ ), array() );
		wp_localize_script( 'search', 'objectL10n', array(
		'noneSelectedText' => esc_html__( "Select an Option", 'inmolink' ),
		'checkAllText' => esc_html__( "Check All", 'inmolink-extras' ),
		'uncheckAllText' => esc_html__( "Uncheck All", 'inmolink-extras' ),
		'selectedText' => esc_html__( "#selected", 'inmolink-extras' ),
	) );
	wp_enqueue_script('lightbox_script');
	wp_enqueue_script('lightbox_min'); 
	wp_enqueue_script( 'lightslider_script');
	wp_enqueue_style( 'lightslider');
	wp_enqueue_style( 'magnificPopup');
	wp_enqueue_script( 'jscookie');
	wp_enqueue_script('jquery');
	wp_enqueue_script('magnific');
	wp_enqueue_script('jquery-min');
	wp_enqueue_script('multiselect');
	wp_enqueue_script('multiselect_filter');
	wp_enqueue_script('owlcarousel');
	wp_enqueue_style('multiselect_css');
	wp_enqueue_style('owlcarouselcss');
	wp_enqueue_style('owlcarousel_css');
	wp_enqueue_style('font-awesome');
	wp_enqueue_script('ajax_script');
	wp_enqueue_script('inmolink-multilevel-locationfield');	
  
	}

add_action('wp_enqueue_scripts', 'property_calendar_assets', 99);
function property_calendar_assets(){
  wp_enqueue_style( 'datepicker_css', 'https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css' );
  wp_enqueue_script( 'jquery_datepicker', 'https://code.jquery.com/ui/1.13.1/jquery-ui.js', array(), '1.0.10', true );
  wp_enqueue_style('my_property_datepicker', plugins_url( '/assets/css/my_property_datepicker.css', __FILE__ ));
}

if( is_admin() ) {
 $settings = new InmolinkSettings();
}

add_action( 'wp_ajax_get_multilevel_location', array('InmoLinkSearch','ajax') );
add_action( 'wp_ajax_nopriv_get_multilevel_location', array('InmoLinkSearch','ajax') );
add_shortcode( 'ro_polylang', 'ro_polylang_func' );
add_shortcode('inmolink_property_results',array('InmoLinkResults','properties_shortcode'));
add_shortcode('inmolink_properties',array('InmoLinkResults','properties_shortcode'));
add_shortcode('inmolink_introtext',array('InmoLinkResults','introtext_shortcode'));
add_shortcode('inmolink_noresults',array('InmoLinkResults','noresults_shortcode'));
add_shortcode( '2020marbella', 'property_listing_func_1' );
add_shortcode( 'ro_favourite','ro_favourite_func' );
add_shortcode( 'ro_prop_title', 'ro_prop_title_func' );
add_action('init','inmolink_rewrite_rule', 10, 0);
add_shortcode( 'order_form', 'order_form' );
add_shortcode('inmolink_property_search_form', array('InmoLinkSearch','form_shortcode'));
add_shortcode('inmolink_property',array('InmoLinkResults','property_shortcode'));
add_shortcode('add_shortlist', array('InmoLinkResults','shortlist_fav'));
add_shortcode('inmolink_shortlist_button',array('InmoLinkResults','shortlist_button'));
add_shortcode( 'horizontal_search', 'property_search_func_1' );
add_shortcode( 'fav_back', array('InmoLinkResults','fav_back_func' ));
add_shortcode( 'property_highlights', 'property_higlights_func_1' );
add_shortcode("inmolink_property_contact_form", array('InmoLinkContact','contactform_shortcode'));
add_shortcode("inmolink_property_contact_field", array('InmoLinkContact','contactform_field'));
add_shortcode( '2020sotogrande', 'property_listing_func_rm' );
add_shortcode("inmolink_property_map", array('InmoLinkMap','inmolink_property_map_list'));
add_shortcode( '2020marbella', 'property_listing_func_1' );
add_shortcode( '2020cordoba', 'property_listing_func_2' );
add_shortcode( '2020malaga', 'property_listing_func_3' );
add_shortcode( '2020barcelona', 'property_listing_func_4' );
add_shortcode( '2020valencia', 'property_listing_func_5' );
add_shortcode( '2020madrid', 'property_listing_func_6' );
add_shortcode( '2020extra', 'property_listing_func_7' );
add_shortcode( '2020elviria', 'property_listing_func_8' );
add_shortcode( '2020agentpro', 'property_listing_func_9' );
add_shortcode( '2020quintestate', 'property_listing_func_10' );
add_shortcode( '2020estepona', 'property_listing_func_11' );
add_shortcode( '2020costablanca', 'property_listing_func_12' );
add_shortcode( '2020puertobanus', 'property_listing_func_13' );
add_shortcode( '2020sotogrande', 'property_listing_func_14' );
add_shortcode( '2020almeria', 'property_listing_func_15' );
add_shortcode( '2020cdsproperty', 'property_listing_func_16' );
add_shortcode( '2020alicante', 'property_listing_func_17' );
add_shortcode( '2020ibiza', 'property_listing_func_18' );
add_shortcode( '2020homepro', 'property_listing_func_19' );
add_shortcode( '2020inmolink', 'property_listing_func_20' );
add_shortcode( '2020manilva', 'property_listing_func_21' );
add_shortcode( 'footer_prop', 'property_listing_func_22' );
add_shortcode( 'ft_prop_style1', 'property_listing_func_23' );
add_shortcode( 'ft_prop_style2', 'property_listing_func_24' );
add_shortcode( 'ft_prop_style3', 'property_listing_func_25' );
add_shortcode( 'property_slider_style01', 'property_listing_func_26' );
add_shortcode( 'property_slider_style02', 'property_listing_func_27' );
add_shortcode( 'property_slider_style03', 'property_listing_func_28' );
add_shortcode( 'horizontal_search', 'property_search_func_1' );
add_shortcode( 'vertical_search', 'property_search_func_2' );
add_shortcode( 'tab_search', 'property_search_func_3' );
add_shortcode( 'mobile_search', 'property_search_func_4' );
add_shortcode( 'agentpro_search', 'property_search_func_5' );
add_shortcode( 'valencia_search', 'property_search_func_6' );
add_shortcode( 'one_line_search', 'property_search_func_7' );
add_shortcode( 'vertical_expand_search', 'property_search_func_8' );
add_shortcode( 'homepro_search', 'property_search_func_9' );
add_shortcode( 'inmolink_search', 'property_search_func_10' );
add_shortcode( 'inmolink_search_filter', 'property_search_func_11' );

// register post type
add_action( 'init','inmolink_listing');
 function inmolink_listing(){ 
        register_post_type( 'inmolink',
         array(
        'labels' => array(
          'name' => 'Inmolink Listing',
        ),
        'public' => true,
        'menu_icon' => 'dashicons-location-alt',
        'capability_type' => 'post',
        'capabilities' => array(
          'create_posts' => false, 
          'edit_posts' => false,
        ),
        'map_meta_cap' => false,
        'taxonomies' => array(''),
        'has_archive' => true
      )
     );

  }
 
  
  add_action( 'init', 'inmolink_create_types_custom_taxonomy');
  function inmolink_create_types_custom_taxonomy() {
  $labels = array(
    'name' => _x( 'Types', 'taxonomy general name' ),
    'parent_item' => __( 'Parent Type' ),
    'parent_item_colon' => __( 'Parent Type:' ),
    'edit_item' => __( 'Edit Type' ),
    'add_new_item' => __( 'Add New Type' ),
    'menu_name' => __( 'Types' ),
  );
  register_taxonomy('types',array('inmolink','page'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'rewrite' => array( 'slug' => 'types' ),
  ));

}
add_action( 'init','inmolink_listingLocation_custom_taxonomy');
function inmolink_listingLocation_custom_taxonomy() {
  $labels = array(
    'name' => _x( 'Locations', 'taxonomy general name' ),
    'parent_item' => __( 'Parent Location' ),
    'parent_item_colon' => __( 'Parent Location:' ),
    'edit_item' => __( 'Edit Location' ),
    'add_new_item' => __( 'Add New Location' ),
    'menu_name' => __( 'Locations' ),
  );
  register_taxonomy('locations',array('inmolink','page'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'rewrite' => array( 'slug' => 'locations' ),
  ));

}
add_action( 'init','inmolink_listingFeature_custom_taxonomy');
function inmolink_listingFeature_custom_taxonomy() {
  $labels = array(
    'name' => _x( 'Features', 'taxonomy general name' ),
    'parent_item' => __( 'Parent Feature' ),
    'parent_item_colon' => __( 'Parent Feature:' ),
    'edit_item' => __( 'Edit Feature' ),
    'add_new_item' => __( 'Add New Feature' ),
    'menu_name' => __( 'Features' ),
  );
  register_taxonomy('features',array('inmolink','page'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'rewrite' => array( 'slug' => 'features' ),
  ));

}
/*
* Activation Hook
*/
function inmolink_plugin_flush_rewrites() {
  inmolink_listing();
  flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'inmolink_plugin_flush_rewrites' );

/*
* Deactivation Hook
*/
function inmolink_plugin_uninstall() {
  unregister_post_type( 'inmolink' );
}
register_uninstall_hook( __FILE__, 'inmolink_plugin_uninstall' );
// upload API data to post taxtonomy
//create function for add meta field
function save_locations_meta( $term_id, $type ){
  if( isset( $_POST['location_type'] )) {
    $type = sanitize_title( $_POST['location_type'] );
    add_term_meta( $term_id, 'location_type', $type, true );
  }
}
//create function for edit meta field
function edit_locations_field( $term, $taxonomy ){
  $location_type = get_term_meta( $term->term_id, 'location_type', true ); ?>
  <tr class="form-field term-group-wrap">
    <th scope="row">
      <label for="type-location"><?php _e( 'Type', 'Inmolink Listing' ); ?></label>
    </th>
    <td>
      <input type="text" class="postform" id="type-location" name="location_type" value="<?php echo $location_type; ?>">
    </td>
  </tr>
  <?php
}
add_action( 'edited_locations','update_locations_meta' , 10, 2);
function update_locations_meta( $term_id, $tt_id ){
  if( isset( $_POST['location_type'] )){
    $group = sanitize_title( $_POST['location_type'] );
    update_term_meta( $term_id, 'location_type', $group );
  }
}
add_filter('manage_edit-locations_columns','add_locations_column' );
function add_locations_column( $columns ){
  $columns['location_type'] = __( 'Type' );
  $columns['location_remote_id'] = __( 'Remote Id' );
  return $columns;
}
 add_action('manage_locations_custom_column', 'add_locations_column_content', 10, 3);
function add_locations_column_content( $content, $column_name, $term_id ){
  $term_id = absint( $term_id );
  if( $column_name == 'location_type' ){    
      $location_type = get_term_meta( $term_id, 'location_type', true );
      if( !empty( $location_type ) ){
        $content .= esc_attr( $location_type );
      }
  }
  if( $column_name == 'location_remote_id' ){
      $location_remote_id = get_term_meta( $term_id, 'location_id', true );
      if( !empty( $location_remote_id ) ){
        $content .= esc_attr( $location_remote_id );
      }
  }
  return $content;
}
//...................
add_action( 'init', 'inmolink_get_languages' );
function inmolink_get_languages()
{
    global $polylang;
    $return  = array();
    $settings = get_option('inmolink_option_name');
	
	$base_url = Connection::get_list_slug();
   if (isset($polylang))
    {
      $hide_default = $polylang->links_model->model->options['hide_default'];
      $default_lang = $polylang->links_model->model->options['default_lang'];
      $languages = $polylang->model->get_languages_list();
      foreach($languages as $language){
          $slug = (string)$language->slug;
		  $locale = (string)$language->locale;
          if($hide_default && $slug == $default_lang)
          {
            $dir = '';
            $page = $base_url;
          }
          else
          {
			//$string = substr($base_url, 0, strpos($base_url, "-"));
			$dir = $slug.'/';
            $page = $base_url.'-'.$slug;
          }

          $return[$slug] = array(
              'locale' => $locale,
              'dir' => $dir,
              'detail_slug' => isset($settings['detail_slug'][$slug]) ? $settings['detail_slug'][$slug] : $page
          );
      }
    }
    else
    {
        
        $locale = get_option('WPLANG',get_locale());
        list($slug,)=explode('_',$locale,2);
   $page = $base_url;
        $return[$slug]  = array(
           
            'locale' => $locale,
              'dir' => $dir,
              'detail_slug' => isset($settings['detail_slug'][$slug]) ? $settings['detail_slug'][$slug] : $page
        );
    }
	
	return $return;

}
add_filter( 'wp_dropdown_cats', 'wp_dropdown_cats_multiple', 10, 2 );

function wp_dropdown_cats_multiple( $output, $r ) {

    if( isset( $r['multiple'] ) && $r['multiple'] ) {

         $output = preg_replace( '/^<select/i', '<select multiple', $output );

        $output = str_replace( "name='{$r['name']}'", "name='{$r['name']}[]'", $output );

        foreach ( array_map( 'trim', explode( ",", $r['selected'] ) ) as $value )
            $output = str_replace( "value=\"{$value}\"", "value=\"{$value}\" selected", $output );

    }

    return $output;
}
function inmolink_get_template($template_name, $args = array(), $tempate_path = '', $default_path = '' )
{
    if(!is_array($args))
        return;

    extract($args);

    $template_file = inmolink_locate_template( $template_name, $tempate_path, $default_path );
	
    if ( ! file_exists( $template_file ) ) {
        _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
        return;
    }
	include $template_file;
}
function property_listing_func_1( $atts ) {ob_start(); require('templates/property/2020marbella.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_2( $atts ) {ob_start(); require('templates/property/2020cordoba.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_3( $atts ) {ob_start(); require('templates/property/2020malaga.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_4( $atts ) {ob_start(); require('templates/property/2020barcelona.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_5( $atts ) {ob_start(); require('templates/property/2020valencia.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_6( $atts ) {ob_start(); require('templates/property/2020madrid.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_7( $atts ) {ob_start(); require('templates/property/2020extra.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_8( $atts ) {ob_start(); require('templates/property/2020elviria.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_9( $atts ) {ob_start(); require('templates/property/2020agentpro.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_10( $atts ) {ob_start(); require('templates/property/2020quintestate.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_11( $atts ) {ob_start(); require('templates/property/2020estepona.php'); $return_string = ob_get_clean();  return $return_string;} 
function property_listing_func_12( $atts ) {ob_start(); require('templates/property/2020costablanca.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_13( $atts ) {ob_start(); require('templates/property/2020puertobanus.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_14( $atts ) {ob_start(); require('templates/property/2020sotogrande.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_15( $atts ) {ob_start(); require('templates/property/2020almeria.php'); $return_string = ob_get_clean();  return $return_string;} 
function property_listing_func_16( $atts ) {ob_start(); require('templates/property/2020cdsproperty.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_17( $atts ) {ob_start(); require('templates/property/2020alicante.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_18( $atts ) {ob_start(); require('templates/property/2020ibiza.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_19( $atts ) {ob_start(); require('templates/property/2020homepro.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_20( $atts ) {ob_start(); require('templates/property/2020inmolink.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_21( $atts ) {ob_start(); require('templates/property/2020manilva.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_22( $atts ) {ob_start(); require('templates/property/footer_prop.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_23( $atts ) {ob_start(); require('templates/property/ft_prop_style1.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_24( $atts ) {ob_start(); require('templates/property/ft_prop_style2.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_25( $atts ) {ob_start(); require('templates/property/ft_prop_style3.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_26( $atts ) {ob_start(); require('templates/property/property_slider_style01.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_27( $atts ) {ob_start(); require('templates/property/property_slider_style02.php'); $return_string = ob_get_clean();  return $return_string;}
function property_listing_func_28( $atts ) {ob_start(); require('templates/property/property_slider_style03.php'); $return_string = ob_get_clean();  return $return_string;}
function property_search_func_1( $atts ) {ob_start(); require('templates/search/horizontal_search.php'); $return_string = ob_get_clean();  return $return_string;}
function property_search_func_2( $atts ) {ob_start(); require('templates/search/vertical_search.php'); $return_string = ob_get_clean();  return $return_string;}
function property_search_func_3( $atts ) {	if(isset($atts['toplocation']))

		$str_location_atts = ' parent="'.$atts['toplocation'].'" ';

	else

		$str_location_atts = ''; 
ob_start(); require('templates/search/tab_search.php'); $return_string = ob_get_clean();  return $return_string;}
function property_search_func_4( $atts ) {ob_start(); require('templates/search/mobile_search.php'); $return_string = ob_get_clean();  return $return_string;}
function property_search_func_5( $atts ) {ob_start(); require('templates/search/agentpro_search.php'); $return_string = ob_get_clean();  return $return_string;}
function property_search_func_6( $atts ) {ob_start(); require('templates/search/valencia_search.php'); $return_string = ob_get_clean();  return $return_string;} 
function property_search_func_7( $atts ) {ob_start(); require('templates/search/one_line_search.php'); $return_string = ob_get_clean();  return $return_string;} 
function property_search_func_8( $atts ) {ob_start(); require('templates/search/vertical_expand_search.php'); $return_string = ob_get_clean();  return $return_string;}
function property_search_func_9( $atts ) {ob_start(); require('templates/search/homepro_search.php'); $return_string = ob_get_clean();  return $return_string;}
function property_search_func_10( $atts ) {ob_start(); require('templates/search/inmolink_search.php'); $return_string = ob_get_clean();  return $return_string;}
function property_search_func_11( $atts ) {ob_start(); require('templates/search/inmolink_search_filter.php'); $return_string = ob_get_clean();  return $return_string;}
function order_form() {	
	ob_start(); require('templates/search/order_form.php'); $return_string = ob_get_clean();  return $return_string;
}
function property_higlights_func_1() {
  ob_start(); require('templates/highlights.php'); $return_string = ob_get_clean();  return $return_string;
} 

function ro_prop_title_func(){
	if (function_exists('pll_current_language')){
$lang =pll_current_language();}else {$lang = 'en'; }
if(!empty($lang)){$langs = $lang;}else{$langs = 'en';}

   if($langs == 'en')  { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'es') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'fr') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'de') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'nl') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'ru') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'no') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'sv') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'da') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'pl') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'nn') { return ''.do_shortcode('[property_field field="name"]').''; }
   else if($langs == 'fi') { return ''.do_shortcode('[property_field field="name"]').''; }
} 
function ro_favourite_func(){
	global $wpdb;
	if (function_exists('pll_current_language')){
    $lang =pll_current_language();}else {$lang = 'en'; }
    if(!empty($lang)){$langs = $lang;}else{$langs = 'en';}
	$tablename = $wpdb->prefix.'Inmolink_theme_setting';
	$result	=	$wpdb->get_results("SELECT * FROM $tablename");
    if( $result){
        foreach($result as $results){
            $lang_slug = $results->lang_slug;
            $button_url = $results->button_url;
            $button_text_html = $results->button_text;
           if ($langs == $lang_slug ) {
		   $output = '<a href="'. $button_url .'" class="fav"><i class="fa fa-heart"></i><sup>Fav <span class="shortlist_counter">0</span></sup></a>';}
		}
	
    return $output;
    }
	}
function inmolink_locate_template( $template_name, $template_path = '', $default_path = '' ) {
  
  if ( ! $template_path ) :
    $template_path = 'inmolink/';
  endif; 
 if ( ! $default_path ) :
      $default_path = plugin_dir_path( __FILE__ ) . 'templates/'; // Path to the template folder
    endif;
    $template = locate_template( array(
      $template_path . $template_name,
      $template_name
    ) );
  if ( ! $template ) :
    $template = $default_path . $template_name;
  endif;
  return apply_filters( 'inmolink_locate_template', $template, $template_name, $template_path, $default_path );
}
function inmolink_rewrite_rule()
{
  add_rewrite_tag('%ref_no%', '([^&]+)');
   if (function_exists('pll_current_language')){
    $langs =pll_current_language();}else {$langs = 'en'; }
    $inmolink_options = get_option('inmolink_option_name');
    if($langs == 'en'){
    $slug=$inmolink_options['detail_slug'];}
    else{$slug=$inmolink_options['detail_slug'].'-'.$langs;}
    $languages = inmolink_get_languages();
	 global $wpdb;
     $tablename = $wpdb->prefix.'Inmolink_property_listing';
     $result =   $wpdb->get_results("SELECT * FROM $tablename");
     foreach($result as $row){
    $lang_slug = $row->lang_slug;
    $lang_page = $row->detail_page_url;
	$a =substr($lang_page, strrpos($lang_page, '/') + 1);
	//add_rewrite_rule('^'.$langs.$slug.'/([^_]+)','index.php?pagename='.$slug.'&ref_no=$matches[1]','top');
	add_rewrite_rule('^'.$lang_page.'/([^_]+)','index.php?pagename='.$a.'&ref_no=$matches[1]','top');
  }
 
 
}

//add_action( 'init','inmolink_listing_property_features_data');
function inmolink_listing_property_features_data() {
  global $polylang;
  $languages =inmolink_get_languages();
	
  foreach($languages as $ln => $args)
  {
   $locale = $args['locale'];
    $data = array(
        'ln' => $locale,
        'limit' => 1000
    ); 
	$fetchFeature  = Connection::get_feature();
	$url= $fetchFeature.'?ln='.$data['ln'];
    $response = Connection::executeCurlRequest($url);
    $result = json_decode($response, true);
	
    foreach ($result['data'] as $feature)
    {
      $taxonomy = 'features';
      $name = $feature['name'];
      $meta_key = 'category_id';
      $meta_value = $feature['id'];
      $args = array(
          'taxonomy' => $taxonomy,
          'hide_empty' => false,
          'meta_key' => $meta_key,
          'meta_value' => $meta_value,
		   'lang' => $ln
      );
      $terms = get_terms( $args );
      if(empty($terms))
      {
        $args['slug'] = sanitize_title($name . '_' . $ln);
        $term = wp_insert_term(
            $name,
            $taxonomy,
            $args
        );
        if(!is_wp_error($term)){
          $term_id = $term['term_id'];
          update_term_meta($term_id, $meta_key, $meta_value);
     
      if(isset($polylang))
            $polylang->model->term->set_language($term_id, $ln);
    
           $parent_id = $term_id;
          $parent_slug = sanitize_title($name);
        } else {
          $parent_id = 0;
        }
      }
      else
      {
        $parent_id = $terms[0]->term_id;
        $parent_slug = sanitize_title($terms[0]->name);
      }
	 
    if($parent_id){
        foreach($feature['value_ids'] as $features)
        {
			
          $name = $features['name'];
          $meta_key = 'feature_id';
          $meta_value = $features['id'];
          $args = array(
              'taxonomy' => $taxonomy,
              'hide_empty' => false,
              'meta_key' => $meta_key,
              'meta_value' => $meta_value,
              'lang' => $ln
          );
		  
          $terms = get_terms( $args );
		  
          if(empty($terms))
          {
              $args['parent'] = $parent_id;
              $args['slug'] = sanitize_title($parent_slug.'_'.$name . '_' . $ln);
              $term = wp_insert_term(
                  $name,
                  $taxonomy,
                  $args
              );
              if(!is_wp_error($term)){
                  $term_id = $term['term_id'];
                  update_term_meta($term_id, $meta_key, $meta_value);
                  if(isset($polylang))
                      $polylang->model->term->set_language($term_id, $ln);
              }
          }
        }
      }
    }
  }
} 
//add_action( 'init','inmolink_listing_location_data');
function inmolink_listing_location_data(){
 $fetchLocation = Connection::get_location();
 $response = Connection::executeCurlRequest($fetchLocation);
 $result = json_decode($response, true);
 foreach($result['data'] as $value){
    $api_id = $value['id'];
    $api_name = $value['name'];
    $api_type = $value['type'];
    $api_parent_id = $value['parent_id'];
    $terms = get_terms(array(
        'taxonomy' => 'locations',
        'hide_empty' => false, 
        'meta_query' => array(
            array(
                'key' => 'location_id',
                'value' => $api_id,
                'compare' => '='
            )
        )
    ));

    if(!empty($terms))
    {
        continue;
    }
    $args = array(
    'taxonomy' => 'locations',
    'hide_empty' => false, 
    'meta_query' => array(
      array(
         'key' => 'location_id',
         'value' => $api_parent_id,
         'compare' => '='
        )
      )
    );
    $terms = get_terms($args);
    if (!empty($terms)){

      $parent_term_id = $terms[0]->term_id;
      $location = wp_insert_term(
        $api_name,  
        'locations', 
        array(
          'description' => '',
          'parent' => $parent_term_id,
          'slug' => $api_name,
        )
      );
      $lastid = $location['term_id'];
      update_term_meta($lastid, 'location_type', $api_type);
      update_term_meta($lastid, 'location_id', $api_id);
    } else {
      $location = wp_insert_term(
        $api_name,   
        'locations',
        array(
          'description' => '',
          'parent' => 0,
          'slug' => $api_name,
        )
      );

      if(is_wp_error($location))
      {
        wp_die("There was an error importing $api_name");
        break;
      }

      $lastid = $location['term_id'];
      update_term_meta($lastid, 'location_type', $api_type);
      update_term_meta($lastid, 'location_id', $api_id);
    }
        }
}
//add_action( 'init','inmolink_listing_property_types_data');
function inmolink_listing_property_types_data(){
     global $polylang;
     $languages =inmolink_get_languages();
   foreach($languages as $ln => $args)
    {
        $locale = $args['locale'];
        $data = array(
            'ln' => $locale,
            'limit' => 1000
        );
        $fetchType  = Connection::get_type();
	    $url= $fetchType.'&ln='.$data['ln'];
        $response = Connection::executeCurlRequest($url);
        $response = json_decode($response, true);
        foreach ($response['data'] as $type)
        {
            $taxonomy = 'types';
            $name = $type['name'];
            $meta_key = 'type_id';
            $meta_value = $type['id'];
            $remote_parent_id = $type['parent_id'];
            $local_parent_id = 0;

            if($remote_parent_id){
                $args = array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                    'meta_key' => $meta_key,
                    'meta_value' => $remote_parent_id,
                    'lang' => $ln
                );

                $terms = get_terms( $args );
                if(!empty($terms)){
                    $local_parent_id = $terms[0]->term_id;
                }
            }

            $args = array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value,
                'lang' => $ln
            );

            $terms = get_terms( $args );

            $args['parent'] = $local_parent_id;

            if(empty($terms))
            {
                $args['slug'] = sanitize_title($name . '_' . $ln);
                $term = wp_insert_term(
                    $name, 
                    $taxonomy, 
                    $args
                );
                if(!is_wp_error($term)){
                    $term_id = $term['term_id'];
                    update_term_meta($term_id, $meta_key, $meta_value);
                    if(isset($polylang))
                        $polylang->model->term->set_language($term_id, $ln);
                }
            }
            
        }
    }
 }
 add_action("wp_ajax_Getid",  'Getid');
 add_action("wp_ajax_nopriv_Getid", 'Getid');
function Getid(){
  $id=$_POST['id'];
  echo $id;
} 
function ro_polylang_func(){
	ob_start();
	if (function_exists('pll_current_language')){
  pll_the_languages(array('show_flags'=>1,'show_names'=>0));
  $flags = ob_get_clean();
  return $flags;
  print_r($flags);
	}
} 

add_action("wp_ajax_inmolink_propertydetail_datapost", "inmolink_propertydetail_formdata");
add_action("wp_ajax_nopriv_inmolink_propertydetail_datapost", "inmolink_propertydetail_formdata");

function inmolink_propertydetail_formdata() {

    parse_str($_POST['formData'], $formData);//This will convert the string to array

    $inmolinkData = inmolink_fetch_properties('POST', 'v1/contact' , $formData);

    if($inmolinkData->data->contact_ref != 'None')
    {
        echo '<span style="color:green">'.__('Your message was sent successfully!','inmolink').'</span>';
    }
    else
    {
        echo '<span style="color:red">'.__('En error ocurred, please try again later.','inmolink').'</span>';
    }
    wp_die();
}
add_shortcode("only_on_first_page", function($atts = array(), $content=NULL){
	if(!isset($_GET['il_page']) || $_GET['il_page'] == "1")
		return do_shortcode($content);
});
add_filter('pll_translation_url', 'inmolink_pll_translation_urls', 10, 2);
function inmolink_pll_translation_urls($url, $ln) {
  $refs = get_query_var('ref_no');
  if(empty($refs))
    return $url;
  
  $url .= strpos($url,'?') === false ? '?' : '&';
  $url .= 'ref_no=' . $refs;

  return $url;
}
/*
Inmolink Currency Converter
*/

add_action( 'wp_ajax_exchange_rate', 'exchange_rate_ajax_callback' );
add_action( 'wp_ajax_nopriv_exchange_rate', 'exchange_rate_ajax_callback' );

function exchange_rate_ajax_callback() {
    echo ro_get_exchange_rate($_POST['from'],$_POST['to']);
    wp_die();
}

add_action( 'wp_ajax_convert_currency', 'convert_currency_ajax_callback' );
add_action( 'wp_ajax_nopriv_convert_currency', 'convert_currency_ajax_callback' );

function convert_currency_ajax_callback()
{
	echo ro_convert_currency($_POST['price'],$_POST['from'],$_POST['to']);
	wp_die();
}

function ro_get_exchange_rate($from='eur', $to='usd'){
    $key = '249dd5cfdf55e001a2c8568ad1cd9cb0';
    $to = strtoupper($to);
    $from = strtoupper($from);
    $transient_name = 'conversion_rate_'.$from.'_'.$to;

    if ( false === ( $currencyValue = get_transient( $transient_name ) ) ) {
        if ( $from == $to ) {
            $currencyValue = 1;
        } else {
            $url = 'http://data.fixer.io/latest?symbols='.( $to ).'&base='.( $from ).'&access_key='.( $key );
            $response = wp_remote_get( $url, array ( 'sslverify' => false ) );
            $json = json_decode($response['body']);
            $currencyValue = $json->rates->{$to};
            set_transient( $transient_name, $currencyValue, 24 * 3600 );
        }
    }
    return $currencyValue;
}

function ro_convert_currency( $amount=10, $from='eur', $to='usd' ){
    $rate = ro_get_exchange_rate($from,$to);
    $return = $amount * $rate;
    $return = number_format((string)$return, 0 , '.',',') . ' ' .strtoupper($to);
    return $return;
}

add_shortcode('currency_converter','shortcode_currency_converter');
function shortcode_currency_converter($atts){
	$defaults = array(
		'from'    =>  'eur',
		'decimal'   =>  '.',
		'thousands' =>  ',',
		'precision' =>  0,
		'separator' =>  ' - ',
		'currencies' => 'eur,gbp,sek,nok,dkk,chf,usd,rub',
	);

	$atts = shortcode_atts($defaults, $atts);

	$all_currencies = array(
		'aed' => __( 'AED', 'currency' ),
		'ang' => __( 'ANG', 'currency' ),
		'ars' => __( 'ARS', 'currency' ),
		'aud' => __( 'AUD', 'currency' ),
		'bdt' => __( 'BDT', 'currency' ),
		'bgn' => __( 'BGN', 'currency' ),
		'bhd' => __( 'BHD', 'currency' ),
		'bnd' => __( 'BND', 'currency' ),
		'bob' => __( 'BOB', 'currency' ),
		'brl' => __( 'BRL', 'currency' ),
		'bwp' => __( 'BWP', 'currency' ),
		'cad' => __( 'CAD', 'currency' ),
		'chf' => __( 'CHF', 'currency' ),
		'clp' => __( 'CLP', 'currency' ),
		'cny' => __( 'CNY', 'currency' ),
		'cop' => __( 'COP', 'currency' ),
		'crc' => __( 'CRC', 'currency' ),
		'czk' => __( 'CZK', 'currency' ),
		'dkk' => __( 'DKK', 'currency' ),
		'dop' => __( 'DOP', 'currency' ),
		'dzd' => __( 'DZD', 'currency' ),
		'eek' => __( 'EEK', 'currency' ),
		'egp' => __( 'EGP', 'currency' ),
		'eur' => __( 'EUR', 'currency' ),
		'fjd' => __( 'FJD', 'currency' ),
		'gbp' => __( 'GBP', 'currency' ),
		'hkd' => __( 'HKD', 'currency' ),
		'hnl' => __( 'HNL', 'currency' ),
		'hrk' => __( 'HRK', 'currency' ),
		'huf' => __( 'HUF', 'currency' ),
		'idr' => __( 'IDR', 'currency' ),
		'ils' => __( 'ILS', 'currency' ),
		'inr' => __( 'INR', 'currency' ),
		'jmd' => __( 'JMD', 'currency' ),
		'jod' => __( 'JOD', 'currency' ),
		'jpy' => __( 'JPY', 'currency' ),
		'kes' => __( 'KES', 'currency' ),
		'krw' => __( 'KRW', 'currency' ),
		'kwd' => __( 'KWD', 'currency' ),
		'kyd' => __( 'KYD', 'currency' ),
		'kzt' => __( 'KZT', 'currency' ),
		'lbp' => __( 'LBP', 'currency' ),
		'lkr' => __( 'LKR', 'currency' ),
		'ltl' => __( 'LTL', 'currency' ),
		'lvl' => __( 'LVL', 'currency' ),
		'mad' => __( 'MAD', 'currency' ),
		'mdl' => __( 'MDL', 'currency' ),
		'mkd' => __( 'MKD', 'currency' ),
		'mur' => __( 'MUR', 'currency' ),
		'mvr' => __( 'MVR', 'currency' ),
		'mxn' => __( 'MXN', 'currency' ),
		'myr' => __( 'MYR', 'currency' ),
		'nad' => __( 'NAD', 'currency' ),
		'ngn' => __( 'NGN', 'currency' ),
		'nio' => __( 'NIO', 'currency' ),
		'nok' => __( 'NOK', 'currency' ),
		'npr' => __( 'NPR', 'currency' ),
		'nzd' => __( 'NZD', 'currency' ),
		'omr' => __( 'OMR', 'currency' ),
		'pen' => __( 'PEN', 'currency' ),
		'pgk' => __( 'PGK', 'currency' ),
		'php' => __( 'PHP', 'currency' ),
		'pkr' => __( 'PKR', 'currency' ),
		'pln' => __( 'PLN', 'currency' ),
		'pyg' => __( 'PYG', 'currency' ),
		'qar' => __( 'QAR', 'currency' ),
		'ron' => __( 'RON', 'currency' ),
		'rsd' => __( 'RSD', 'currency' ),
		'rub' => __( 'RUB', 'currency' ),
		'sar' => __( 'SAR', 'currency' ),
		'scr' => __( 'SCR', 'currency' ),
		'sek' => __( 'SEK', 'currency' ),
		'sgd' => __( 'SGD', 'currency' ),
		'skk' => __( 'SKK', 'currency' ),
		'sll' => __( 'SLL', 'currency' ),
		'svc' => __( 'SVC', 'currency' ),
		'thb' => __( 'THB', 'currency' ),
		'tnd' => __( 'TND', 'currency' ),
		'try' => __( 'TRY', 'currency' ),
		'ttd' => __( 'TTD', 'currency' ),
		'twd' => __( 'TWD', 'currency' ),
		'tzs' => __( 'TZS', 'currency' ),
		'uah' => __( 'UAH', 'currency' ),
		'ugx' => __( 'UGX', 'currency' ),
		'usd' => __( 'USD', 'currency' ),
		'uyu' => __( 'UYU', 'currency' ),
		'uzs' => __( 'UZS', 'currency' ),
		'vef' => __( 'VEF', 'currency' ),
		'vnd' => __( 'VND', 'currency' ),
		'xof' => __( 'XOF', 'currency' ),
		'yer' => __( 'YER', 'currency' ),
		'zar' => __( 'ZAR', 'currency' ),
		'zmk' => __( 'ZMK', 'currency' )
	);
	$currencies = explode(',',$atts['currencies']);

	$return = '<select style="min-width:100px;" class="currency_selector">';

	$to = '';

	if(isset($_COOKIE['currency_conversion']) && strpos($_COOKIE['currency_conversion'],'-')){
		list(,$to) = explode('-',$_COOKIE['currency_conversion'],2);
	}

	foreach($currencies as $curr){
		$selected = ($to==$curr) ? 'selected' : '';
		if(isset($all_currencies[$curr])){
			$return .= '<option value="'.$curr.'" '.$selected.'>'.$all_currencies[$curr].'</option>';
		}
	}
	$return.= '</select>';

	//$url = plugins_url('ajax-call.php',__FILE__);

	$ajaxurl = admin_url( 'admin-ajax.php' );

	ob_start();

	?>
	<script>
	jQuery(document).ready(function(){
		jQuery('select.currency_selector').change(function(){
			var to = jQuery(this).val();
			var from = '<?php echo $atts['from']; ?>';
			document.cookie = "currency_conversion="+from+"-"+to+" ; path=/";
            data = {
                'action': 'exchange_rate',
                'from'  : from,
                'to'    : to
            };

            jQuery.ajax({
                context: this,
                type: "POST",
                url: '<?php echo $ajaxurl; ?>',
                dataType: "text",
                data: data,
                success: function(rate)
                {
                    console.log("conversion "+ rate);
                    jQuery('[data-price]').each(function(){
                        var price = jQuery(this).data('price');
                        console.log("conver "+price+" from "+from+" into "+to );
                        newprice = Math.round(price * rate).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        console.log(newprice);
						jQuery(this).text(newprice + ' ' + to.toUpperCase() );
                    });
                }
            });
		})
	});

	</script>
	<?php
	$return .= ob_get_contents();
	ob_end_clean();
	return $return;
}

add_filter('printed_price_value','convert_printed_price_value',10,3);

function convert_printed_price_value($return,$price,$args){
	if(isset($_COOKIE['currency_conversion']) && strpos($_COOKIE['currency_conversion'],'-')){
		list($from,$to) = explode('-',$_COOKIE['currency_conversion'],2);
		if($from != $to)
		{
			$return = ro_convert_currency( $price, $from, $to);
		}
	}
	return $return;
}

add_action('wp_footer', function(){
  ?>
  <script>
   
    (function($) {


      // var from = ['2022, 06, 01', '2022, 07, 01'];
      // var to = ['2022, 06, 09', '2022, 07, 10'];

      var from = JSON.parse(jQuery('#date_start').val());
      var to = JSON.parse(jQuery('#date_end').val());

      $("#my-property-calendar").datepicker({
        beforeShowDay: function (date) {

          for (let i = 0; i < from.length; i++) {
            var date_from = new Date(from[i]);
            var date_to = new Date(to[i]);
            
            if (date >= date_from && date <= date_to) {
                return [true, 'ui-state-error my-highlight-dates', 'tooltipText'];
            }
          }

          return [true, '', ''];
        },
        
      });
      
    }(jQuery));
    
  </script>
  <?php
});