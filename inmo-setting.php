<?php
require_once(INMO__PLUGIN_DIR.'Connection.php');
class InmolinkSettings {
  /**
   * Holds the values to be used in the fields callbacks
   */
  private $options;

  /**
   * Start up
   */
  public function __construct() {
      add_action( 'admin_menu', array( $this, 'inmolink_plugin_page' ) );
      add_action( 'admin_init', array( $this, 'page_init' ) );
  }

  /**
   * Add options page
   */
  public function inmolink_plugin_page() {
      // This page will be under "Settings"
      add_options_page(
          'Inmolink Settings',
          'InmoTech API',
          'manage_options',
          'inmolink',
          array( $this, 'inmolink_admin_page' )
      );
  }

  /**
   * Options page callback
   */
  public function inmolink_admin_page() {
      // Set class property
      $this->options = get_option( 'inmolink_option_name' );
      ?>
      <div class="wrap">
          <h1>Inmolink API Settings</h1>
          <form method="post" action="options.php">
          <?php
              // This prints out all hidden setting fields
              settings_fields( 'inmolink_option_group' );
              do_settings_sections( 'inmolink-setting-admin' );
              submit_button();
          ?>
          </form>
      </div>
      <?php 
      /*
      * Import forms
      */
        if(isset($_POST['importlocation'])){
            inmolink_listing_location_data();}
        if(isset($_POST['importtype'])){
             inmolink_listing_property_types_data();}
        if(isset($_POST['importfeatures'])){
             inmolink_listing_property_features_data();}
      ?>
      <form method="post">
        <h2>Import Location</h2>
        <input type="submit" name="importlocation" value="Import">
        <span>NOTE: Allow several minutes for this process to complete.</span>
      </form>

      <form method="post">
        <h2>Import Types</h2>
        <input type="submit" name="importtype" value="Import">
        <span>NOTE: Allow several minutes for this process to complete.</span>
      </form>

      <form method="post">
        <h2>Import Features</h2>
        <input type="submit" name="importfeatures" value="Import">
        <span>NOTE: Allow several minutes for this process to complete.</span>
      </form>
      <?php
  }

  /**
   * Register and add settings
   */
  public function page_init() {
      register_setting(
          'inmolink_option_group', // Option group
          'inmolink_option_name', // Option name
          array( $this, 'sanitize' ) // Sanitize
      );

      add_settings_section(
          'inmolink_setting_section', // ID
          'InmoTech API', // Title
          array( $this, 'print_section_info' ), // Callback
          'inmolink-setting-admin' // Page
      );

      add_settings_field(
          'api_access_token',
          'API Access Token',
          array( $this, 'api_access_token_callback' ),
          'inmolink-setting-admin', // Page
          'inmolink_setting_section' // Section
      );

      add_settings_field(
          'api_base_url',
          'API Base URL',
          array( $this, 'api_baseurl_callback' ),
          'inmolink-setting-admin', // Page
          'inmolink_setting_section' // Section
      );

      add_settings_field(
        'google_api_key',
        'Google API Key',
        array( $this, 'google_api_key_callback' ),
        'inmolink-setting-admin', // Page
        'inmolink_setting_section' // Section
      );

      add_settings_field(
        'property_slug',//id
        'Property page slug:',//name
        array( $this, 'property_slug_callback' ),
        'inmolink-setting-admin', // Page
        'inmolink_setting_section' // Section
      );
   
 
  }

  /**
   * Sanitize each setting field as needed
   *
   * @param array $input Contains all settings fields as array keys
   */
  public function sanitize( $input ) {
      $new_input = array();

      if( isset( $input['api_access_token'] ) )
          $new_input['api_access_token'] = sanitize_text_field( $input['api_access_token'] );

      if( isset( $input['api_base_url'] ) )
          $new_input['api_base_url'] = sanitize_text_field( $input['api_base_url'] );

      if( isset( $input['google_api_key'] ) )
          $new_input['google_api_key'] = sanitize_text_field( $input['google_api_key'] );

      if( isset( $input['property_slug'] ) )
          $new_input['property_slug'] = sanitize_text_field( $input['property_slug'] ); 
        
       

      return $new_input;
  }

  /**
   * Print the Section text
   */
  public function print_section_info() {
      print 'Enter API settings below:';
  }

  /**
   * Get the settings option array and print one of its values
   */
  public function api_baseurl_callback() {
      printf(
          '<input type="text" id="api_base_url" name="inmolink_option_name[api_base_url]" value="%s" />',
          isset( $this->options['api_base_url'] ) ? esc_attr( $this->options['api_base_url']) : ''
      );
  }

  /**
   * Get the settings option array and print one of its values
   */
  public function api_access_token_callback() {
      printf(
          '<input type="text" id="api_access_token" name="inmolink_option_name[api_access_token]" value="%s" />',
          isset( $this->options['api_access_token'] ) ? esc_attr( $this->options['api_access_token']) : ''
      );
  }

  /**
   * Get the settings option array and print one of its values
   */

 function property_slug_callback() {?>
  <select name="inmolink_option_name[property_slug]" id="selectpage"  >
    <?php
   $inmolink_options = get_option('inmolink_option_name');
$propertyslugval=$inmolink_options['property_slug'];

?>
        <option value= "<?php echo $propertyslugval; ?>" selected>
        <?php echo ( "Select page" )?> </option>
        <?php
           $pages = get_pages();


            foreach ( $pages as $page ) {
           $a = $page->post_title;?>
        <option  value=" <?php echo esc_attr($a) ?>"<?php if($propertyslugval==$a) echo 'selected="selected"'; ?> id="property_slug">
           <?php echo $page->post_title;?>           
        </option>
        <?php   }
        ?>

 </select>
   <?php
$inmolink_options = get_option('inmolink_option_name');
$propertyslugval=$inmolink_options['property_slug'];
//echo $propertyslugval;



 ?>
 <?php
    echo '<br>';
    echo '<span class="setting-description">';
    echo '<b>Note:</b> save permalinks after changing any of the above property page slugs.';
    echo '</span>';
  }
  
 
  public function google_api_key_callback() {
      printf(
          '<input type="text" id="google_api_key" name="inmolink_option_name[google_api_key]" value="%s" />',
          isset( $this->options['google_api_key'] ) ? esc_attr( $this->options['google_api_key']) : ''
      );
  }
}
?>