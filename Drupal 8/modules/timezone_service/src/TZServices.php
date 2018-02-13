<?php
/**
 * @file providing the service that do some timezone related functions.
 *
 */
namespace  Drupal\timezone_service;
class TZServices {

  public function hasWord($word, $txt) {
    $patt = "/(?:^|[^a-zA-Z])" . preg_quote($word, '/') . "(?:$|[^a-zA-Z])/i";
    return preg_match($patt, $txt);
  }

  public function time24to12($h24, $min){
    if ($h24 === 0) { $newhour = 12; }
    elseif ($h24 <= 12) { $newhour = $h24; }
    elseif ($h24 > 12) { $newhour = $h24 - 12; }
    return ($h24 < 12) ? $newhour . ":$min am" : $newhour . ":$min pm";
  }

}