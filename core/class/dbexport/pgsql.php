<?php

function export(&$info){
  echo phpws_debug::testarray($info);
  switch ($info['type']){

  case "int8":
  case "int4":
    $setting = "INT";
    $info['flags'] = preg_replace("/unique primary/", "PRIMARY KEY", $info['flags']);
    break;

  case "int2":
    $setting = "SMALLINT";
    break;

  case "text":
  case "blob":
    $setting = "TEXT";
    $info['flags'] = NULL;
    break;
    
  case "bpchar":
    $setting = "CHAR(255)";

    if (empty($info['flags']))
      $info['flags'] = "NULL";
    break;
    
  case "date":
    $setting = "DATE";
    break;
    
  case "real":
    $setting = "FLOAT";
    break;
    
  case "timestamp":
    $setting = "TIMESTAMP";
    $info['flags'] = NULL;
    break;
  }
  return $setting;
}

?>