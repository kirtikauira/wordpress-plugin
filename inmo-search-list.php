<?php
require_once(dirname(__FILE__).'/Connection.php');
class InmoLinkSearch
{
    
    static public function form_shortcode($atts = array(), $content = NULL)
    {  
        $search_slug = Connection::get_search_slug();
        $slug=  $search_slug;
        $form = new self();
        $defaults = array (
            'class' => '',
            'action' => '',
            'method' => 'get',
            'addurlparams' => '',
        );
        $atts = shortcode_atts($defaults,$atts);
        add_shortcode("inmolink_property_search_field", array($form, 'field'));
        add_shortcode("inmolink_featurecheckboxes", array($form, 'inmolink_feature_checkboxes_field'));
        add_shortcode( 'inmolink_multilevel_location',array($form,'shortcode'));
       // $class = str_replace('-',' ',$atts['class']);
        $return = '<form id="details"'.
            'class="'.$atts['class'].'" '.
            'action="'.$atts['action'].'" '.
            'method="'.$atts['method'].'" '.
            '>';
        $fields = do_shortcode($content);
        if( $atts['addurlparams'] != '' ) {

            foreach($_REQUEST as $k => $v) {

                
                if(strpos($fields, 'name="'.$k))
                    continue;

                if(!is_array($v)) {
                    $return .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
                } else {
                    foreach($v as $v2) {
                        $return .= '<input type="hidden" name="'.$k.'[]" value="'.$v2.'" />';
                    }
                }
            }
        } 

        $return .= $fields;
        $return .= '</form>';

        return $return;
    }    

