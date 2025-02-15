<?php

// Add responsive container to embed videos
function forty_responsive_video( $html ) {
	//add http protocol
    $html = str_replace('<iframe src="//', '<iframe src="http://', $html);

    return '<div class="flex-video">' . $html . '</div>';
}
add_filter( 'embed_oembed_html', 'forty_responsive_video', 10, 3 );
add_filter( 'video_embed_html', 'forty_responsive_video' );


// Get a cleaner template name. You can use as ID or class in your template containers.
function get_template_name() {
  if (is_page()) {
    global $post;
    return str_replace('.php', '', get_post_meta($post->ID, '_wp_page_template', true));
  }
  return '';
}


// Add custom ACF option pages
if(function_exists('acf_add_options_page')) {
  acf_add_options_page();
  acf_add_options_sub_page('Options');
}


// Returns timthumbified URL
function forty_timthumbify($src){
	global $blog_id;
	if(is_multisite()){
		$blog_upload_dir = get_site_url(1).'/wp-content/blogs.dir/'.$blog_id;
		$src = get_site_url(1).'/functions/timthumb/timthumb.php?src='.$blog_upload_dir.strstr($src, '/files/');
	}else
		$src = get_bloginfo('template_directory').'/functions/timthumb/timthumb.php?src='.$src;

	return $src;
}

function custom_post_type_sub_page_urls(){
  add_rewrite_tag('%custom_post_type_sub_page%', '([^&]+)');
  add_rewrite_rule(
    'station/([^/]*)/([^/]*)',
    'index.php?station=$matches[1]&custom_post_type_sub_page=$matches[2]',
    'top'
  );
  flush_rewrite_rules();
}
add_action('init', 'custom_post_type_sub_page_urls');