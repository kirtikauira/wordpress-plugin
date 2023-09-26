<?php

/**
 *
 */

require_once(INMO__PLUGIN_DIR.'Connection.php');

class InmoLinkResults
{
    function __construct()
    {
        $token = Connection::get_token();
        $base_url = Connection::get_base_url();
        $this->api_base_url = $base_url;
        $this->api_access_token = $token;
        $this->reset();
    }

    public function reset()
    {
        $this->requestUrl = '';
        $this->count = 0;
        $this->page = 1;
        $this->i = 0;
        $this->results = array();
    }

    public static function shortlist_fav($atts = array(), $content = NULL)
    {
         require_once("/assets/css/CSSfile.css");
           if(!empty($atts['shortlist']))
        {
            if(!isset($_COOKIE['shortlist']) || empty($_COOKIE['shortlist']))
                $args['ref_no'] = 'noref';
            else
                $args['ref_no'] = $_COOKIE['shortlist'];
            
            return $args;
        }
    }
    public static function fav_back_func($atts = array(), $content = NULL){
    global $wpdb;
	 $results = new self();
       $defaults = array(
            'ref_no' => get_query_var('ref_no')
        );
		$defaults = shortcode_atts($defaults, $_GET);
        $atts = shortcode_atts($defaults, $atts); 
        $results->fetch_properties($atts);
        if($results->count != 1){
            $results->reset();
        }
	    $tablename = $wpdb->prefix.'Inmolink_theme_setting';
	    $result	=	$wpdb->get_results("SELECT * FROM $tablename");
		add_shortcode('inmolink_shortlist_button',array($results,'shortlist_button'));
		add_shortcode('inmolink_property_detail',array($results,'property_field'));
    if($result){
        if (function_exists('pll_current_language')){
        $lang =pll_current_language();}else {$lang = 'en'; }
        if(!empty($lang)){$langs = $lang;}else{$langs = 'en';}		
	    foreach($result as $row){
        if ($langs == $row->lang_slug ) {$output = '

				<a href="javascript:history.back();" class="back_btn btn btn-floating btn-small bg_color btn_link p-0"><i class="fa fa-reply-all" ></i>123</a>

				'. do_shortcode('[inmolink_shortlist_button add=\'<i class="fa fa-star-o" ></i>\' remove=\'<i class="fa fa-star" aria-hidden="true"></i>\' class="btn btn-floating btn-small bg_color btn_link p-0"][inmolink_property_detail field="pdf" class="btn btn-floating btn-small bg_color btn_link pdf_btn p-0" ]') .'
                 ';}
	    }

		return $output;
	}


}
    private function get_locale()
    {
        if(function_exists('pll_current_language'))
            $locale = pll_current_language('locale');
        else
            $locale = get_option('WPLANG',get_locale());

        return $locale;
	}

    public function fetch_properties($atts = array())
    {
        static $cache = array();
       
        if(!isset($atts['locale']))
        $atts['ln'] =  $this->get_locale();
		$url = $this->api_base_url . 'v1/property';
        $url.= '?'.http_build_query($atts);
		
        $this->requestUrl = $url;
       
        $transient_key = 'url_json_'.md5($url);

        //returned previously memory cached result
        if(isset($cache[$transient_key]))
        {
            $this->log("curl cached: $url");

            $return = json_decode($cache[$transient_key]);
        }
        else
        {
            $this->log("curl start: $url");

            $curl = curl_init();
    
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "access_token: ".$this->api_access_token),
                    "User-Agent: wp-inmolink-property",
                    "Accept: */*",
                    "WP-Inmolink-Token: ".md5("Inmolink" . microtime()),
                    "Referer: ".$_SERVER['SERVER_NAME'],
                    "Cache-Control: no-cache",
                    "Accept-Encoding: gzip, deflate, br"
                )
            );

            $responseData = curl_exec($curl);
            $json = json_decode($responseData);
          
            //if successful then save to cache and set transient
            if(!curl_errno($curl) && json_last_error() == JSON_ERROR_NONE)
            {
                $this->log($json);
                $cache[$transient_key] = $responseData;
            }
            else
            {
                $this->log("curl error: ".curl_error($curl));
            }
            
            curl_close($curl);
    
