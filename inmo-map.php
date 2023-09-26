<?php
/**
 *
 */
require_once(INMO__PLUGIN_DIR.'Connection.php');
class InmoLinkMap
{
  static public function inmolink_property_map_list(){
    global $wpdb;
    $token = Connection::get_token();
        $base_url = Connection::get_base_url();
    $url= $base_url.'v1/property?';
    $get_googleAPI_option = get_option('inmolink_option_name');
        $get_google_api_key = $get_googleAPI_option['google_api_key'];
    $tablename = $wpdb->prefix.'Inmolink_property_search';
    $table_name =$wpdb->prefix.'Inmolink_theme_setting';
    $result_map = $wpdb->get_results("SELECT lang_slug,property_sale,property_rent,holiday_homes,no_property_sale,no_property_rent,no_property_holiday_homes FROM $table_name"); 
      $result = $wpdb->get_results("SELECT * FROM $tablename"); 

        foreach($result as $row){
        $lang_slug = $row->lang_slug;
  if (function_exists('pll_current_language')){
$lang =pll_current_language();}else {$lang = 'en'; }
        if ($lang == $lang_slug ) {
        $page_url = $row->result_page_url;
        $PageUrl= array('page_url'=>$page_url);
        }

        }
    foreach($result_map as $row){
        $lang_slug = $row->lang_slug;
        if ($lang == $lang_slug ) {
          
        $sale = $row->property_sale;
        $rent = $row->property_rent;
        $homes = $row->holiday_homes;
        $nopropsale = $row->no_property_sale;
        $noproprent = $row->no_property_rent;
        $nopropholidayhomes = $row->no_property_holiday_homes;
         
        $PageText= array('sale'=>$sale, 'rent'=>$rent,'homes'=>$homes,'nopropsale'=>$nopropsale,'noproprent'=>$noproprent,'nopropholidayhomes'=>$nopropholidayhomes );
          

        }
        //echo'<pre>';
      //print_r($result_map);
        //die();
        }
    
       ?>
     
<div id="map_wrapper">
    <div id="panel">
      <input onclick="showMarkers();" type=button value="Back" id="hidden_btn" class="hidden_btn" >
    </div>
    <div><input onclick="deleteMarkers();" type=button value="Delete Markers" id="hidden_btn" class="hidden_btn" ></div>
    <div id="map-canvas" class="mapping"></div>
</div>
<style> 

 #map-canvas {
        width: 100%;
        height: 0px;
      margin: 0px;
      padding: 0px;
      padding-bottom:60%;
    }
    #firstHeading {
    width: auto;
    height: auto;
    font-size: medium;
   }
    #panel {
      position: absolute;
      margin-left: 100px;
      z-index: 5;
      padding: 5px;
    }
    .hidden_btn{
      display:none;
    }

     </style>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $get_google_api_key; ?>" defer></script>     
<script>
	
