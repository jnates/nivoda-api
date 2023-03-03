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
  add_option('nivoda_username', getenv('API_KEY'));
  add_option('nivoda_password', getenv('SECRET_KEY'));
}

// Plugin activation function
function nivoda_api_activate() {
  nivoda_api_config();
}

register_activation_hook( __FILE__, 'nivoda_api_activate' );

// Function to authenticate the connection to the Nivoda API
function nivoda_api_auth() {
  $username =  getenv('USERNAME');// get_option('nivoda_username');
  $password =   getenv('PASSWORD'; // get_option('nivoda_password');
  $endpoint_url =  getenv('GRAPHQL_API_UR');
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
  query ($query: DiamondQuery) {
      diamonds_by_query(query: $query) {
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
    return $result['data'];
  }
}

// Function to display results on WordPress page using custom shortcode
function nivoda_api_display() {
  $output = 'frontend viejo de nates';
  $token = nivoda_api_auth();
  $response = get_all_diamonds($token);
  
  if ( $response ) {
    foreach ( $response['diamonds_by_query']['items'] as $diamond ) {
        echo '<ul>';
        echo '<li>' . $diamond['id'] . '</li>';
        echo '<li>' . $diamond['price'] . '</li>';
        echo '<li>' . $diamond['discount'] . '</li>';
        echo '<li>' . $diamond['diamond']['id'] . '</li>';
        echo '<li>' . $diamond['diamond']['image'] . '</li>';
        echo '<li>' . $diamond['diamond']['availability'] . '</li>';
        echo '<li>' . $diamond['diamond']['final_price'] . '</li>';
        echo '<li>' . $diamond['diamond']['location'] . '</li>';
        echo '</ul>';
    }
}
  $output =  $output . $response; 
  return $output;  // Generate the HTML to display the results of the query to the Nivoda API
}

add_shortcode('nivoda-api', 'nivoda_api_display' );
