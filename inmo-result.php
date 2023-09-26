<?php

/**
 * 
 */
add_action("wp_ajax_GetSearchStatus",'GetSearchStatus');
add_action("wp_ajax_nopriv_GetSearchStatus",'GetSearchStatus');
function GetSearchStatus(){
	$Destination = $_POST['Destination'];
	$locaton_parent = $_POST['locaton_parent'];
	$sub_location = $_POST['locaton_child'];
	$type= $_POST['type'];
	$Bed=$_POST['Bed'];
	$Bath=$_POST['Bath'];
	$Status=$_POST['Status'];
	$MinPrice = $_POST['MinPrice'];
	$MaxPrice=$_POST['MaxPrice'];
	$features= $_POST['Features'];
	$array = array('destination'=>$Destination,'location'=>$locaton_parent,'childlocation' =>$sub_location,'type'=>$type,'bed'=>$Bed,'bath'=>$Bath,'status'=>$Status,'minprice'=>$MinPrice,'maxprice'=>$MaxPrice,'features'=>$features);
    $encrypt_result=base64_encode(serialize($array));
    }
	
add_action("wp_ajax_GetChildResponse",  'GetChildResponse');
add_action("wp_ajax_nopriv_GetChildResponse", 'GetChildResponse');
function GetChildResponse(){
  $parentid= $_POST['parentid'];
	foreach( get_terms( 'locations', array( 'hide_empty' => false, 'parent' => $parentid ) ) as $child_term ) 
        {?>
		
         <option value="<?php echo $child_term->term_id;?>"> <?php echo $child_term->name; ?></option>
        <?php }
		
	}
add_action("wp_ajax_GetInstantdResponse", 'GetInstantdResponse');
add_action("wp_ajax_nopriv_GetInstantdResponse",'GetInstantdResponse');
function GetInstantdResponse(){
	 parse_str($_POST['get_args'], $_GET);
	 if(isset($_POST['priceToggle'])){
		 global $wpdb;
		 if (function_exists('pll_current_language')){
         $lang =pll_current_language();}else {$lang = 'en'; }
         if(!empty($lang)){$langs = $lang;}else{$langs = 'en';}
		 $tablename = $wpdb->prefix.'Inmolink_property_search';
	     $result	=	$wpdb->get_results("SELECT * FROM $tablename");  
	       foreach($result as $row){
            $lang_slug = $row->lang_slug;
	        $sale_price = $row->sale_price;
	        $rent_price = $row->rent_price;
	        $holiday_price = $row->holiday_price;
			 if ($langs == $lang_slug ) {
				 $label_8 = $row->min_price;
			     $label_9 = $row->max_price;
				 $status = $_POST['status'];
				 $getStatus = new InmoLinkSearch();
				 if($status == 'resale'){ $getPriceOf = $sale_price; }
                elseif ($status == 'development'){ $getPriceOf = $sale_price; }
                elseif ($status == 'long_rental'){ $getPriceOf = $rent_price; }
                elseif ($status == 'short_rental'){ $getPriceOf = $holiday_price; }
                elseif ($status == 0){ $getPriceOf = $sale_price; }
                $list_price_min = array(
                  'field'=>"list_price_min_ajax",
                  'label'=>$label_8,
                  'data'=>$getPriceOf,
                  'format'=>"€%s",
                  'thousands'=>","
                );

                $list_price_max = array(
                  'field'=>"list_price_max_ajax",
                  'label'=>$label_9,
                  'data'=>$getPriceOf,
                  'format'=>"€%s",
                  'thousands'=>","
                );

                $pricesToggled = array(
                'list_price_min' => $getStatus->field($list_price_min),
                'list_price_max' => $getStatus->field($list_price_max)
                );
                  echo json_encode($pricesToggled);
			 }
		   }
           
         wp_die();  
  }
  else{
	 
    $results = new InmoLinkResults();
    $args = $results->parseGetParams();
    $args = array_filter($args);
	$results->fetch_properties($args);
    $stats = array(
      'count' => $results->count,
      'params' => $args,
      'get' => $_GET
    );
    echo json_encode($stats);
    wp_die();
}
}
add_action("wp_ajax_GetMapResponse", 'GetMapResponse');
add_action("wp_ajax_nopriv_GetMapResponse",'GetMapResponse');
function GetMapResponse(){
    $id= $_POST['term_id'];
	$a =array("sale"=>"ref_no=&location%5B%5D=$id&search_location_1=$id&bedrooms_min=&bathrooms_min=&list_price_min=&list_price_max=&listing_type=resale","rent"=>"ref_no=&location%5B%5D=$id&search_location_1=$id&bedrooms_min=&bathrooms_min=&list_price_min=&list_price_max=&listing_type=long_rental","holiday"=>"ref_no=&location%5B%5D=$id&search_location_1=$id&bedrooms_min=&bathrooms_min=&list_price_min=&list_price_max=&listing_type=short_rental");
	foreach($a as $a){
	 parse_str($a, $_GET);
	 $results = new InmoLinkResults();
     $args = $results->parseGetParams();
	 $args = array_filter($args);
	 $results->fetch_properties($args);
	 $array[]=$results->count;
	 }
	 $stats = array(
      'sale' => $array[0],
      'rent_lt' =>$array[1],
      'rent_st' => $array[2]
     );
    echo json_encode($stats);
    wp_die();

}
?>