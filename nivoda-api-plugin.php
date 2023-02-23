<?php
/*
Plugin Name: nivoda
Plugin URI: <URL del plugin (puede ser la del autor)>
Description: <Descripción breve del plugin y sus funcionalidades>
Version:<Versión del plugin>
Author:<Autor del plugin>
Author URI:<URL del autor del plugin>
License:<Licencia con la que se distribuye el plugin. La más frecuente es GPL>
Text domain: <nombre-del-plugin (para agrupar los textos traducibles del plugin)>
*/

//require_once( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' );
// Configuration function to store Nivoda API credentials
function nivoda_api_config() {
  add_option('nivoda_api_key', getenv('API_KEY'));
  add_option('nivoda_api_secret', getenv('SECRET_KEY'));
}

// Plugin activation function
function nivoda_api_activate() {
  nivoda_api_config();
}

register_activation_hook( __FILE__, 'nivoda_api_activate' );

// Function to authenticate the connection to the Nivoda API
function nivoda_api_auth() {
  $api_key = get_option('nivoda_api_key');
  $api_secret = get_option('nivoda_api_secret');
  
}

// Function to query the Nivoda API
function nivoda_api_query($query) {
  $endpoint_url = 'https://wdc-intg-customer-staging.herokuapp.com/api/diamonds-graphiql';
  echo 'aaa llego endpoiut '. $endpoint_url;

  $args = array(
     'method' => 'POST',
    'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6ImM1YWRiZWM0LTRkZjQtNDhlMC1iY2RlLTMxZmYxYjgxOGE5MiIsInJvbGUiOiJDVVNUT01FUiIsInN1YnR5cGUiOm51bGwsImNvdW50cnkiOiJHQiIsInB0IjoiREVGQVVMVCIsImlmIjoiIiwiY2lkIjoiZTk3MDEyYzYtOGE3Ni00NzNmLTljZjctMzBlMGU2ZjI3MWRhIiwiYXBpIjp0cnVlLCJhcGlfaCI6dHJ1ZSwiYXBpX2MiOnRydWUsImFwaV9vIjp0cnVlLCJhcGlfciI6dHJ1ZSwiaWF0IjoxNjc3MTcxNjU2LCJleHAiOjE2NzcyNTgwNTZ9.uEwh1TNoepJemk_7QTTvcu0qtMXrq7VX6S_jlkQpkMA',
    ),
    'body' => json_encode( array( 'query' => $query ) ),
);
  $response = wp_remote_post($endpoint_url, $args );
  if ( is_wp_error( $response ) ) {
    echo '<pre>';
    echo $response->get_error_message();
    echo '</pre>';
   return '<p>Error connecting to Nivoda API: ' . $response->get_error_message() . '</p>';
  } else {
   $body= wp_remote_retrieve_body($response);
   echo '<pre>';
   echo $body;
   echo '</pre>';
   $result = json_decode( $body, true );
    
    if ( isset( $result['errors'] ) ) {
        return null;
    }
    
    return $result['data'];
  }
}

// Function to display results on WordPress page using custom shortcode
function nivoda_api_display() {
  $output = '<pre>llego si guardo</pre>';
  $query = '{
  query ($query: DiamondQuery) {
      diamonds_by_query(query: $query) {
        items {
          id
          diamond{
            image
            id
            supplier{
              id
              name
              
            }
            location
            final_price
          }
        }
      }
    }
  }';
  echo 'antes del query';
  $response = nivoda_api_query($query);
  if ( $response ) {
    echo '<ul>';
    
    foreach ( $response['items']['diamond'] as $post ) {
        echo '<li>' . $response['id'] . '</li>';
    }
    
    echo '</ul>';
}
  $output =  $output . $response; 
  return $output;  // Generate the HTML to display the results of the query to the Nivoda API
}

add_shortcode('nivoda-api', 'nivoda_api_display' );
