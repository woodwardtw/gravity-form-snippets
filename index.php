<?php 


//gives you a clean URL to files uploaded via Gravity Forms so that building links or embedding them via the URL works - w/o this you have a pretty secure media upload option
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
    $timeline_type = rgar($entry, '3');//entry field ID is 3
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
FORM REGISTRATION of USERS AND SITES
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


//site creation w no user creation

add_action( 'gform_after_submission_5', 'gform_site_cloner', 10, 2 );//specific to the gravity form id

function gform_site_cloner($entry, $form){

    $_POST =  [
        'action'         => 'process',
        'clone_mode'     => 'core',
        'source_id'      => rgar( $entry, '3' ), //specific to the form entry fields and should resolve to the ID site to copy
        'target_name'    => rgar( $entry, '2' ), //specific to the form entry fields - need to parallel site url restrictions
        'target_title'   => rgar( $entry, '2' ), //specific to the form entry fields
        'disable_addons' => true,
        'clone_nonce'    => wp_create_nonce('ns_cloner')
    ];
  
  // Setup clone process and run it.
  $ns_site_cloner = new ns_cloner();
  $ns_site_cloner->process();

  $site_id = $ns_site_cloner->target_id;
  $site_info = get_blog_details( $site_id );
  if ( $site_info ) {
    // Clone successful!
  }
}



//for initial user registration AND site creation documentation at https://docs.gravityforms.com/gform_site_created/
add_action( 'gform_site_created', 'your_function_name', 10, 5 );



$search_criteria = array(
    'status'        => 'active',
    // 'field_filters' => array(
    //     'mode' => 'any',       
    //     array(
    //         'key'   => '6',
    //         'value' => $id
    //     )
    // )
);

  $sorting         = array();
  $paging          = array( 'offset' => 0, 'page_size' => 100 );
  $total_count     = 0;
  $form_id = 4;
  
  $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging, $total_count );
  //print("<pre>".print_r($entries,true)."</pre>");
  $html = '';
  $total_scores = [];
  $total_guesses = [];
    foreach ($entries as $entry) {
      if (intval($entry['gsurvey_score'])>0){
        $pre = 'pos-';
      }
      if (intval($entry['gsurvey_score'])<0){
        $pre = 'neg';
      } 
      if (intval($entry['gsurvey_score']) === 0) {
        $pre = 'zero-';
      }
      array_push($total_scores,$pre . $entry['gsurvey_score']);
      $guess = array_push($total_guesses, $entry[3]);
    }    
      $gform_scores = array(          
           'scores' => $total_scores,
       );
     wp_localize_script('main-course', 'gformScores', $gform_scores); //sends data to script as variable     
}