            $return = $json;

        }

        $this->results = is_array($return->data) ? $return->data : array();
        $this->count = isset($return->count) ? $return->count : 0 ;
        $this->page = isset($return->page) ? $return->page : 1 ;
        $this->pages = isset($return->pages) ? $return->pages : 0 ;
        //echo "<script>console.log("$return->data")</script>";
    }

    public function parseGetParams($atts = array(), $post_id = 0)
    {
		
		
        if($post_id == 0)
            $post_id = get_the_id();
        
        if(isset($_GET['il_page']) && is_numeric($_GET['il_page']))
        {
            $args['page'] = (int)$_GET['il_page'];
        }
		if(isset($_GET['order']))
        {
            $args['order'] = $_GET['order'];
        }
        if(isset($_GET['ref_no']))
        {
            $args['ref_no'] = $_GET['ref_no'];
        }
        $args['limit'] = $atts['perpage'];
        
        if(!empty($atts['shortlist']))
        {
            if(!isset($_COOKIE['shortlist']) || empty($_COOKIE['shortlist']))
                $args['ref_no'] = 'noref';
            else
                $args['ref_no'] = $_COOKIE['shortlist'];
            
         return $args;
		
        }
        
        /*
        * Filter locations
        */

        $term_ids = array();
        
        // keep backward compatibility with single slug parameter
        if(empty($atts['locations']) && !empty($atts['location']))
            $atts['locations'] = $atts['location'];

        if(!empty($atts['locations'])){
            $term_slugs = explode(',',$atts['locations']);

            foreach($term_slugs as $slug){
                $term = get_term_by('slug', $slug, 'locations');
                if($term !== false)
                    $term_ids[] = $term->term_id;
            }
        }

        if(empty($term_ids) && isset($_GET['location']))
        {
            $term_ids = $_GET['location'];
            if(!is_array($term_ids)){
                $term_ids = array($term_ids);
            }
        }
        if(empty($term_ids))
        {
            $term_ids = array_map( function($term){ return $term->term_id; }, wp_get_post_terms($post_id, 'locations') );
        }

        if(!empty($term_ids))
        {
            $remote_ids = array();

            foreach ($term_ids as $term_id) {
                if($remote_id = get_term_meta((int)$term_id, 'location_id', true))
                    $remote_ids[] = $remote_id;
                else
                {
                    $child_terms = get_term_children( $term_id, 'locations' );
                    foreach($child_terms as $child_term_id)
                    {
                        if($remote_id = get_term_meta((int)$child_term_id, 'location_id', true))
                            $remote_ids[] = $remote_id;
                    }
                }
            }

            $args['location_id'] = implode(',', $remote_ids);
        }
        
        /**
         * Filter property types
         */

        $term_ids = array();
        
        // keep backward compatibility with single slug parameter
        if(empty($atts['types']) && !empty($atts['type']))
            $atts['types'] = $atts['type'];

        if(!empty($atts['types'])){
            $term_slugs = explode(',',$atts['types']);
            
            foreach($term_slugs as $slug){
                $term = get_term_by('slug', $slug, 'types');
                if($term !== false)
                    $term_ids[] = $term->term_id;
            }
        }

        if(empty($term_ids) && isset($_GET['type']))
        {
            $term_ids = $_GET['type'];
            if(!is_array($term_ids)){
                $term_ids = array($term_ids);
            }
        }
        if(empty($term_ids))
        {
            $term_ids = array_map( function($term){ return $term->term_id; }, wp_get_post_terms($post_id, 'types') );
        }

        if(!empty($term_ids))
        {
            $remote_ids = array();
            foreach ($term_ids as $term_id) {
                if($remote_id = get_term_meta((int)$term_id, 'type_id', true))
                    $remote_ids[] = $remote_id;
                else
                {
                    $child_terms = get_term_children( $term_id, 'types' );
                    foreach($child_terms as $child_term_id)
                    {
                        if($remote_id = get_term_meta((int)$child_term_id, 'type_id', true))
                            $remote_ids[] = $remote_id;
                    }
                }
            }

            $args['type_id'] = implode(',', $remote_ids);
        }

        /**
         * Filter features
         */

        $term_ids = array();
        
        if(!empty($atts['features'])){
            $term_slugs = explode(',',$atts['features']);

            foreach($term_slugs as $slug){
                $term = get_term_by('slug', $slug, 'features');
                if($term !== false)
                    $term_ids[] = $term->term_id;
            }
        }

        if(empty($term_ids) && isset($_GET['features']))
        {
            $term_ids = $_GET['features'];
            if(!is_array($term_ids)){
                $term_ids = array($term_ids);
            }
        }
        if(empty($term_ids))
        {
            $term_ids = array_map( function($term){ return $term->term_id; }, wp_get_post_terms($post_id, 'features') );
        }

        if(!empty($term_ids))
        {
            $remote_ids = array();
            foreach ($term_ids as $term_id) {
                if($remote_id = get_term_meta((int)$term_id, 'feature_id', true))
                    $remote_ids[] = $remote_id;
                else
                {
                    $child_terms = get_term_children( $term_id, 'features' );
                    foreach($child_terms as $child_term_id)
                    {
                        if($remote_id = get_term_meta((int)$child_term_id, 'feature_id', true))
                            $remote_ids[] = $remote_id;
                    }
                }
            }

            $args['features'] = implode(',', $remote_ids);
        }

        if($refs = get_query_var('ref_no'))
        {
            //$args['ref_no'] = $refs;
        }

        $params = array(
            'listing_type',
            'bedrooms_min',
            'bedrooms_max',
            'bathrooms_min',
            'bathrooms_max',
            'list_price_min',
            'list_price_max',
			'build_size_min',
			'build_size_max',
			'plot_size_min',
			'plot_size_max',
			'terrace_size_min',
			'terrace_size_max',
			'ownonly'

        );

        foreach ($params as $param)
        {
            if(isset($_GET[$param]) && !empty($_GET[$param]))
            {
                $args[$param] = $_GET[$param];
            }
        }
        /**
         * Filter similar
         */
		
		 if(!empty($atts['similar'])){
          $id=get_query_var('ref_no');
			$curl = curl_init();
          curl_setopt_array($curl, array(
           CURLOPT_URL => 'https://cp.inmolink.xyz/v1/property?ref_no='.$id.'&ln=en_GB',
           CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
                    "access_token: ".$this->api_access_token),
                    "User-Agent: wp-inmolink-property",
                    "Accept: */*",
                    "WP-Inmolink-Token: ".md5("Inmolink" . microtime()),
                    "Referer: ".$_SERVER['SERVER_NAME'],
                    "Cache-Control: no-cache",
                    "Accept-Encoding: gzip, deflate, br"
                )
);

$response = curl_exec($curl);
 $json = json_decode($response,true);
curl_close($curl);
$similar_ids=$json['data'][0]['similar_property_ids'];
if( sizeof($similar_ids) == 0 ){
$args['id'] = 'noid';
return $args;	
}
else{
$args['id'] = implode(',',$similar_ids);
		return $args;
}
		 }
	
        return $args;
    }
      
	  
    public static function property_shortcode($atts = array(), $content = NULL)
    {
        $results = new self();

        $defaults = array(
            'ref_no' => get_query_var('ref_no')
        );
		$defaults = shortcode_atts($defaults, $_GET);
        $atts = shortcode_atts($defaults, $atts);
        $results->fetch_properties($atts);
        if($results->count != 1){
            $results->reset();
        }
         
        add_shortcode('property_field',array($results,'property_field'));
        add_shortcode('inmolink_property_detail',array($results,'property_field'));
        add_shortcode('inmolink_shortlist_button',array($results,'shortlist_button'));
        add_shortcode('inmolink_property_detail_slider',array($results,'property_slider'));
        add_shortcode('inmolink_property_detail_gallery',array($results,'property_gallery'));
        add_shortcode('inmolink_property_detail_agentlogo',array($results,'property_agent_logo'));
        add_shortcode('inmolink_agent',array($results,'agent_details'));
        add_shortcode( 'inmolink_property_detail_map', array($results, 'property_location_map') );
        add_shortcode( 'inmolink_property_detail_features', array($results, 'property_featuressection') );
		add_shortcode('inmolink_social_sharing',array($results,'inmolink_social_sharing_link'));
        


        if(empty($atts['ref_no']))
            return '';

        $return = '';
        if($pixel_url = $results->get_tracking_pixel_url())
            $return .= '<img src="'.$pixel_url.'" class="inmolink_pixel" />';
            
        $return .= do_shortcode($content);

        return $return;
    }

    public function agent_details($atts = array(), $content = NULL)
    { 
        
        $property = &$this->results[$this->i];
        
        if(!isset($property->agent_id) || !isset($property->agent_id->id) || (int)$property->agent_id->id == 0){
            return '';
        }

        $property_agent = new InmolinkAgent($property->agent_id->id);
        return do_shortcode($content);
    }

    public function get_tracking_pixel_url()
    {
        $property = &$this->results[$this->i];

        if(!isset($property) || !isset($property->ref_no) ||  !isset($property->id))
            return false;

        $url = $this->api_base_url . 'inmolink/property/'.$property->id.'/'.md5(time()).'/'.$property->ref_no.'.gif';
    
        return $url;
    }

    private function prepare_results_args($atts=array())
    {
		
        $defaults = array(
		    'ref_no' => '',
            'type' => '',
            'location' => '',
            'types' => '',
            'locations' => '',
            'features' => '',
            'pagination_class' => '',
			'similar' => is_int(array_search('similar',$atts)) ? '1' : '',
            'listing_type' => '',
            'perpage' => 12,
            'shortlist' => is_int(array_search('shortlist',$atts)) ? '1' : '',
        );
        $args = $this->parseGetParams(shortcode_atts($defaults, $atts));
       
        // pass-through values will be sent directly as-is to the API
        $apiDefaults = array(
            'ref_no' => '',
            'bedrooms_min' => '',
            'bedrooms_max' => '',
            'bathrooms_min' => '',
            'bathrooms_max' => '',
            'list_price_min' => '',
            'list_price_max' => '',
            'order' => '',
            'ownfirst' => '',
			 'similar' => '',
            'ownonly' => '',
            'listing_type' => ''
        );

        $apiAtts = shortcode_atts($apiDefaults, $atts);
        $args = array_merge(array_filter($apiAtts),array_filter($args));

        $args = array_filter($args);
        return $args;
      }

    public function noresults_shortcode($atts = array(), $content = NULL)
    {
        $results = new self();
        $args = $results->prepare_results_args($atts);
		
        $results->fetch_properties($args);

        if(count($results->results) == 0){
            return do_shortcode($content);
        }
        return '';
    }
    public static function introtext_shortcode($atts = array(), $content = NULL)
    {
        $content = empty($content) ? "<p>At the moment we can offer you a selection ".
            "of #COUNT# #TYPE#s for sale in #LOCATION#. Although you will find some smaller ".
            "#LOCATION# #TYPE#s for as little as #MINPRICE#, the average price for #TYPE#s ".
            "for sale in or around #LOCATION# is #AVGPRICE#.</p><p>Browse through the #LOCATION# ".
            "#TYPE#s on this page and create a list of favourites to review later in more detail. ".
            "You can send the list to yourself or request more detailed information from one of our ".
            "#LOCATION# Estate agents.</p>" : $content;
        
        $vars = array(
            'TYPE' => '',
            'LOCATION' => '',
            'COUNT' => '',
            'MINPRICE' => '',
            'MAXPRICE' => '',
            'AVGPRICE' => '',
        );
        
        $results = new self();
        $args = $results->prepare_results_args($atts);
        $args['order'] = 'list_price_asc';
        $results->fetch_properties($args);
        if($results->count == 0){
            return '';
        }
        $vars['COUNT'] = $results->count;
        $price = $results->results[0]->list_price;
        $vars['MINPRICE'] = number_format((int)$price,0,'',',');
        $vars['AVGPRICE'] = number_format((int)$price*2.5,0,'',',');

        $results = new self();
        $args['order'] = 'list_price_desc';
        $price = $results->results[0]->list_price;
        $vars['MAXPRICE'] = number_format((int)$price,0,'',',');

        $post_id = get_the_ID();

        $locations = wp_get_post_terms( $post_id, 'locations', array('fields'=>'names') );
        $vars['LOCATION'] = implode(', ',$locations);

        $types = wp_get_post_terms( $post_id, 'types', array('fields'=>'names') );
        $vars['TYPE'] = implode(', ',$types);

        foreach($vars as $k => $v){
            $content = str_replace('#'.$k.'#',$v,$content);
        }
        return $content;
    }

    public static function properties_shortcode($atts = array(), $content = NULL)
    {
		
        $results = new self();
        $args = $results->prepare_results_args($atts);
		
		$results->fetch_properties($args);
        add_shortcode('property_field',array($results,'property_field'));
        add_shortcode('inmolink_property_result_field',array($results,'property_field'));
        add_shortcode('property_permalink',array($results,'permalink'));
        add_shortcode('inmolink_property_permalink',array($results,'permalink'));
        add_shortcode('inmolink_shortlist_button',array($results,'shortlist_button'));
        

        $return = '';
        for ($i=0; $i < count($results->results); $i++) {
            $results->i = $i;
            $return .= do_shortcode($content);
            // $return .= $results->results[$i]->ref_no;
        }

        if($results->pages > 1)
        {
            $return .= '<div class="'.$atts['pagination_class'].'" style="clear:both;">';
            $return .= paginate_links(array(
                'total' => $results->pages,
                'format' => '?il_page=%#%',
                'current' => $results->page,
                'mid_size' => 2
            ));
            $return .= '</div>';
        }
        return $return;
    }

    public function permalink($atts = array(), $content = NULL)
    {
        $slug = Connection::get_list_slug();
        $property = &$this->results[$this->i];
        $defaults = array(
            'target' => '',
            'class'  => '',
            'rel'    => '',
            'href'   =>$slug,
            'pretty' => '',
        );
        $atts = wp_parse_args( $atts, $defaults );
        $target = $atts['target'];
        $class = $atts['class'];
        $rel = $atts['rel'];
        $href = get_site_url().'/'.$atts['href'] . '/';

        if($atts['pretty'] != '') 
            $href .= $property->ref_no.'_'.sanitize_title($property->type_id->name . ' ' . $property->location_id->name).'/';      
        else
            $href .= '?ref_no='.$property->ref_no;        

        $link = '<a href="'.$href.'" class="'.$class.'" rel="'.$rel.'" target="'.$target.'">'.do_shortcode($content).'</a>';
        return $link;
    }

    public function property_field($atts = array(), $content = NULL)
    {
		$slug = Connection::get_list_slug();
        $property = &$this->results[$this->i];

        // echo '<pre>';
        // print_r($property->date_ranges);
        // die();
           
         $l = $this->i;
         global $wpdb;
		 if (function_exists('pll_current_language')){
         $lang =pll_current_language();}else {$lang = 'en'; }
         if(!empty($lang)){$langs = $lang;}else{$langs = 'en';}
		 $tablename = $wpdb->prefix.'Inmolink_property_listing';
	     $result	=	$wpdb->get_results("SELECT lang_slug,per_month,per_week,from_list FROM $tablename"); 
		 if($result){
           
            foreach($result as $row){ 
                $lang_slug = $row->lang_slug;
                    if ($langs == $lang_slug ) {
                        $month = $row->per_month;
                        $week = $row->per_week;
                        $from = $row->from_list;
                    }
			     }
		
             }
        
         $atts = shortcode_atts( array (
            'field'  => 'ref_no',
            'format'  => '%s',
            'maxlength' => '',
            'class'  => '',
            'class'  => '',
            'from'  => $from.' ',
            'separator' => ' - ',
            'per_month' => ' / '.$month,
            'per_week' => ' / '.$week,
			'thousands' => ''
        ), $atts );
         
        $displaydata = '';

        if(isset($property->is_own) && $property->is_own == true)
        {
            $atts['class'] .= ' own';
        }

        if(isset($property->is_featured) && $property->is_featured == true)
        {
            $atts['class'] .= ' featured';
        }

        if(isset($property->is_onsale) && $property->is_onsale == true)
        {
            $atts['class'] .= ' onsale';
        }
        
        $field = $atts['field'];
		echo '<pre>';
		print_r($field );
		die;

        if($field == 'slider')
        {
		
        $defaults = array(
            'target' => '',
            'class'  => '',
            'rel'    => '',
            'href'   =>$slug,
            'pretty' => '',
        );
        $atts = wp_parse_args( $atts, $defaults );
        $target = $atts['target'];
        $class = $atts['class'];
        $rel = $atts['rel'];
        $href = get_site_url().'/'.$atts['href'] . '/';
          
            $href .= $property->ref_no.'_'.sanitize_title($property->type_id->name . ' ' . $property->location_id->name).'/';     
			$displaydata .= '<div id="slide_cont_'.$l.'">';
			//$displaydata .='<a href="'.$href.'" class="'.$class.'" rel="'.$rel.'" target="'.$target.'">';
        	$displaydata .= '<div id="slide_cont" class="'.$atts['class'].'" style="margin-top: 0px;" >';
        	$displaydata .= '<img src="'.$property->images[0]->src.'" class="MainImage" alt="'.$property->images[0]->name.'">';
        	$displaydata .= '</div>';
            $displaydata .= '<a id="prevv_image" class="results-prev-image"></a>';
            $displaydata .= '<a id="nextt_image" class="results-next-image"></a>';
            $displaydata .= "<input type='hidden' id='img_no' name='test_name' value='0'>";
			//$displaydata .='</a>';
            $displaydata .= '</div>';   ?>
            <script>
              jQuery(document).ready(function(){
	
                var images =[  <?php foreach($property->images as $image){?>
                         "<?php echo $image->src; ?>" ,<?php }?> ]
	               //previous
             jQuery("#slide_cont_<?php echo $l ?> a#prevv_image").click(function(){
             jQuery( '#slide_cont_<?php echo $l ?> img.MainImage' ).fadeOut(3,function()
              {
               var prev_val = jQuery( "#slide_cont_<?php echo $l ?> input#img_no" ).val();
               var prev_val = Number(prev_val)-1;
             if(prev_val <= -1)
             {
             prev_val = images.length - 1;
              }
             jQuery( '#slide_cont_<?php echo $l ?> img.MainImage' ).attr( 'src' , images[prev_val] );
             jQuery( "#slide_cont_<?php echo $l ?> input#img_no" ).val(prev_val);
              });
             jQuery( '#slide_cont_<?php echo $l ?> img.MainImage' ).fadeIn(0);
              });
 
                // next image 
             jQuery( "#slide_cont_<?php echo $l ?> a#nextt_image" ).click(function(){
	         jQuery( '#slide_cont_<?php echo $l ?> img.MainImage' ).fadeOut(1,function()
              {
             var next_val = jQuery( "#slide_cont_<?php echo $l ?> input#img_no" ).val();
             var next_val = Number(next_val)+1;
             if(next_val >= images.length)
              {
               next_val = 0;
              }
            jQuery( '#slide_cont_<?php echo $l ?> img.MainImage' ).attr( 'src' , images[next_val] );
            jQuery( "#slide_cont_<?php echo $l ?> input#img_no" ).val(next_val);
              });
            jQuery( '#slide_cont_<?php echo $l ?> img.MainImage' ).fadeIn(0);

              });
              });
            </script>                          
         <?php }

        elseif($field == 'image')
        {
        	$displaydata .= '<span id="inmolink-image_'.$l.'" class="MainImage '.$atts['class'].'">';
        		$displaydata .= '<img src="'.$property->images[0]->src.'" class="MainImage" alt="'.$property->images[0]->name.'">';
        	$displaydata .= '</span>';
        }
        elseif($field == 'ref_no')
        {
            $displaydata .= $property->ref_no;
        }
        elseif($field == 'bedrooms' AND isset($atts['class']) AND $atts['class'] !='' AND $atts['class'] !='px-2 Bedrooms')
        {
            //if($property->bedrooms != 0){
                $displaydata .='<span class="'.$atts['class'].'">';
                $displaydata .=$property->bedrooms;
                $displaydata .='</span>';
            //}
        }
	
        elseif( $field == 'calendar' ){
            $from = array();
            $to = array();
            if ( isset($property->date_ranges) ){
                foreach($property->date_ranges->Range as $range){
                    $start = $range->Start;
                    $end = $range->End;

                    $date_start= str_replace('-', ', ', $start);
                    $date_end= str_replace('-', ', ', $end);
                    $from[] = $date_start;
                    $to[] = $date_end;
                    // array_push($from, $date_start);
                    // array_push($to, $date_end);
                
                    ?>
                    <?php
                }
                
                $displaydata .= "<input type='hidden' id='date_start' value='".json_encode($from)."'>";
                $displaydata .= "<input type='hidden' id='date_end' value='".json_encode($to)."'>";
                

                $displaydata .= '<div id="my-property-calendar"></div>';
            }
        }
        elseif($field == 'pdf')
        {
        	$displaydata .= '<span id="inmolink-pdf_'.$l.'">';
        		$displaydata .= '<a href="'.$property->pdf.'" target="_blank" class="'.$atts['class'].'">PDF</a>';
        	$displaydata .= '</span>';
        }
        elseif( $field == 'seo_page_title' ){
            $displaydata .= $property->seo_info->page_title;
        }
        elseif( $field == 'seo_meta_title' ){
            $displaydata .= $property->seo_info->meta_title;
        }
        elseif( $field == 'seo_meta_keywords' ){
            $displaydata .= $property->seo_info->meta_keywords;
        }
        elseif( $field == 'seo_meta_description' ){
            $displaydata .= $property->seo_info->meta_description;
        }
        elseif($field == 'virtualtour' )
        {
            if(isset($property->virtual_tour_url) && $property->virtual_tour_url != '')
            {
                $url = (string)$property->virtual_tour_url;

                $matches = array();
                preg_match('~vimeo.com/(\d+)/?~',$url,$matches);
                if(isset($matches[1])){
                    $url = 'https://player.vimeo.com/video/'.$matches[1];
                }
                preg_match('~(?:youtube\.com/watch[\?&]v=|youtu\.be/)([\w\-\_]+)~',$url,$matches);
                if(isset($matches[1])){
                    $url = 'https://www.youtube.com/embed/'.$matches[1];
                }
                $displaydata .= '<span id="inmolink-virtualtour_'.$l.'" class="'.$atts['class'].'">';
                $displaydata .= '<iframe src="'.$url.'" width="100%" height="100%" ></iframe>';
                $displaydata .= '</span>';
            }
        }
        elseif($field == 'video' )
        {
            if(isset($property->video_url) && $property->video_url != '')
            {
                $url = (string)$property->video_url;

                $matches = array();
                preg_match('~vimeo.com/(\d+)/?~',$url,$matches);
                if(isset($matches[1])){
                    $url = 'https://player.vimeo.com/video/'.$matches[1];
                }
                preg_match('~(?:youtube\.com/watch[\?&]v=|youtu\.be/)([\w\-\_]+)~',$url,$matches);
                if(isset($matches[1])){
                    $url = 'https://www.youtube.com/embed/'.$matches[1];
                }
                $displaydata .= '<span id="inmolink-video_'.$l.'" class="'.$atts['class'].'">';
                $displaydata .= '<iframe src="'.$url.'" width="100%" height="100%" ></iframe>';
                $displaydata .= '</span>';
            }
        }
		
        elseif(isset($property->{$field}) && !is_array($property->{$field}))
        {
		
			
            $value = $property->{$field};
			
			$style='';
            if($field == 'bedrooms' && $value ==0){
                $style='style="display:none;"';
            }
           $displaydata .= '<span class="'.$atts['class'].'" '.$style.'>';
          
            $formatted = apply_filters('property_field_value',$value,$field,$atts,$property);
             
            if($field == 'list_price')
			
                $formatted = apply_filters('printed_price_value', $formatted, $value, $atts);
	
             // if {field}_2 is 0, it means field is a starting value
            if((int)$property->{$field} != 0 && isset($property->{$field.'_2'}) && (int)$property->{$field.'_2'}  != 0 )
            {
                $displaydata .= $atts['from'];
            }
          
            if($field == 'list_price'){
                $displaydata .= '<span '.($field == 'list_price' ? 'data-price="'.$value.'"' : '' ).' class="Numeric_pricevalues" >';
            }

            if (filter_var($formatted, FILTER_VALIDATE_URL)) { 
                $displaydata .= '<img src="'.$formatted.'" />';
            }
            else{
                $displaydata .= $formatted;
            }

            if($field == 'list_price'){
                $displaydata .= '</span>';
            }
             
				
            // if {field}_2 is present, append with separator
            if(isset($property->{$field.'_2'}) && (int)$property->{$field.'_2'} > (int)$property->{$field} )
            {
                $value = $property->{$field.'_2'};
                $formatted = apply_filters('property_field_value',$value,$field,$atts,$property);
                
                if($field == 'list_price')
                    $formatted = apply_filters('printed_price_value', $formatted, $value, $atts);

                $displaydata .= $atts['separator'];
                $displaydata .= '<span '.($field == 'list_price' ? 'data-price="'.$value.'"' : '' ).' >';
                $displaydata .= $formatted;
                $displaydata .= '</span>';
            }

            if($field == 'list_price')
            {
                if($property->listing_type == 'long_rental')
                    $displaydata .= $atts['per_month'];

                if($property->listing_type == 'short_rental')
                    $displaydata .= $atts['per_week'];
				
            }

        	$displaydata .= '</span>';
			
        }
		
        elseif(isset($property->{$field.'_id'}->name))
        {
        	$displaydata .= '<span class="'.$atts['class'].'">';
            $value = $property->{$field.'_id'}->name;
            $value = apply_filters('property_field_value',$value,$field,$atts,$property);

            $displaydata .= sprintf($atts['format'],$value);
        	$displaydata .= '</span>';
        }
       
        return $displaydata;

     }
        
    public function shortlist_button($atts=array(), $content=NULL){
		
        $defaults = array(
            'class' =>	'',
            'add' =>	'&#9734;',
            'remove' =>	'&#9733;',
        );
        $atts = shortcode_atts($defaults, $atts);
        
        $property = &$this->results[$this->i];
        $class = 'add_shortlist btn btn-floating btn-small bg_color btn_link border-0 p-0 '.$atts['class'];
        $label_add = $atts['add'];
        $label_remove = $atts['remove'];
    
        $ref = (string)$property->ref_no;
    
        if(isset($_COOKIE['shortlist']) && in_array($ref,explode(',',$_COOKIE['shortlist'])))
           $label = $label_remove;
        else
            $label = $label_add;
    
        return "<button class='$class' data-ref='". $ref ."' data-label-add='".$label_add."' data-label-remove='".$label_remove."'>".$label."</button>";
    }
            



    public function property_gallery($atts=array(), $content=NULL){
        
        $defaults = array(
        );
        $atts = shortcode_atts( $defaults, $atts);

        $data = array(
            'property' => $this->results[$this->i],
        );
        $return_data = '';
		$return_data ='<div class="clearfix">';
        $return_data .= '<ul id="image-gallery" class="gallery list-unstyled cS-hidden">';
         foreach($data['property']->images as $image): 
          $return_data .=' <li data-thumb="'.$image->src.' "> <img src="'.$image->src.'"/></li>';
         endforeach; 
        $return_data .='</ul>';
        $return_data .= '<div class="popup-gallery">';
         foreach ($data['property']->images as $image) :
          $return_data .= '<a href="'.$image->src.'" title="">
          <img src="'.$image->src.'" width="75" height="75">
          </a>';
        endforeach; 
        $return_data .= '</div>';
        $return_data .='</div>';
        return $return_data;
    }   
    function property_agent_logo( $atts=array() )
    {
        $defaults = array(
            'class'	=>	'',
            'default' => '',
            'class_no_logo' => 'no_logo'
        );
        $atts = shortcode_atts($defaults, $atts);
        
        $property = $this->results[$this->i];
        
        $class = $atts["class"];
        
        if(!isset($property) || !isset($property->agent_id->logo) || empty($property->agent_id->logo) ){
            $class .= " ".$atts["class_no_logo"];
            $img = !empty($atts["default"]) ? '<img src="' . (string)$atts["default"] . '" />' : '';
        }else{
            $img = '<img src="' . (string)$property->agent_id->logo . '" />';
        }
    
        return '<div class="' . $class . '">'.$img.'</div>';
    }

    function property_location_map( $atts=array(), $content=NULL )
    {
        $data = array(
            'property' => $this->results[$this->i],
        );
       
        ob_start();
        inmolink_get_template('property-map.php', $data);
        $return_data = ob_get_clean();
        return $return_data;
    } 
	function inmolink_social_sharing_link($atts=array(), $content=NULL){
		 $data =$this->results[$this->i];
		 $URL= '';
		 $variable='';
		 $main=home_url($wp->request);
         $URL = $main."$_SERVER[REQUEST_URI]";
         $Title =$data->name;
        
		 $twitterURL = 'https://twitter.com/intent/tweet?text='.$Title.'&amp;url='.$URL;
		 $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u='.$URL.'&t='.$Title;
        // $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u='.$URL.'&t='.$Title.'&img='.$imggg;
		 $whatsappURL='whatsapp://send?text='.$URL;
		 $variable .= '<div class="wito-social">';
		 $variable .= '<a class="wito-facebook" href="'.$facebookURL.'" target="_blank">`Facebook</a>';
		 $variable .= '<a class="wito-twitter" href="'. $twitterURL .'" target="_blank">Twitter</a>';
		 $variable .= '<a class="wito-email" href="mailto:?Subject='.$Title.'&amp;Body='.$URL.'">Email
         </a>';
		 $variable .= '<a class="wito-whatsapp" href="'.$whatsappURL.'" data-pin-custom="true" target="_blank">Whatsapp</a>';
		 $variable .= '</div>';
		 return $variable;
	}
    public function property_slider($atts=array(), $content=NULL)
    {
        $defaults = array(
        );
        $atts = shortcode_atts( $defaults, $atts);
        $data = array(
            'property' => $this->results[$this->i],
        );
        $template = 'property-detail-slider.php';
        ob_start();
        inmolink_get_template($template, $data);
        $return_data = ob_get_clean();
        return $return_data;
    }
    
    function property_featuressection( $atts=array(), $content=NULL )
    {
        $data = array(
            'property' => $this->results[$this->i],
        );

        ob_start();
        inmolink_get_template('property-feature.php', $data);
        $return_data = ob_get_clean();
        return $return_data;
    }

    public function pagination($atts)
    {
        $atts = array(
            'total' => $as->pages,
            'current' => $as->page,
            'show_all' => false,
            'mid_size' => $mid,
            'show' => 2
        );
        return paginate_links($args);
    }

    private function log($message)
    {
        if(is_super_admin() && !wp_doing_ajax())
            echo "<script>console.log(".json_encode($message).")</script>";
    }
}

