<?php

// Rebuild the zips when a taxonomy is saved or deleted
add_filter('edited_recipe_theme', 'rebuild_zip_from_term');
function rebuild_zip_from_term ($term_id) {
  $args = array(
    'post_type' => 'recipe',
    'post_status' => 'publish',
    'nopaging' => true,
    'tax_query' => array(
      array(
        'taxonomy' => 'recipe_theme',
        'field' => 'term_id',
        'terms' => $term_id,
        'include_children' => true,
        'operator' => 'IN'
      )
    )
  );
  
  $query = new WP_Query($args);

  while ($query->have_posts()) {
    $query->the_post();

    generate_zip_file($query->post->ID, $query->post);
  }
}

add_filter('delete_recipe_theme', 'rebuild_zip_for_all_recipes');
function rebuild_zip_for_all_recipes () {
  $args = array(
    'post_type' => 'recipe',
    'post_status' => 'publish',
    'nopaging' => true
  );
  
  $query = new WP_Query($args);

  while ($query->have_posts()) {
    $query->the_post();

    generate_zip_file($query->post->ID, $query->post);
  }
}

add_filter('posts_where', 'my_posts_where');
function my_posts_where ($where) {
  return str_replace("meta_key = 'bar_recipe_themes_%_theme'", "meta_key LIKE 'bar_recipe_themes_%_theme'", $where);
}

add_action('save_post', 'generate_zip_file');
function generate_zip_file ($post_id, $post = false) {
  $post = (!$post ? get_post($post_id) : $post);
  $uploads = wp_upload_dir();
  $destination = $uploads['basedir'] . '/zip/';

  if (is_null($post) || $post->post_status !== 'publish')
    return;

  if (!file_exists($destination))
    mkdir($destination, 0777, true);

  if ($post->post_type === 'station') {
    // get all the files for the station
    get_files_for_station($post_id, $destination, $post->post_name);

  } elseif ($post->post_type === 'recipe') {
    // get all the stations that use this recipe's tax
    $terms = wp_get_post_terms($post_id, 'recipe_theme', array('fields' => 'ids'));

    $args = array(
      'post_type' => 'station',
      'post_status' => 'publish',
      'nopaging' => true,
      'meta_query' => array(
        array(
          'key' => 'bar_recipe_themes_%_theme',
          'value' => $terms,
          'operator' => 'IN'
        )
      )
    );

    $query = new WP_Query($args);

    // process each station
    while ($query->have_posts()) {
      $query->the_post();
      $destination_dir = $uploads['basedir'] . '/zip/';
      $destination_filename = $query->post->post_name;
      get_files_for_station(get_the_id(), $destination_dir, $destination_filename);
    }
  }

}

// Gets all the files needed for the zip based on the station post id
function get_files_for_station ($station_id, $destination_dir, $destination_filename) {
  $files = array(
    'sales' => array(),
    'themes' => array(),
    'root' => array()
  );

  // Station files
  $files['root'][] = get_field('bar_download_calendar', $station_id);

  // Station's sales assets
  $sales = get_field('bar_sales_assets', $station_id);

  if (!empty($sales)) {
    $sales_files = array();

    foreach ($sales as $sale) {
      $files['sales'][] = $sale['file'];
      $sales_files[] = $sale['file'];
    }

    if (!empty($sales_files)) {
      $sales_files = clean_file_array($sales_files);
      $sales_dest = $destination_dir . $destination_filename . '-sales.zip';
      create_zip($sales_dest, $sales_files);
    }
  }

  // Get all the themes for this station
  $themes = get_field('bar_recipe_themes', $station_id);
  $terms = array();

  foreach ($themes as $theme)
    $terms[] = (int)$theme['theme'];

  // Recipe's files
  $args = array(
    'post_type' => 'recipe',
    'post_status' => 'publish',
    'nopaging' => true,
    'tax_query' => array(
      array(
        'taxonomy' => 'recipe_theme',
        'field' => 'term_id',
        'terms' => $terms,
        'include_children' => true,
        'operator' => 'IN'
      )
    )
  );

  $query = new WP_Query($args);
  $recipe_theme_files = array();

  // We need to build a separate zip for each theme
  while ($query->have_posts()) {
    $query->the_post();

    $themes = get_the_terms($query->post->ID, 'recipe_theme');
    $downloads = get_field('recipe_downloads', $query->post->ID);
    $recipe_pdf = get_field('recipe_pdf', $query->post->ID);

    // Each theme gets its own zip
    foreach ($themes as $theme) {
      if (!isset($recipe_theme_files[$theme->name]))
        $recipe_theme_files[$theme->name] = array('root' => array());
      
      if (!isset($recipe_theme_files[$theme->name][$query->post->post_title]))
        $recipe_theme_files[$theme->name][$query->post->post_title] = array();

      // Theme kit file
      $recipe_theme_files[$theme->name]['root'][] = get_field('kit_file', 'recipe_theme_' . $theme->term_id);

      // Add the pdf
      $recipe_theme_files[$theme->name][$query->post->post_title][] = $recipe_pdf;

      // Get the additional download files
      if (!empty($downloads)) {
        foreach ($downloads as $d_file) {
          $recipe_theme_files[$theme->name][$query->post->post_title][] = $d_file['download_file'];
        }
      }
    }
  }

  $files['themes'] = $recipe_theme_files;

  $mapped = flatten_file_array($files);

  // error_log(print_r($mapped, true));

  // compress the files
  if (!empty($mapped)) {
    $destination = $destination_dir . $destination_filename . '.zip';
    return create_zip($destination, $mapped);
  }
}

