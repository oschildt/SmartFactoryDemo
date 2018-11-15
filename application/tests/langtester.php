<?php
use function SmartFactory\approot;

//-----------------------------------------------------------------
require_once "../includes/SmartFactory/application_root_inc.php";
//-----------------------------------------------------------------
?><!DOCTYPE html>
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
  global $text_entries;
  global $text_entry_doublicates;
  global $supported_languages;
  global $incomplete_translations;
  
  $xmldoc = new DOMDocument();
  
  if(!@$xmldoc->load(approot() . "localization/texts.xml"))
  {
    echo("<p style='color:red; font-weight: bold'>Translation file 'localization/texts.xml' cannot be loaded!</p>");
    return false;
  }
  
  $xsdpath = new DOMXPath($xmldoc);
  
  $nodes = $xsdpath->evaluate("/document/interface_languages/language");
  foreach($nodes as $node)
  {
    $lang_code = $node->getAttribute("id");
    if(!empty($lang_code)) $supported_languages[$lang_code] = $lang_code;
  }
  
  $nodes = $xsdpath->evaluate("/document/texts/text");
  foreach($nodes as $node)
  {
    $text_id = $node->getAttribute("id");
    if(empty($text_id)) continue;
    
    if(!empty($text_entries[$text_id]))
    {
      $text_entry_doublicates[$text_id] = $text_id;
    }
    
    $text_entries[$text_id] = [];

    foreach($node->childNodes as $child)
    {
      if($child->nodeName == '#text') continue;
      
      $text_entries[$text_id][$child->nodeName] = trim($child->nodeValue);
    }
    
    foreach($supported_languages as $lng)
    {
      if(empty($text_entries[$text_id][$lng]))
      {
        $incomplete_translations[$text_id][] = $lng;
      }
    }    
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
        $f = str_replace(approot(), "", $file);
        
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
    if($file == "." || $file == "..") continue;

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
  $dir = approot();
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