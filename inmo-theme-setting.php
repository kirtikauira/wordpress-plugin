<?php
require_once(dirname(__FILE__).'/inmo-theme-setting-db.php');
add_action("wp_ajax_SaveDataRow",  'SaveDataRow');
add_action("wp_ajax_nopriv_SaveDataRow", 'SaveDataRow');
add_action( 'admin_menu', 'inmolink_menu_page' );
function inmolink_menu_page() {

	add_menu_page(
		'InmoTech Settings', 
		'InmoTech Settings', 
		'edit_posts', 
		'inmolink-theme-settings', 
		'inmolink_theme_settings_content',
		'dashicons-location-alt', 
		59 
	);
	add_submenu_page(
		'inmolink-theme-settings', 
		'InmoTech Footer Settings', 
		'InmoTech Footer Settings',
		'edit_posts',
		'inmolink-footer-settings', 
		'inmolink_footer_settings_content'
	);
	add_submenu_page(
		'inmolink-theme-settings', 
		'InmoTech Property Listing', 
		'InmoTech Property Listing',
		'edit_posts',
		'inmolink-property-listing', 
		'inmolink_property_listing_content'
	);
	add_submenu_page(
		'inmolink-theme-settings', 
		'InmoTech Property Search', 
		'InmoTech Property Search',
		'edit_posts',
		'inmolink-property-search', 
		'inmolink_property_search_content',
	);
	add_submenu_page(
		'inmolink-theme-settings', 
		'InmoTech Property Detail', 
		'InmoTech Property Detail',
		'edit_posts',
		'inmolink-property-detail', 
		'inmolink_property_detail_content',
	);


}

