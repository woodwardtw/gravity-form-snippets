<?php 


//gives you a clean URL to files uploaded via Gravity Forms so that building links or embedding them via the URL works
add_filter( 'gform_secure_file_download_location', '__return_false' );


/*
***
FORM SUBMISSION TO POST RELATED STUFF
***
*/

//takes submitter to the post create on submissions that create posts
add_action('gform_after_submission', 'redirect_on_post', 10, 2);
function redirect_on_post($entry, $form) {
    $post_id = $entry['post_id'];
    $url = get_site_url() . "/?p=" . $post_id;
    wp_redirect($url);
    exit;
}


//set post password on submissions that create posts --- I think it uses the first field as the password but it's been a while
add_action( 'gform_after_submission', 'set_default_values', 10, 2 );
function set_default_values( $entry, $post ) {		
      		
      		$post = get_post( $entry['post_id'] );
      		$pw = $entry[1]; //

               wp_update_post(array(
            	'ID' => $post->ID,
            	'post_password' => $pw,
        ));
}


//if you're using the registration add on make sure you're triggering author stuff off this instead of the wp user_registered

add_action( 'gform_user_registered', 'YOUR_FUNCTION', 10, 1 );


//conditional for templates etc.
// [gravityforms action="conditional" merge_tag="{My Field:1}" condition="isnot" value=""]
// My Field Label: {My Field:1}
// [/gravityforms]