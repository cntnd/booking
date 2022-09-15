<?php

/**
 * cntnd Util Class
 */
class CntndUtil {

  // Data

  public static function escapeData($string){
    $specialchars = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    $base64 = base64_encode($specialchars);
    return $base64;
  }

  public static function unescapeData($string,$decode_specialchars=true){
    $base64 = utf8_encode(base64_decode($string));
    if ($decode_specialchars){
      $base64 = htmlspecialchars_decode($base64, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    }
    $decode = json_decode($base64, true);
    return $decode;
  }

    public static function emptyIfNull($value, $empty = ""){
        if (empty($value)){
            return $empty;
        }
        return $value;
  }

  public static function emptyIfNullObject($object, $key, $empty = array()){
      if (array_key_exists($key, $object)){
          if (is_object($object[$key]) || is_array($object[$key])){
              return $object[$key];
          }
          return self::emptyIfNull($object[$key], "");
      }
      return $empty;
  }

  // endregion

  // String comparisons

  public static function startsWith($haystack, $needle){
    return strncmp($haystack, $needle, strlen($needle)) === 0;
  }

  public static function endsWith($haystack, $needle){
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
  }

  // endregion

  // Module templates

  public static function templates($module, $client){
    $cfgClient = \cRegistry::getClientConfig();
    $templates = array();
    $template_dir   = $cfgClient[$client]["module"]["path"].$module.'/template/';
    $handle         = opendir($template_dir);
    while ($entryName = readdir($handle)){
      if (is_file($template_dir.$entryName) && !self::startsWith($entryName, "_")){
        $templates[]=$entryName;
      }
    }
    closedir($handle);
    asort($templates);

    return $templates;
  }

  public static function isTemplate($module, $client, $template)
  {
    $cfgClient = \cRegistry::getClientConfig();
    $template_file = $cfgClient[$client]["module"]["path"] . $module . '/template/' . $template;

    if (!empty($template) && self::endsWith($template, ".html")) {
      if (file_exists($template_file)) {
        return true;
      }
    }
    return false;
  }


  // endregion

  // DB Utilities

  public static function toBool($value){
    if (is_bool($value)){
      return (bool) $value;
    }
    else if (is_string($value) && $value == "true" || $value == "false"){
      if ($value == "true"){
        return true;
      }
      return false;
    }
    else if (is_int($value) && $value == 1 || $value == 0){
      if ($value == 1){
        return true;
      }
      return false;
    }
    return false;
  }

  public static function boolToInt($value){
      $value = self::toBool($value);
      if ($value){
          return 1;
      }
      return 0;
  }

  // endregion
}
?>