function inmolink_theme_settings_content(){
global $wpdb;
	$create_sql = '';
    $table_name = $wpdb->prefix.'Inmolink_theme_setting';
	if( $wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") !=  $table_name)
    { 
      $create_sql ="CREATE TABLE $table_name (
       id INT(11) AUTO_INCREMENT,
	   lang_slug VARCHAR (250),
	   button_url VARCHAR (250),
	   button_text VARCHAR (250),
	   property_sale VARCHAR (250),
	   property_rent VARCHAR (250),
	   holiday_homes VARCHAR (250),
	   no_property_sale VARCHAR (250),
	   no_property_rent VARCHAR (250),
	   no_property_holiday_homes VARCHAR (250),
	   PRIMARY KEY (id)
	    )";}
	require_once(ABSPATH . "wp-admin/includes/upgrade.php");
	dbDelta( $create_sql);?>
<div class="wrap inmolink-settings-wrap">
<form id="post" method="post" name="post" action="">
	  <div class="property_favourite">
	 <h3>Favorite Button & Map Child Marker Text</h3>
	    <table>
        <tr>
            <th width="20">Sr#</th>
			<th>Lang Slug</th>
			<th>Button URL</th>
			<th>Button Text (HTML)</th>
			<th>Property sale</th>
			<th>Property rent</th>
			<th>Holiday homes</th>
			<th>No Property Messege For Sale</th>
			<th>No Property Messege For Rent</th>
			<th>No Property Messege For Holiday homes</th>
		</tr>
        <tbody id="tbodySetting">
		 
		<?php 
			$result	= $wpdb->get_results("SELECT * FROM $table_name  ORDER BY id ASC");
			if(count($result)>0){
			$s	= '';
			foreach($result as $row){
			    $s++;  ?>
			   <form id=""  method="post">
				<tr>
				<td class="control"><?php echo $s;?></td>
				<td><input type="text" name="lang_slug" value="<?php echo $row->lang_slug; ?>"/></td>
				<td><input type="text" name="button_url" value="<?php echo $row->button_url; ?>"/></td>
				<td><input type="text" name="button_text" value='<?php echo $row->button_text; ?>'/></td>
				<td><input type="text" name="property_sale" value='<?php echo $row->property_sale; ?>'/></td>
				<td><input type="text" name="property_rent" value='<?php echo $row->property_rent; ?>'/></td>
				<td><input type="text" name="holiday_homes" value='<?php echo $row->holiday_homes; ?>'/></td>
				<td><input type="text" name="no_property_sale" value='<?php echo $row->no_property_sale; ?>'/></td>
				<td><input type="text" name="no_property_rent" value='<?php echo $row->no_property_rent; ?>'/></td>
				<td><input type="text" name="no_property_holiday_homes" value='<?php echo $row->no_property_holiday_homes; ?>'/></td>
				<input type="hidden" name="getid" id='series' value="<?php echo $row->id; ?>">

				<td><input type="submit" id= "deleteControl" name="deleteeControl" value="DELETE" class="delete" data-id=<?php echo $row->id;?> ></td>
				<td><input type="submit" id= "editControl" name="editControl" value="EDIT" data-id=<?php echo $row->id;?> ></td>
				</tr>
			    </form>
				<?php }
			} ?>
		
		</tbody>
    </table>
    <button type="button" id="ThemeSetting">Add New Language</button>
    <input type="submit" name="ThemeSetting"  value="Update">
	</div>
   </form>
   </div>
<?php }
//-----------------------inmolink_footer_settings
function inmolink_footer_settings_content(){
	global $wpdb;
	$create_sql = '';
    $table_name = $wpdb->prefix.'Inmolink_footer_settings';
	if( $wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") !=  $table_name)
    { 
      $create_sql ="CREATE TABLE $table_name (
       id INT(11) AUTO_INCREMENT,
       lang_slug VARCHAR (250) ,
	   properties_qty VARCHAR (250),
	   properties_order VARCHAR (250),
	   properties_filter VARCHAR (250),
	   property_url VARCHAR (250),
	   copyright VARCHAR (250),
	   PRIMARY KEY (id)
	    )";}
	require_once(ABSPATH . "wp-admin/includes/upgrade.php");
	dbDelta( $create_sql);
		?>
	<form method="post" name="post" action="">
    <h2>Inmolink Footer Settings</h2>
    <h4>Inmolink Footer Settings</h4>
    <div class="footer_settings">

    <table>
        <tr>
            <th width="20">Sr#</th>
			<th>Polylang Slug</th>
			<th>Qty Properties</th>
			<th>Property Filter</th>
			<th>Property URL</th>
		</tr>
        <tbody id="tbodyfootersettings">
		<?php 
			$result	=	$wpdb->get_results("SELECT * FROM $table_name  ORDER BY id ASC");
			//echo '<pre>';
			//print_r ($result);					
			if(count($result)>0){
			$s	= '';
			foreach($result as $row){
			    $s++;  ?>  
                <form id=""  method="post">
			    <tr>
				<td class="set"><?php echo $s;?></td>
				<td><input type="text"  name="lang_slug" value="<?php echo $row->lang_slug; ?>"/></td>
				<td><input type="text" name="properties_qty" value="<?php echo $row->properties_qty; ?>"/></td>			
				<td><input type="text" name="properties_filter" value="<?php echo $row->properties_filter; ?>"/></td>
				<td><input type="text" name="property_url" value="<?php echo $row->property_url; ?>"/></td>
				<input type="hidden" name="getid" id='series' value="<?php echo $row->id; ?>">

				<td><input type="submit" id= "deleteset" name="deleteeset" value="DELETE" class="delete" data-id=<?php echo $row->id;?> ></td>
                <td><input type="submit" id= "editset" name="edittset" value="EDIT" data-id=<?php echo $row->id;?> ></td>
				</tr>
                </form>
				<?php 

			          } 
		              } ?>
		</tbody>
    </table>

    <button type="button" id="addset">Add New Language</button>
    <input type="submit" name="addset"  value="Update">
   </div></form>
</div>
    <?php }
//-----------------------inmolink_property_listing
function inmolink_property_listing_content(){
	global $wpdb;
	$create_sql = '';
    $table_name = $wpdb->prefix.'Inmolink_property_listing';
	if( $wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") !=  $table_name)
    { 
      $create_sql ="CREATE TABLE $table_name (
       id INT(11) AUTO_INCREMENT,
       lang_slug VARCHAR (250) ,
       read_more_button VARCHAR (250),
	   properties_qty VARCHAR (250),
	   detail_page_url VARCHAR (250),
	   from_list VARCHAR (250),
	   per_month VARCHAR (250),
	   per_week VARCHAR (250),
	   PRIMARY KEY (id)
	    )";}
	require_once(ABSPATH . "wp-admin/includes/upgrade.php");
	dbDelta( $create_sql);
		?>
	<form method="post" name="post" action="">
    <h2>Property Listing</h2>
    <h4>Property Listing</h4>
    <div class="property_listing">

    <table>
        <tr>
            <th width="20">Sr#</th>
			<th>Polylang Slug</th>
			<th>Read More Button Text</th>
			<th>Properties Qty per page</th>
			<th>Detail Page URL</th>
			<th>From</th>
			<th>Per Month</th>
			<th>Per Week</th>
		</tr>
        <tbody id="tbodyList">
		<?php 
			$result	=	$wpdb->get_results("SELECT * FROM $table_name  ORDER BY id ASC");
			//echo '<pre>';
			//print_r ($result);					
			if(count($result)>0){
			$s	= '';
			foreach($result as $row){
			    $s++;  ?>  
                <form id=""  method="post">
			    <tr>
				<td class="list"><?php echo $s;?></td>
				<td><input type="text"  name="lang_slug" value="<?php echo $row->lang_slug; ?>"/></td>
				<td><input type="text" name="read_more_button" value="<?php echo $row->read_more_button; ?>"/></td>
				<td><input type="text" name="properties_qty" value="<?php echo $row->properties_qty; ?>"/></td>
				<td><input type="text" name="detail_page_url" value="<?php echo $row->detail_page_url; ?>"/></td>
				<td><input type="text" name="from_list" value="<?php echo $row->from_list; ?>"/></td>
				<td><input type="text" name="per_month" value="<?php echo $row->per_month; ?>"/></td>
				<td><input type="text" name="per_week" value="<?php echo $row->per_week; ?>"/></td>
				<input type="hidden" name="getid" id='series' value="<?php echo $row->id; ?>">

				<td><input type="submit" id= "deletelist" name="deleteelist" value="DELETE" class="delete" data-id=<?php echo $row->id;?> ></td>
                <td><input type="submit" id= "editlist" name="editlist" value="EDIT" data-id=<?php echo $row->id;?> ></td>
				</tr>
                </form>
				<?php 

			          } 
		              } ?>
		</tbody>
    </table>

    <button type="button" id="addListing">Add New Language</button>
    <input type="submit" name="addListing"  value="Update">
   </div></form>
</div>
    <?php }
//-----------------------inmolink_property_search    
function inmolink_property_search_content(){
	 global $wpdb;
	$create_sql = '';
    $table_name = $wpdb->prefix.'Inmolink_property_search';
	$sql_search ="CREATE TABLE $table_name (
       id INT(11) AUTO_INCREMENT,
       lang_slug VARCHAR (250) ,
       close_button VARCHAR (250) ,
       tab_sale VARCHAR (250),
       tab_rent_url VARCHAR (250),
       tab_dev VARCHAR (250),
       result_page_url VARCHAR (250),
       more_button VARCHAR (250),
       tab_sale_url VARCHAR (250),
       tab_holiday VARCHAR (250),
	   tab_holiday_url VARCHAR (250),
       tab_dev_url VARCHAR (250),
       order_button VARCHAR (250),
	   less_button VARCHAR (250),
	   tab_rent VARCHAR (250),
	   order_drop_down VARCHAR (250),
	   advanced_search VARCHAR (250),
	   sale_price VARCHAR (250),
	   not_found_message VARCHAR (250),
	   label_status_dropdown VARCHAR (250),
	   map_search VARCHAR (250),
	   rent_price VARCHAR (250),
	   no_property_url VARCHAR (250),
	   features_heading VARCHAR (250),
	   map_search_url VARCHAR (250),
	   holiday_price VARCHAR (250),
	   ref VARCHAR (250),
	   location VARCHAR (250),
	   sublocation VARCHAR (250),
	   property_type VARCHAR (250),
	   status VARCHAR (250),
	   bed VARCHAR (250),
	   bath VARCHAR (250),
	   price VARCHAR (250),
	   min_price VARCHAR (250),
	   max_price VARCHAR (250),
	   submit VARCHAR (250),
	   reset VARCHAR (250),
	   min_build VARCHAR (250),
	   max_build VARCHAR (250),
	   min_plot VARCHAR (250),
	   max_plot VARCHAR (250),
	   min_terrace VARCHAR (250),
	   max_terrace VARCHAR (250),
	   build_size_value VARCHAR (250),
	   plot_size_value VARCHAR (250),
	   terrace_size_value VARCHAR (250),
       PRIMARY KEY (id)
	    )";
	require_once(ABSPATH . "wp-admin/includes/upgrade.php");
	 dbDelta( $sql_search);
?>
<div class="property_search">		
<h1>Property Search</h1>

<h2>Property Search</h2>
<!--start of 1st section-->
<form method="post" name="post">
    <table>
        <tr >
        	<th>Sr#</th>
			<th>Lang Slug</th>
			<th>Result Page URL</th>
			<th>Order Button</th>
			<th>Close Button</th>
			<th>More Button</th>
			<th>Less Button</th>
			<th>Tab Sale</th>
			<th>Tab Sale URL</th>
			<th>Tab Rent</th>
			<th>Tab Rent URL</th>
			<th>Tab Holiday</th>
			<th>Tab Holiday URL</th>
			<th>Tab Dev</th>
			<th>Tab Dev URL</th>
			<th>Order Drop Down</th>
			<th>Label Status Dropdown</th>
			<th>Features Heading</th>
			<th>Advanced Search</th>
			<th>Map Search</th>
			<th>Map Search URL</th>
			<th>Sale Price</th>
			<th>Rent Price</th>
			<th>Holiday Price</th>
			<th>Not Found Message</th>
			<th>404 No Propert Found Page URL</th>
			<th>Ref</th>
            <th>Location</th>
            <th>Sublocation</th>
            <th>Property Type</th>
            <th>Status</th>
            <th>Bed</th>
            <th>Bath</th>
            <th>Price</th>
            <th>Min Price</th> 
            <th>Max Price</th> 
            <th>Submit</th> 
            <th>Reset</th>
            <th>Min Build</th>
            <th>Max Build</th>
            <th>Min Plot</th>
            <th>Max Plot</th>
            <th>Min Terrace</th>
            <th>Max Terrace</th>
            <th>Build size value</th>
            <th>Plot size value</th>
            <th>Terrace size value</th> 
        </tr>

        <tbody id="tbodySearch">
		<?php 
			$result	=$wpdb->get_results("SELECT * FROM $table_name  ORDER BY id ASC");
			if(count($result)>0){
			$s	= '';
			foreach($result as $row){
			    $s ++;  ?>
			    <form id=""  method="post"> 
			    <tr>
				<td class='product'><?php echo $s;?></td>	
				<td><input type="text" name="lang_slug" value="<?php echo $row->lang_slug; ?>"/></td>
				<td><input type="text" name="result_page_url" value="<?php echo $row->result_page_url; ?>"/></td>
				<td><input type="text" name="order_button" value="<?php echo $row->order_button; ?>"/></td>
				<td><input type="text" name="close_button" value="<?php echo $row->close_button; ?>"/></td>
				<td><input type="text" name="more_button" value="<?php echo $row->more_button; ?>"/></td>
				<td><input type="text" name="less_button" value="<?php echo $row->less_button; ?>"/></td>
				<td><input type="text" name="tab_sale" value="<?php echo $row->tab_sale; ?>"/></td>
				<td><input type="text" name="tab_sale_url" value="<?php echo $row->tab_sale_url; ?>"/></td>
				<td><input type="text" name="tab_rent" value="<?php echo $row->tab_rent; ?>"/></td>	
				<td><input type="text" name="tab_rent_url" value="<?php echo $row->tab_rent_url; ?>"/></td>
				<td><input type="text" name="tab_holiday" value="<?php echo $row->tab_holiday; ?>"/></td>
				<td><input type="text" name="tab_holiday_url" value="<?php echo $row->tab_holiday_url; ?>"/></td>
				<td><input type="text" name="tab_dev" value="<?php echo $row->tab_dev; ?>"/></td>
				<td><input type="text" name="tab_dev_url" value="<?php echo $row->tab_dev_url; ?>"/></td>
				<td><input type="text" name="order_drop_down" value="<?php echo $row->order_drop_down; ?>"/></td>
				<td><input type="text" name="label_status_dropdown" value="<?php echo $row->label_status_dropdown; ?>"/></td>
				<td><input type="text" name="features_heading" value="<?php echo $row->features_heading; ?>"/></td>
				<td><input type="text" name="advanced_search" value="<?php echo $row->advanced_search; ?>"/></td>
				<td><input type="text" name="map_search" value="<?php echo $row->map_search; ?>"/></td>
				<td><input type="text" name="map_search_url" value="<?php echo $row->map_search_url; ?>"/></td>
				<td><input type="text" name="sale_price" value="<?php echo $row->sale_price; ?>"/></td>
				<td><input type="text" name="rent_price" value="<?php echo $row->rent_price; ?>"/></td>
				<td><input type="text" name="holiday_price" value="<?php echo $row->holiday_price; ?>"/></td>
				<td><input type="text" name="not_found_message" value="<?php echo $row->not_found_message; ?>"/></td>
				<td><input type="text" name="no_property_url" value="<?php echo $row->no_property_url; ?>"/></td>
				<td><input type="text" name="ref" value="<?php echo $row->ref; ?>"/></td>
                <td><input type="text" name="location" value="<?php echo $row->location; ?>"/></td>
                <td><input type="text" name="sublocation" value="<?php echo $row->sublocation; ?>"/></td>
                <td><input type="text" name="property_type" value="<?php echo $row->property_type; ?>"/></td>
                <td><input type="text" name="status" value="<?php echo $row->status; ?>"/></td>
                <td><input type="text" name="bed" value="<?php echo $row->bed; ?>"/></td>
                <td><input type="text" name="bath" value="<?php echo $row->bath; ?>"/></td>
                <td><input type="text" name="price" value="<?php echo $row->price; ?>"/></td>
                <td><input type="text" name="min_price" value="<?php echo $row->min_price; ?>"/></td>
                <td><input type="text" name="max_price" value="<?php echo $row->max_price; ?>"/></td>
                <td><input type="text" name="submit" value="<?php echo $row->submit; ?>"/></td>
                <td><input type="text" name="reset" value="<?php echo $row->reset; ?>"/></td>
                <td><input type="text" name="min_build" value="<?php echo $row->min_build; ?>"/></td>
                <td><input type="text" name="max_build" value="<?php echo $row->max_build; ?>"/></td>
                <td><input type="text" name="min_plot" value="<?php echo $row->min_plot; ?>"/></td>
                <td><input type="text" name="max_plot" value="<?php echo $row->max_plot; ?>"/></td>
                <td><input type="text" name="min_terrace" value="<?php echo $row->min_terrace; ?>"/></td>
                <td><input type="text" name="max_terrace" value="<?php echo $row->max_terrace; ?>"/></td>
                <td><input type="text" name="build_size_value" value="<?php echo $row->build_size_value; ?>"/></td>
                <td><input type="text" name="plot_size_value" value="<?php echo $row->plot_size_value; ?>"/></td>
                <td><input type="text" name="terrace_size_value" value="<?php echo $row->terrace_size_value; ?>"/></td>
				<input type="hidden" name="gettid" id='series' value="<?php echo $row->id; ?>">	
                <td><input type="submit" id= "deletesearch" name="deleteesearch" value="DELETE" class="delete" data-id=<?php echo $row->id;?> ></td>
                <td><input type="submit" id= "<?php echo $row->id;?>" name="editsearch" value="EDIT" id=<?php echo $row->id;?> ></td>
			    </tr> 
			     </form>
             <?php }
			        } ?>
			        
            </tbody></table>
            <button type="button" id="addSearching">Add New Language</button>&nbsp;
            <input type="submit" name="addSearching"  value="Update"> 
</table>
<!--end of labels-->                                      
     
  </form>
</div>  	
<?php }
//-----------------------inmolink_property_detail
function inmolink_property_detail_content(){
 global $wpdb;
	$create_sql = '';
    $table_name = $wpdb->prefix.'Inmolink_property_detail';
	if( $wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") !=  $table_name)
    { 
      $create_sql ="CREATE TABLE $table_name (
       id INT(11) AUTO_INCREMENT,
       lang_slug VARCHAR (250) ,
       title VARCHAR (250),
	   ref_no VARCHAR (250),
	   price VARCHAR (250),
	   location VARCHAR (250),
	   sublocation VARCHAR (250),
	   area VARCHAR (250),
	   country VARCHAR (250),
	   property_type VARCHAR (250),
	   bedrooms VARCHAR (250),
	   bathrooms VARCHAR (250),
	   plot_size VARCHAR (250),
	   living_area VARCHAR (250),
	   terrace VARCHAR (250),
	   PRIMARY KEY (id)
	    )";}
	require_once(ABSPATH . "wp-admin/includes/upgrade.php");
	dbDelta( $create_sql);
		?>
    <h2>Property Detail</h2>
    <h4>Single Property Highlights Labels</h4>
     <div class="property_detail">
    <form method="POST" id="addList">
    <table>
        <tr>
            <th>Sr#</th>
			<th>Lang Slug</th>
			<th>Title</th>
			<th>Ref No</th>
			<th>Price</th>
			<th>Location</th>
			<th>Sublocation</th>
			<th>Area</th>
			<th>Country</th>
			<th>Property Type</th>
			<th>Bedrooms</th>
			<th>Bathrooms</th>
			<th>Plot Size</th>
			<th>Living Area</th>
			<th>Terrace</th>
        </tr>
        <tbody id="tbody">
		<?php 
			$result	=	$wpdb->get_results("SELECT * FROM $table_name  ORDER BY id ASC");
			if(count($result)>0){
			$s	= '';
			foreach($result as $row){
			    $s++;  ?>
			    <form id=""  method="post">
				<tr>
				<td class='item'><?php echo $s;?></td>
				<td><input type="text"  name="lang_slug" value="<?php echo $row->lang_slug; ?>"/></td>
				<td><input type="text"  name="title" value="<?php echo $row->title; ?>"/></td>
				<td><input type="text"  name="ref_no" value="<?php echo $row->ref_no; ?>"/></td>
				<td><input type="text"  name="price" value="<?php echo $row->price; ?>"/></td>
				<td><input type="text"  name="location" value="<?php echo $row->location; ?>"/></td>
				<td><input type="text"  name="sublocation" value="<?php echo $row->sublocation; ?>"/></td>		
				<td><input type="text"  name="area" value="<?php echo $row->area; ?>"/></td>
				<td><input type="text"  name="country" value="<?php echo $row->country; ?>"/></td>
				<td><input type="text"  name="property_type" value="<?php echo $row->property_type; ?>"/></td>
				<td><input type="text"  name="bedrooms" value="<?php echo $row->bedrooms; ?>"/></td>
				<td><input type="text"  name="bathrooms" value="<?php echo $row->bathrooms; ?>"/></td>
				<td><input type="text"  name="plot_size" value="<?php echo $row->plot_size; ?>"/></td>
				<td><input type="text"  name="living_area" value="<?php echo $row->living_area; ?>"/></td>
				<td><input type="text"  name="terrace" value="<?php echo $row->terrace; ?>"/></td>
				<input type="hidden" name="getid" value="<?php echo $row->id; ?>">

				<td><input type="submit" id= "deleteDetail" name="deleteeDetail" value="DELETE" class="delete" data-id=<?php echo $row->id;?> ></td>
				<td><input type="submit" id= "editDetail" name="editDetail" value="EDIT" data-id=<?php echo $row->id;?> >
				</td>
				</tr>
			    </form>
				<?php }
			} ?>
		</tbody>
    </table>
 
    <button type="button" id="addDetail">Add New Language</button>
    <input type="submit" name="addDetail"  value="Update">
</form>
</div>
    <?php }?>