var map;
var markers = [];
var marker_locations = [];
var marker_sub_locations = [];
var marker_child_locations = [];
var mark =
[  
<?php
foreach( get_terms( 'locations', array( 'hide_empty' => false, 'parent' => 0 ) ) as $parent_term ) {
  
      $areas=trim(preg_replace('/\s*\([^)]*\)/', '', $parent_term->name));
      $area=str_replace(' ', '%20', $areas.' , Málaga , Spain');
      $geocode=file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$area.'&key='.$get_google_api_key.'&sensor=false');
          $output= json_decode($geocode);
      $lat = $output->results[0]->geometry->location->lat;
        $lng = $output->results[0]->geometry->location->lng;
      $term_id = $parent_term->term_id;
      $slug = substr($parent_term->slug, 0, strpos($parent_term->slug, '-'));?>
{"name":"<?php echo $parent_term->name; ?>","slug":"<?php echo $parent_term->name; ?>","count":0,"lat":"<?php echo $lat; ?>","long":" <?php echo $lng; ?>","term_id":"<?php echo $term_id; ?>",
"child":[<?php
 foreach( get_terms( 'locations', array( 'hide_empty' => false, 'parent' =>      $parent_term->term_id ) ) as $child_term ) {
if($child_term->name == 'Melilla'){
$child_location=str_replace(' ', '%20',$child_term->name.',Málaga,Spain');}
else if($child_term->name == 'Benalmadena Costa'){$child_location='BenalmádenaCosta';}
else if($child_term->name == 'Benalmadena Pueblo'){$child_location='PuebloBenalmádena';}
//else if($child_term->name == 'Casares Pueblo'){$child_location='PuebloCasaresMarbella';}
else if($child_term->name == 'Monte Halcones'){$child_location=str_replace(' ', '%20',$child_term->name.',Málaga,Spain');}
else{$child_location=str_replace(' ', '%20',$child_term->name.','.$slug.',Málaga,Spain'); }
$child_termId=$child_term->term_id;

$geo="https://maps.googleapis.com/maps/api/geocode/json?address=$child_location&key=$get_google_api_key&sensor=false"; 

       $geocode=file_get_contents($geo);
            $output= json_decode($geocode);
            //echo'<pre>';
            //print_r($output);
            //die();
      if($child_term->name == 'Casares Pueblo'){
        $lat =36.4449446;
        $lng=-5.2759536;
      }
      else {
          $lat = $output->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
          $lng = $output->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
        }
      
      ?>
{"name":"<?php echo $child_term->name; ?>","slug":"<?php echo $child_term->name; ?>","count":0,"lat":"<?php echo $lat; ?>","long":" <?php echo $lng; ?>","term_id":"<?php echo $child_term->term_id; ?>"},
 <?php 
}
?>]}, 
<?php }?> ];
</script>
<script>
var image = "<?php echo plugins_url( '/images/map_icon.png', __FILE__ )?>";
var sub_image = "<?php echo plugins_url( '/images/sub_icon.png', __FILE__ )?>";
var ajaxurl = '<?php echo home_url()?>/wp-admin/admin-ajax.php';
var infoWindows = [];
function initialize() {

  var country_coord = new google.maps.LatLng(36.726181944876686, -4.4224900096202795);

  var mapOptions = {
    zoom: 7,
    //minZoom: 1,
    //maxZoom: 13,
    center: country_coord,
    mapTypeId: google.maps.MapTypeId.TERRAIN
  };
  
  map = new google.maps.Map(document.getElementById('map-canvas'),
    mapOptions);

  for( var i = 0; i < mark.length; i++ ) {

    var location = new google.maps.LatLng(mark[i]['lat'], mark[i]['long']);
    var InfoWindow = new google.maps.InfoWindow();
    var marker = new google.maps.Marker({
      position:location,
      map: map,
      id: i,
      icon:image,
      title: mark[i]['name']
    }); 
    markers.push(marker);
    autoCenter(markers);
    google.maps.event.addListener(marker, 'click',function() {
      setAllMap(null);
      var marker_autoCenter = [];
      jQuery('#hidden_btn').removeClass('hidden_btn');
      k = this.id;
      map.setZoom(17);
      
      for (j = 0; j < mark[k]['child'].length; j++) {
        //console.log(mark[k]['child'][j]['lat'], mark[k]['child'][j]['long']);
        var position = new google.maps.LatLng(mark[k]['child'][j]['lat'], mark[k]['child'][j]['long']);

        var marker = new google.maps.Marker({
          position: position,
          map: map,
          id: k,
          sub_id: j,
          area: mark[k]['name'],
          name: mark[k]['child'][j]['name'],
          title: mark[k]['child'][j]['name'],
          property_counter : mark[k]['child'][j]['count'],
          term_id: mark[k]['child'][j]['term_id'],
          icon: sub_image,
          location_slug : mark[k]['child'][j]['slug']
        });
        
        marker_locations.push(marker);
        marker_autoCenter.push(marker);
    google.maps.event.addListener(marker, 'click',function() {
          closeAllInfoWindows();
      //map.setZoom(12);
   
          var  location = this.name;
          var term_id = this.term_id;
          var property_counter = this.property_counter;
          var location_slug = this.location_slug;

         if(mark[this.id]['child'][this.sub_id]['childof']!==undefined){
            console.log(mark[k]['child']);
            jQuery('#hidden_btn_loc').removeClass('hidden_btn');
            jQuery('#hidden_btn').addClass('hidden_btn');
            for (n = 0; n < mark[this.id]['child'][this.sub_id]['childof'].length; n++) {
              setLocationsMap(null);
              var position = new google.maps.LatLng(mark[this.id]['child'][this.sub_id]['childof'][n]['lat'], mark[this.id]['child'][this.sub_id]['childof'][n]['long']);
              var marker = new google.maps.Marker({
                position: position,
                map: map,
                area: mark[k]['name'],
                name: mark[k]['child'][this.sub_id]['childof'][n]['name'] ,
                title: mark[k]['child'][this.sub_id]['childof'][n]['name'],
                icon: sub_image,
                term_id: mark[k]['child'][this.sub_id]['childof'][n]['term_id']
              });
              marker_sub_locations.push(marker);
               //console.log(marker);
              google.maps.event.addListener(marker, 'click', function() {

                sub_location = this.name;
                         
                    var contentString = '<div id="content">'+
                      '<div id="siteNotice">'+
                      '</div>'+
                      '<h1 id="firstHeading" class="firstHeading">'+sub_location+'</h1>'
                    var infoWindow = new google.maps.InfoWindow({
                      content: contentString
                    });
                     infowindow.open(map,marker);

              });
              //////

            }
          }
        
// child part getting executed          
          else{
            //console.log(mark[k]['child'].length); 
            var marker = new google.maps.Marker({     
              position: this.position,
              map: map,
              area: this.area,
              name: this.name,
              title: this.title,
              icon: sub_image,
              term_id: this.term_id
            });
            marker_child_locations.push(marker);
            //console.log(ajaxurl);
            //console.log(this.term_id);
            jQuery.ajax({
              url : ajaxurl,
              type: "POST",
              data :  {
                'action':'GetMapResponse',
                'term_id':this.term_id,
              },
              success: function(data, textStatus, jqXHR)
              {
                var res = jQuery.parseJSON(data);
                console.log(res);
                var infotext = '';
                if(res.sale>0){
                  infotext += '<p><a href="<?php echo home_url();?><?php echo $page_url; ?>?location%5B%5D='+term_id+'&search_location_1='+term_id+'&listing_type=resale">'+res.sale+' <?php echo $PageText["sale"]?></a></p>';
                }
                else{
                  infotext += '<p><?php echo '0'.' '.$PageText["nopropsale"]?></p>';
                }
                if(res.rent_lt>0){
                  infotext += '<p><a href="<?php echo home_url();?><?php echo $page_url; ?>?location%5B%5D='+term_id+'&search_location_1='+term_id+'&listing_type=long_rental">'+res.rent_lt+' <?php echo $PageText["rent"]?></a></p>';
                }
                else{
                  infotext += '<p><?php echo '0'.' '.$PageText["noproprent"]?></p>';
                }
                if(res.rent_st>0){
                  infotext += '<p><a href="<?php echo home_url();?><?php echo $page_url; ?>?location%5B%5D='+term_id+'&search_location_1='+term_id+'&listing_type=short_rental">'+res.rent_st+' <?php echo $PageText["homes"]?></a></p>'; 
                }
                else{
                  infotext += '<p><?php echo '0'.' '.$PageText["nopropholidayhomes"]?></p>';
                }

                var contentString = '<div id="content">'+
                  '<div id="siteNotice">'+
                  '</div>'+
                  '<h1 id="firstHeading" class="firstHeading">'+location+'</h1>'+
                  infotext;
                var infoWindow = new google.maps.InfoWindow({
                  content: contentString
                });
                          
                infoWindow.open(map,marker);
                infoWindows.push(infoWindow);

              },
              error: function (jqXHR, textStatus, errorThrown)
              {
                console.log(errorThrown);
              }
            });  

                 
          }
// end of child part getting executed 
        });
      }
autoCenter(marker_autoCenter);
      /////

    });

  }
}

