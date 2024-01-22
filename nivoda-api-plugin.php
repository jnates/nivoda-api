<?php
/*
Plugin Name: Nivoda API Integration
Plugin URI: https://example.com/nivoda-plugin
Description: Plugin for integrating with the Nivoda API to retrieve diamond information.
Version: 1.0
Author: Juan Nates
Author URI: https://example.com/author
License: GPL
Text domain: nivoda-plugin
*/

define( 'GRAPHQL_API_URL', 'https://integrations.nivoda.net/api/diamonds' );  
define( 'USERNAME', '' );  
define( 'PASSWORD', '' );  

register_activation_hook( __FILE__, 'nivoda_api_activate' );

// Function to authenticate the connection to the Nivoda API
function nivoda_api_auth() {
  $username =  USERNAME;// get_option('nivoda_username');
  $password =  PASSWORD; // get_option('nivoda_password');
  $endpoint_url = GRAPHQL_API_URL;
  $authenticate = '
  query { 
    authenticate
    { 
      username_and_password
      (username:"'.$username.'",password:"'.$password.'")
        {token}
    }
  }';

  $args = array(
   'method' => 'POST',
   'headers' => array(
       'Content-Type' => 'application/json',
   ),
   'body' => json_encode( array( 'query' => $authenticate ) ),
 );
 
  $response = wp_remote_post($endpoint_url, $args );
  if ( is_wp_error( $response ) ) {
    echo 'entro error get :<pre>';
    echo $response->get_error_message();
    echo '</pre>';
   return '<p>Error connecting to Nivoda API: ' . $response->get_error_message() . '</p>';
  } else {
   $body= wp_remote_retrieve_body($response);
   $result = json_decode( $body, true );
    if ( isset( $result['errors'] ) ) {
        return null;
    }
    
    return $result['data']['authenticate']['username_and_password']['token'];
  }
}

// Function to get all  Nivoda Diamonds info
function get_all_diamonds($token) {
  $endpoint_url = GRAPHQL_API_URL;
  $query = '
  query {
      diamonds_by_query(query: {has_image:true}) {
        items {
          id
          price
          discount
          markup_price
          markup_discount
          diamond{
            id
            image
            supplier{
              id
              name
                id
            }
            availability
            location
            final_price
          }
        }
      }
    }
  ';

  $args = array(
     'method' => 'POST',
    'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $token,
    ),
    'body' => json_encode( array( 'query' => $query ) ),
  );

  $response = wp_remote_post($endpoint_url, $args );
  if ( is_wp_error( $response ) ) {
   return '<p>Error connecting to Nivoda API: ' . $response->get_error_message() . '</p>';
  } else {
   $body = wp_remote_retrieve_body($response);
   $result = json_decode( $body, true );
    
    if ( isset( $result['errors'] ) ) {
        echo 'error getting data '.$result['errors'][0]['message'];
        return null;
    }
    return $result['data']['diamonds_by_query']['items'];
  }
}

// Function to display results on WordPress page using custom shortcode
function nivoda_api_display($request) {
  $token = nivoda_api_auth();
  $response = get_all_diamonds($token);
  $increment=0.20;

  foreach($response as $item){
    $item['price']=Ceil($item['price'] + ($item['price'] *  $increment));
  }

  return new WP_REST_Response(array('mensaje'=>'success','data' => $response),200); 
}

add_action('rest_api_init', function (){
  register_rest_route('nivoda-api/v1', '/diamonds', array(
    'methods' => 'GET',
    'callback' => 'nivoda_api_display',
  ));
});
