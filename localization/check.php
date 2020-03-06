<!DOCTYPE html>
<html lang="en">
<head>
<title>Language tester</title>
</head>
<body>
<h2>Language tester</h2>

<?php
//-----------------------------------------------------------------
function load_language_texts()
{
    global $messages;
    
    global $supported_languages;
    global $text_entries;
    global $language_entries;
    global $country_entries;
    
    global $incomplete_translations;
    
    $json = file_get_contents("texts.json");
    if ($json === false) {
        $messages .= "<p style='color:red; font-weight: bold'>Translation file 'localization/texts.json' cannot be loaded!</p>";
        return false;
    }
    
    $json_array = json_decode($json, true);
    if ($json_array === null) {
        $messages .= "<p style='color:red; font-weight: bold'>Translation file 'localization/texts.json' cannot be loaded!</p>";
        return false;
    }
    
    if (!empty($json_array["interface_languages"])) {
        foreach ($json_array["interface_languages"] as $lang_code) {
            $supported_languages[$lang_code] = $lang_code;
        }
    }
    
    if (!empty($json_array["texts"])) {
        $text_entries = $json_array["texts"];
        
        foreach ($text_entries as $text_id => &$translations) {
            foreach ($supported_languages as $lng) {
                if (empty($translations[$lng])) {
                    $incomplete_translations[$text_id][] = $lng;
                }
            }
        }
    }
    
    if (!empty($json_array["languages"])) {
        $language_entries = $json_array["languages"];
    }
    
    if (!empty($json_array["countries"])) {
        $country_entries = $json_array["countries"];
    }
    
    return true;
} // load_language_texts
//-----------------------------------------------------------------
function process_file($file)
{
  global $found_entries;
  global $text_entries;
  global $missing_definitions;
  
  $handle = fopen($file, "rt");
  if(!$handle)
  {
    echo "<p style='color:red; font-weight: bold'>Error by opening file '$file' for reading!</p>";
    return false;
  }

  $length = filesize($file);

  if($length == 0) return true;

  $contents = fread($handle, $length);
  fclose($handle);

  if(preg_match_all("/[^a-zA-Z0-9_]text\\(\\s*(\"([^\"]+)\"|'([^']+)')/", $contents, $matches, PREG_SET_ORDER))
  {
    foreach($matches as $nr => $match)
    {
      /*
      echo "<pre>";
      print_r($match);
      echo "</pre>";
      echo "-----------------------------<br/>";
      */

      $entry = "";

      if(!empty($match[2])) $entry = $match[2];
      if(!empty($match[3])) $entry = $match[3];
      
      if(empty($entry)) continue;
      
      $found_entries[$entry] = $entry;
      
      if(empty($text_entries[$entry]))
      {
        $f = str_replace("../", "", $file);
        
        $missing_definitions[$entry][$f] = $f;
      }
      
    } // foreach
  } // if preg
  
  return true;
} // process_file
//-----------------------------------------------------------------
function process_dir($dir)
{
  global $files_processed;
  
  $files = scandir($dir);
  foreach($files as $file)
  {
    if($file == "." || $file == ".." || $file == "vendor") continue;

    if(is_dir($dir . $file))
    {
      if(!process_dir($dir . $file . "/")) return false;
      continue;
    }

    $path_parts = pathinfo($dir . $file);
    if(!empty($path_parts['extension']) && $path_parts['extension'] == "php")
    {
      $files_processed++;
      if(!process_file($dir . $file)) return false;
    }
  }

  return true;
} // process_dir
//-----------------------------------------------------------------

$files_processed = 0;

$supported_languages = [];
$text_entries = [];
$found_entries = [];
$text_entry_doublicates = [];
$incomplete_translations = [];
$missing_definitions = [];
$never_used = [];

$success = false;

if(load_language_texts())
{
  $dir = "../../";
  if(process_dir($dir))
  {
    echo "Supported languages: " . implode(", ", $supported_languages) . "<br>";
    echo "Entries found: " . count($text_entries) . "<br>";
    echo "Files processed: " . $files_processed . "<br>";
    
    $success = true;
    
    if(!empty($text_entry_doublicates))
    {
      $success = false;
      
      echo("<p style='color:maroon; font-weight: bold'>Doublicates found!</p>");
      
      foreach($text_entry_doublicates as $dbl)
      {
        echo($dbl . "<br>");        
      }
    }

    if(!empty($incomplete_translations))
    {
      $success = false;

      echo("<p style='color:maroon; font-weight: bold'>Incomplete translations found!</p>");
      
      foreach($incomplete_translations as $text => $langs)
      {
        echo($text . ": " . implode(", ", $langs) . "<br>");        
      }
    }
    
    if(!empty($missing_definitions))
    {
      $success = false;

      echo("<p style='color:maroon; font-weight: bold'>Missing definitions found!</p>");
      
      foreach($missing_definitions as $text => $files)
      {
        echo($text .  ": " . implode(", ", $files) . "<br>");        
      }
    }
    
    $never_used = array_diff(array_keys($text_entries), $found_entries);
    if(!empty($never_used))
    {
      $success = false;

      echo("<p style='color:maroon; font-weight: bold'>These definitions are never used!</p>");
      
      foreach($never_used as $text)
      {
        echo($text . "<br>");        
      }
    }
  }
}

if($success)
{
  echo("<p style='color:green; font-weight: bold'>All is fine!</p>");
}
?>
</body>
</html>