    public function field($atts = array(), $content = NULL )
    {
        $atts2 = $atts;
        $defaults = array (
            'field' => '',
            'label' => '',
            'data' => '',
            'thousands' => ',',
            'slug' => '',
            'parent' => '',
            'class' => '',
			'format'  => '%s'
        );
        $atts = shortcode_atts($defaults,$atts);
        
		
        $script = "";
        
        $postfield = $atts['field'];
        $postlabel = $atts['label'];
        $postdata = $atts['data'];
		$postformat = $atts['format'];
   
        foreach($atts2 as $k => $v){
            if(strpos($k,'data_')===0)
            {
                list($field,$value) = explode('__',substr($k,5),2);
                $d = explode(',',$v);

                if(isset($_GET[$field]) && $_GET[$field]==$value)
                    $postdata = $v;

                $options_string = '<option value="">'.$postlabel.'</option>';
                foreach($d as $v2){
                    $k2 = $v2;
                    $options_string .= '<option value="'.$k2.'">'.$v2.'</option>';
                }

                $script .= '<script>
                jQuery("[name=\''.$field.'\']").change(function(){
                    var value = jQuery(this).children(\'option:selected\').first().val();
                    console.log(value);
                    if(value == "'.$value.'"){
                        jQuery("[name=\''.$postfield.'\']").children("option").remove();
                        jQuery("[name=\''.$postfield.'\']").append(\''.$options_string.'\');

                        if(typeof(jQuery("select[name=\''.$postfield.'\']").multiselect) == "function"){
                            jQuery("select[name=\''.$postfield.'\']").multiselect("refresh");
                        }
                    }
                });
                </script>';
            }
        }
        $dispalydata = '';
   
        if($postfield == 'ref_no'){
            $dispalydata .='<input type="text" placeholder="'.$postlabel.'" id="ref_no" name="ref_no" value="">';
            
        }
        if($postfield == 'listing_type'){
          
            $postdata = explode(',', $postdata);
            //$postdata = str_replace('-',' ',$postdata);
            
            $dispalydata .='<select name="listing_type" class="statuslist">';
    
            if(!empty($postlabel))
                //$postlabel = str_replace('-',' ',$postlabel);
                $dispalydata .='<option value="">'.$postlabel.'</option>';
                
            foreach ($postdata as $v)
            {
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;
               
                 
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }

            $dispalydata .='</select>';

        } 
        if($postfield == 'furnished'){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select name="furnished">';
    
            if(!empty($postlabel))
                $dispalydata .='<option value="">'.$postlabel.'</option>';
    
            foreach ($postdata as $v)
            {
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;

                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
            } 
            if($postfield == 'location'){
                
              $args = array(
                'show_option_all'   => $postlabel,
                'option_none_value' => 0,
                'hide_empty'        => 0,
                'selected'          => isset( $_GET['location'] ) ? $_GET['location'] : '' ,
                'hierarchical'      => 1,
                'parent'            => '',
                'echo'              => 0,
                'name'              => 'location',
                'taxonomy'          => 'locations',
                'multiple'          => true,
                'hide_if_empty'     => false,
                'value_field'       => 'term_id',
                'class'             => 'LocationType',
                
            );
            if($atts['parent']!='' && $parent_term = get_term_by('slug', $atts['parent'], 'locations')){
                $args['parent'] = $parent_term->term_id;
                $args['option_none_value'] = $parent_term->term_id;
                $args['selected'] = isset( $_GET['location'] ) ? $_GET['location'] : $parent_term->term_id;
                $args['show_option_none'] = $parent_term->name;
                $args['show_option_all'] = '';
            }
            $dispalydata .= wp_dropdown_categories( $args );
            }
            if($postfield == 'type'){
             if (is_array($_GET['type'])){
              $value = implode($_GET['type'],',');}
               else{$value = $_GET['type']; }
               
             $args = array(
                'show_option_all'   => $postlabel,
                'option_none_value' => 0,
                'hide_empty'        => 0,
                'selected'          => isset( $_GET['type'] ) ? $value : '' ,
                'hierarchical'      => 1,
                'parent'            => '',
                'echo'              => 0,
                'name'              => 'type',
                'taxonomy'          => 'types',
                'multiple'          => true,
                'hide_if_empty'     => false,
                'value_field'       => 'term_id',
                'class'             => 'PropertyType',
                'id'                => 'second'

            );

            
            if($atts['parent']!='' && $parent_term = get_term_by('slug', $atts['parent'], 'types')){
                $args['parent'] = $parent_term->term_id;
                $args['option_none_value'] = $parent_term->term_id;
                $args['selected'] = isset( $_GET['type'] ) ? $_GET['type'] : $parent_term->term_id;
                $args['show_option_none'] = $parent_term->name;
                $args['show_option_all'] = '';
            }
           
            $dispalydata .= wp_dropdown_categories( $args );
        }
                
        if($postfield == 'feature' && $term = get_term_by('slug', $atts['slug'], 'features')){
            static $checkbox_id = 0;
            $checkbox_id++;

            if(!$postlabel)
                $postlabel = $term->name;

            $selected = '';
            if(is_array($_GET['features']) && in_array($term->term_id,$_GET['features']))
                $selected = ' checked ';

            $dispalydata .= '<span class="'.$atts['class'].'">';
            $dispalydata .= '<input id="feature_'.$checkbox_id.'" type="checkbox" name="features[]" '.$selected.' value="'.$term->term_id.'" >';
            $dispalydata .= '<label for="feature_'.$checkbox_id.'">'.$postlabel.'</label>';
            $dispalydata .= '</span>';
        }
    
        if($postfield == 'bedrooms_min'){
            $postdata = explode(',', $postdata);
            //echo '<pre>';
                //print_r($postdata); 
            $dispalydata .='<select name="bedrooms_min" class="bedlist">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $v){
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;

                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
        if($postfield == 'bathrooms_min'){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select name="bathrooms_min" class="bathlist">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $v){
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
        if($postfield == 'build_size_min' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select id="build_size_min" name="build_size_min">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
        if($postfield == 'build_size_max' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select id="build_size_max" name="build_size_max">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
        if($postfield == 'plot_size_min' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select id="plot_size_min" name="plot_size_min">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
        if($postfield == 'plot_size_max' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select id="plot_size_max" name="plot_size_max">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
        if($postfield == 'terrace_size_min' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select id="terrace_size_min" name="terrace_size_min">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
        if($postfield == 'terrace_size_max' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select id="terrace_size_max" name="terrace_size_max">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
        if($postfield == 'list_price_min' && !empty($postdata)){
            $postdata = explode(',', $postdata);
			$x= 1;
			$length = count($postdata);
            $dispalydata .='<select id="list_price_min" name="list_price_min">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
				
                $v = number_format($k, 0, '',$atts['thousands']);
				if($x === $length){
                 $v = $v.'+'; 
                }
				else{$v = $v;}
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.sprintf($atts['format'],$v).'</option>';
				$x++;
            }
            $dispalydata .='</select>';
        }   
        if($postfield == 'list_price_max' && !empty($postdata)){
            $postdata = explode(',', $postdata);
			$x= 1;
			$length = count($postdata);
            $dispalydata .='<select id="list_price_max" name="list_price_max">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
				
                $v = number_format($k, 0, '',$atts['thousands']);
				if($x === $length){
                 $v = $v.'+'; 
                }
				else{$v = $v;}
				
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.sprintf($atts['format'],$v).'</option>';
				$x++;
            }
			
            $dispalydata .='</select>';
        }
        if($postfield == 'list_price_min_ajax' && !empty($postdata)){
            $postdata = explode(',', $postdata);
			$x= 1;
			$length = count($postdata);
            $dispalydata .='<option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
				if($x === $length){
                 $v = $v.'+'; 
                }
				else{$v = $v;}
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.sprintf($atts['format'],$v).'</option>';
				$x++;
            }
        }
      if($postfield == 'list_price_max_ajax' && !empty($postdata)){
            $postdata = explode(',', $postdata);
			$x= 1;
			$length = count($postdata);
            $dispalydata .='<option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
				if($x === $length){
                 $v = $v.'+'; 
                }
				else{$v = $v;}
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.sprintf($atts['format'],$v).'</option>';
				$x++;
            }
        }
        if($postfield == 'order' && !empty($postdata))
        {
            $postdata = explode(',', $postdata);
            $dispalydata .='<select name="'.$postfield.'">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $v)
            {
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;

                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
        if($postfield == 'reset'){
            $dispalydata .= '<input type="reset" value="'.$atts['label'].'" class="'.$atts['class'].' inmoReset" />';
        }
    
        if($postfield == 'submit'){
            $dispalydata .= '<input type="submit" id="submit" data-label="'.$atts['label'].'" value="'.$atts['label'].'" class="'.$atts['class'].'inmoSubmit" />';

            
        }
         $dispalydata.= '<input type="hidden" class="slug" value="search"/>';
        $dispalydata .= $script ;
        

        return $dispalydata;

    }

    private static function getDictionary(){
        return array(
            'area' => __('Area','inmolink'),
            'sublocation' => __('Sublocation','inmolink'),
            'n_selected' => __('#selected','inmolink'),
            'n_selected' => __('#selected','inmolink'),
            'loading' => __('Loading ...','inmolink'),
            'ajaxurl' => admin_url('admin-ajax.php')
        );
    }
 

       public function shortcode($atts)
	{
		$defaults = array(
			'levels'  => 1,
			'id'      => 'search_location',
			'class'   => 'search_location',
			'label'   => '',
			'parent'  => ''
		);

		$atts = shortcode_atts($defaults, $atts);
		$dictionary = self::getDictionary();
		$levels = $atts['levels'];
		$fid = $atts['id'];
		$class = $atts['class'];
		$label = $atts['label'];
		$parent_slug = $atts['parent'];
		$multiple = in_array('multiselect',$atts) ? ' multiple ' : '';

		if($parent_slug && $parent = get_term_by('slug',$parent_slug,'location')){
			$parent_id = $parent->term_id;
		}
		else{
			$parent_id = 0;
		}

	    $return = '';

		$return .= '<select multiple class="hidden" style="display:none" name="location[]" />';

		$selectedArea = '';
		if($parent_slug && $parents = get_term_by('slug',$parent_slug,'locations')){
			$selectedArea = $parents->term_id;
			$return .= '<option value="'.$parents->term_id.'" selected>'.$parents->term_id.'</option>';
		}

		if(isset($_GET['location'])){
			foreach($_GET['location'] as $v){
				$return .= '<option value="'.$v.'" selected>'.$v.'</option>';
			}
		}

		$return .= '</select>';
		
		for($i=1 ; $i<=$levels ; $i++)
		{
			$name = $fid.'_'.$i;
			$id = $fid.'_'.$i;
			$class_i = $class . ' ' .$class .'_'.$i;
			if (function_exists('pll_current_language')){
         $langs =pll_current_language();}else {$langs = 'en'; }
         global $wpdb;
        $tablename = $wpdb->prefix.'Inmolink_property_detail';
        $result =   $wpdb->get_results("SELECT area,sublocation FROM $tablename where lang_slug = '$langs'"); 
        foreach($result as $results){
            if($lang == $results->lang_slug){
            $area[] = $results->area;
			$sublocation[] = $results->sublocation;
        }
        }
			$areas = array_unique($area);
			$sublocations = array_unique($sublocation);
        $dictionary['area'] = implode(',',$areas);
		$dictionary['sublocation'] = implode(',',$sublocations);
			
			if($i == 1 || $parent_id = $_GET[$fid.'_'.($i-1)])
			{
				$show_option_none = ($i==1) ? $dictionary['area'] : $dictionary['sublocation'];
				//ARJUN 
				$return.= wp_dropdown_categories(array(
					'taxonomy'			=> 'locations',
					'hierarchical'		=> true,
					'depth'				=> (int)($i!=$levels), //depth 0 will display the whole rest of the tree if reached final location
					'name'				=> $name,
					'orderby'			=> 'name',
					'order'				=> 'ASC',
					'class'				=> $class_i,
					'hide_empty'		=> false,
					'selected'			=> isset($_REQUEST[$name]) ? $_REQUEST[$name] : $selectedArea,
					'echo'				=> false,
					'show_option_none'	=> $show_option_none,
					'option_none_value'	=> /* isset($_GET[$fid.'_'.($i-1)]) ? $_GET[$fid.'_'.($i-1)] : ''*/ '0' ,
					'child_of'			=> $parent_id,
					'value_field'		=> 'term_id',
					'multiple'			=> ($i == $levels), //send true for multiselect else - ($i == $levels)
				));
			}
			elseif($i == $levels && $selectedArea !='')
			{
				$show_option_none = ($i==1) ? $dictionary['area'] : $dictionary['sublocation'];
				//ARJUN 
				$return.= wp_dropdown_categories(array(
					'taxonomy'			=> 'locations',
					'hierarchical'		=> true,
					'depth'				=> (int)($i!=$levels), //depth 0 will display the whole rest of the tree if reached final location
					'name'				=> $name,
					'orderby'			=> 'name',
					'order'				=> 'ASC',
					'class'				=> $class_i,
					'hide_empty'		=> false,
					'selected'			=> isset($_REQUEST[$name]) ? $_REQUEST[$name] : $selectedArea,
					'echo'				=> false,
					'show_option_none'	=>$show_option_none,
					'option_none_value'	=> /* isset($_GET[$fid.'_'.($i-1)]) ? $_GET[$fid.'_'.($i-1)] : ''*/ '0' ,
					'child_of'			=> $selectedArea,
					'value_field'		=> 'term_id',
					'multiple'			=> ($i == $levels), //send true for multiselect else - ($i == $levels)
				));
			}
			elseif($i == $levels){
				$return.= '<select multiple name="'.$name.'[]" id="'.$name.'" class="'.$class_i.'"  ><option value="0">'.$dictionary['sublocation'].'</option></select>';
			}
			else{
				$return.= '<select name="'.$name.'" id="'.$name.'" class="'.$class_i.'"  ><option value="">'.$dictionary['sublocation'].'</option></select>';
			}
			//$parent_id = $selectedArea;
		}
		return $return ;
	}

    public function ajax($data)
    {
        $parent_id = (int)$_POST['parent'];
        $args = array(
            'taxonomy'          => 'locations',
            'orderby'           => 'name',
            'order'             => 'ASC',
            'name'              => '',
            'class'             => '',
            'hierarchical'      => true,
            'depth'             => (int)$_POST['depth'], 
            'hide_empty'        => false,
            'child_of'          => (int)$parent_id,
            'value_field'       => 'term_id'
        );
        wp_dropdown_categories($args);
        exit;
    }
    public function inmolink_feature_checkboxes_field($atts){
    $defaults = array(
        'id'            => 'search_feature',
        'class'         => 'search_feature',
        'label'         => '',
        'category'      => '',
    );

    $atts = wp_parse_args( $atts, $defaults );
    $fid = $atts['id'];
    $class = $atts['class'] ;
    $label = $atts['label'] ;
    $parent_slug = $atts['category'];

    if($parent_slug && $parent = get_term_by('slug',$parent_slug,'features')){
        $parent_id = $parent->term_id;
    }
    else{
        $parent_id = 0; 
    }
     
    $return = '';
//  $return .= '<div class="advance-search">';
//  $return .= '<label class="title-search">Advance Search</label>';
//  $return .= '<div class="search-detail" style="display: none;">';
//  $return .=  '<h3 class="feature-title">Features1234</h3>';
    $return.= '<div class="advance__search">';
    $terms = get_terms(
        array(
            'taxonomy' => 'features',
            'hide_empty' => false ,
            'parent' => $parent_id
        )
    );

    foreach($terms as $term){
        if(isset($_GET['features']) && is_array($_GET['features']) && in_array($term->term_id,$_GET['features'])){
            $checked = ' checked="checked" ';
        }
        else{
            $checked = '';
        }

        static $checkbox_id = 0;

        $return .= '<details class="feature_'.$term->slug.'"><span class="expand"></span><summary>';
        if($parent_id != 0){
            $checkbox_id++;
            $return .= '<input type="checkbox" id="feature_checkbox_'.$checkbox_id.'" name="features['.$term->term_id.']" value="'.$term->term_id.'" '.$checked.'>';
        }

        $return .= '<label for="feature_checkbox_'.$checkbox_id.'">'.$term->name.'</label></summary>';

        if($parent_id == 0){
            $terms_2 = get_terms(
                array(
                    'taxonomy' => 'features',
                    'hide_empty' => false ,
                    'parent' => $term->term_id
                )
            );

            $return.= '<ul>';

            foreach($terms_2 as $term_2)
            {
                $checkbox_id++;
                $checked = (isset($_GET['features']) && is_array($_GET['features']) && in_array($term_2->term_id,$_GET['features'])) ? ' checked="checked" ' : '';
                $return .= '<li class="feature_'.$term_2->slug.'">';
                $return .= '<input id="feature_checkbox_'.$checkbox_id.'" type="checkbox" name="features['.$term_2->term_id.']" value="'.$term_2->term_id.'"'.$checked.'>';
                $return .= '<label for="feature_checkbox_'.$checkbox_id.'">'.$term_2->name.'</label>';
                $return .= '</li>';
            }
            $return .= '</ul>';
        }
        $return .= '</details>';
    }

    $return .= '</div>';
//  $return .= '</div>';
//  $return .= '</div>';
    return $return ;
}
}
?>