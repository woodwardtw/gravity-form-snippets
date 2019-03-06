<?php 


//gives you a clean URL to files uploaded via Gravity Forms so that building links or embedding them via the URL works
add_filter( 'gform_secure_file_download_location', '__return_false' );


//in case you need to change who the default email comes from 
add_filter( 'gform_notification', 'change_from_email', 10, 3 );
function change_from_email( $notification, $form, $entry ) {
        $notification['from'] = 'wordpress@rampages.us'; //you'll want to change this to your functional email address
    return $notification;
}


/*
***
FORM SUBMISSION TO POST RELATED STUFF
***
*/

//takes submitter to the post create on submissions that create posts
add_action('gform_after_submission', 'alt_gform_redirect_on_post', 10, 2);
function alt_gform_redirect_on_post($entry, $form) {
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

//set the publishing date of an entry based on a gform field
function special_timeline_update($entry, $form){
    $time = rgar($entry, '1');//assumes the gform date field is field 1 if not change it
    $post = get_post( $entry['post_id'] );
    $post->post_date = $time;
    $post->post_date_gmt = get_gmt_from_date( $time );
    wp_update_post($post);
}
add_action( 'gform_after_submission_5', 'special_timeline_update', 10, 2 );//set to run off form 5 if not change it


//set featured image by form entry choice
function altlab_timeline_featured_image($entry, $form){
    $timeline_type = rgar($entry, '3');//entry field
    if ($timeline_type  == 'First computer I owned' ){//response
        $img_id = 6895;//id of image in wp media library
    }
    if ($timeline_type  == 'First time online'){
        $img_id = 6899;
    }
    if ($timeline_type  == 'First email'){
        $img_id = 6900;
    }
    if ($timeline_type  == 'First cell phone'){
        $img_id =  6898;
    }
 
    set_post_thumbnail( $entry['post_id'], $img_id );
}
add_action( 'gform_after_submission_5', 'altlab_timeline_featured_image', 10, 2 );//note form ID


//conditional for templates etc. more at https://gravitywiz.com/gravity-forms-conditional-shortcode/
// [gravityforms action="conditional" merge_tag="{My Field:1}" condition="isnot" value=""]
// My Field Label: {My Field:1}
// [/gravityforms]


/*
***
FORM REGISTRATION
***
*/

//add users to multiple sites
add_action( 'gform_user_registered', 'many_site_registration_save', 10, 3 );
 
function many_site_registration_save( $user_id, $feed, $entry ) {
    $sites = array(25,26,27,28); //the IDs of the sites you want the user added to
    foreach ($sites as $site) {
        add_user_to_blog($site, $user_id, 'author'); //the last variable is the desired role for the user
    }
}


//if you're using the registration add on make sure you're triggering author stuff off this instead of the wp user_registered

add_action( 'gform_user_registered', 'YOUR_FUNCTION', 10, 1 );

