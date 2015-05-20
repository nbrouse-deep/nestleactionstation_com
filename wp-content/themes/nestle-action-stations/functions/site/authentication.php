<?php

defined('PLAIN_COOKIE') or define('PLAIN_COOKIE', '22114567843235');

function user_has_access () {
  return isset($_COOKIE['authenticated']) && $_COOKIE['authenticated'] === PLAIN_COOKIE;
}

add_filter('wp_ajax_authenticate_passcode', 'verify_access_passcode');
add_filter('wp_ajax_nopriv_authenticate_passcode', 'verify_access_passcode');

function verify_access_passcode () {
  $codes = get_field('pass_code', 'option');
  $check_value = $_POST['password']; // since we're not using this input to manipulate a db, I bypassed cleaning the input
  $remember = (bool)$_POST['remember'];

  foreach ($codes as $code) {
    if ($code['password'] === $check_value) {
      $expires = $remember ? time() + (1 * 1 * 6 * 60 * 60) : time() + (60 * 60 * 24);

      return setcookie('authenticated', PLAIN_COOKIE, $expires, '/');
    }
  }

  return http_response_code(401);
}
