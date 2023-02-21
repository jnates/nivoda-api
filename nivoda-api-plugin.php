<?php

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
function nivoda_api_query() {
  $response = wp_remote_get( 'https://api.nivoda.com/endpoint', array( 'timeout' => 120, 'sslverify' => false ) );

  if ( is_wp_error( $response ) ) {
    $output .= '<p>Error connecting to Nivoda API: ' . $response->get_error_message() . '</p>';
  } else {
    $output .= '<p>Connection to Nivoda API successful!</p>';
  }
}

// Function to display results on WordPress page using custom shortcode
function nivoda_api_display() {
  $output = '';
  $response = nivoda_api_query();
  return $output;   // Generate the HTML to display the results of the query to the Nivoda API
}

add_shortcode( 'nivoda-api', 'nivoda_api_display' );
