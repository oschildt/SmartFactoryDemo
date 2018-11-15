<?php
namespace MyApplication;

function approot()
{
  $aroot = __DIR__;
  $aroot = str_replace("\\", "/", $aroot);
  $aroot = str_replace("src", "", $aroot);
  
  return $aroot;
} // approot