function clean_file_array ($files, $relative_path = '') {
  $mapped = array();
  $site_url = site_url('/');

  foreach ($files as $file) {
    if (empty($file))
      continue;

    // If the url index of the file object doesn't exist, it's already the url of the file
    $file_url = (isset($file['url']) ? $file['url'] : $file);

    $key = str_replace($site_url, ABSPATH, $file_url);
    $dest = $relative_path . basename($key);

    $mapped[$key] = $dest;
  }

  return $mapped;
}

function flatten_file_array ($files) {
  $flattened_files = array_merge(clean_file_array($files['root']), clean_file_array($files['sales'], 'Sales Materials/'));

  // The theme files are more nested
  foreach ($files['themes'] as $theme_name => $recipes) {
    // Add the theme root files
    $flattened_files = array_merge($flattened_files, clean_file_array($recipes['root'], $theme_name . '/'));

    foreach ($recipes as $recipe_name => $recipe_files) {
      // Skip the root files
      if ($recipe_name === 'root')
        continue;

      $flattened_files = array_merge($flattened_files, clean_file_array($recipe_files, $theme_name . '/' . $recipe_name . '/'));
    }
  }

  return $flattened_files;
}

function create_recipe_themes_zip ($theme_files, $destination_dir) {
  foreach ($theme_files as $theme_name => $recipes) {
    // Add the theme root files
    $flattened_files = clean_file_array($recipes['root']);

    foreach ($recipes as $recipe_name => $recipe_files) {
      // Skip the root files
      if ($recipe_name === 'root')
        continue;

      $flattened_files = array_merge($flattened_files, clean_file_array($recipe_files, $recipe_name . '/'));
    }

    $destination = $destination_dir . $theme_name . '.zip';
    create_zip($destination, $flattened_files);
  }
}

// Creates the zip file
function create_zip ($destination, $files) {
  $zip = new ZipArchive();

  if ($zip->open($destination, ZIPARCHIVE::OVERWRITE) !== true)
    return false;

  foreach($files as $source => $dest)
    $zip->addFile($source, $dest);

  $zip->close();

  //check to make sure the file exists
  return file_exists($destination);
}

function get_zip_download_link ($post_name, $is_sales_link = false) {
  $uploads = wp_upload_dir();
  $zip = $uploads['baseurl'] . '/zip/' . $post_name . ($is_sales_link ? '-sales.zip' : '.zip');

  return get_force_download_link($zip);
}

function get_force_download_link ($resource_url) {
  $template = get_bloginfo('template_url') . '/functions/site/force-download.php?file=';

  if (filter_var($resource_url, FILTER_VALIDATE_URL)) {
    $file_headers = array_change_key_case(get_headers($resource_url, 1), CASE_LOWER);
    $resource_url = ($file_headers[0] == 'HTTP/1.1 404 Not Found') ? '#' : $template . $resource_url;
  } else {
    $resource_url = $template . $resource_url;
  }

  return $resource_url;
}