function showMarkers() {
  setAllMap(map);
  setLocationsMap(null);
  setSubLocationsChildMap(null);
  //map.setZoom(2);
  autoCenter(markers);
  jQuery('#hidden_btn').addClass('hidden_btn');
}
// Add a marker to the map and push to the array.
function addMarker(location) {
  var marker = new google.maps.Marker({
    position: location,
    map: map
  });
  markers.push(marker);
}
function autoCenter(marks) {
  //  Create a new viewpoint bound
  var bounds = new google.maps.LatLngBounds();
  for (var i = 0; i < marks.length; i++) {
    bounds.extend(marks[i].position);
  }
  map.fitBounds(bounds);
}

function closeAllInfoWindows() {
  for (var i=0;i<infoWindows.length;i++) {
     infoWindows[i].close();
  }
}

function setLocationsMap(map) {
  for (var i = 0; i < marker_locations.length; i++) {
    marker_locations[i].setMap(map);
  }
}

function setSubLocationsMap(map) {
  for (var i = 0; i < marker_sub_locations.length; i++) {
    marker_sub_locations[i].setMap(map);
  }
}

function setSubLocationsChildMap(map) {
  for (var i = 0; i < marker_child_locations.length; i++) {
    marker_child_locations[i].setMap(map);
  }
}

function setAllMap(map) {
  for (var i = 0; i < markers.length; i++) {
    markers[i].setMap(map);
  }
}
// Removes the markers from the map, but keeps them in the array.
function clearMarkers() {
  setAllMap(null);
}

// Shows any markers currently in the array.
function showMarkers() {
  setAllMap(map);
  setLocationsMap(null);
  setSubLocationsChildMap(null);
  map.setZoom(7);
  autoCenter(markers);
  jQuery('#hidden_btn').addClass('hidden_btn');
}
function  showLocationMarkers() {
  setLocationsMap(map);
  setAllMap(null);
  setSubLocationsMap(null);

  jQuery('#hidden_btn_loc').addClass('hidden_btn');
  jQuery('#hidden_btn').removeClass('hidden_btn');
}
// Deletes all markers in the array by removing references to them.
function deleteMarkers() {
  clearMarkers();
  marker.setVisible(false);
  markers = [];
  jQuery('#hidden_btn').addClass('hidden_btn');
}

jQuery(document).ready(function(){
  initialize();
})

</script> 
   <?php  
  } 
} 