add_filter('property_field_value',function($value,$field,$atts)
{
	if(!isset($atts['format']))
        return $value;
        
    return sprintf($atts['format'],$value);
},15,3);

add_filter('property_field_value',function($value,$field,$atts)
{  
	
   if(is_numeric($value) && isset($atts['thousands']) && !empty($atts['thousands']))
    {
        $value = number_format($value, 0, '',$atts['thousands']);
    }
	
    return $value;
},10,3);

add_filter('property_field_value',function($value,$field,$atts)
{
    if(isset($atts['maxlength']) && intval($atts['maxlength']) > 0 )
    {
        $value = mb_substr($value,0,(int)$atts['maxlength']);
    }
    return $value;
},10,3);

add_filter( 'property_field_value_refs', 'resalesonline_gform_refs_field' );
function resalesonline_gform_refs_field( $value ) {
  
    if(!isset($_COOKIE['shortlist']))
        return '';
 if(is_array($_COOKIE['shortlist']))
        return implode(',',$_COOKIE['shortlist']);
    else
        return $_COOKIE['shortlist']; 
}

add_filter( 'gform_field_value_refs', 'gform_refs_field' );
function gform_refs_field( $value ) {
    if(!isset($_COOKIE['shortlist']))
        return '';

	if(is_array($_COOKIE['shortlist']))
		return implode(',',$_COOKIE['shortlist']);
	else
	    return $_COOKIE['shortlist'];
}


?>
