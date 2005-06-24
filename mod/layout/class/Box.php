<?php

class Layout_Box {
    var $id          = NULL;
    var $theme       = NULL; 
    var $content_var = NULL;
    var $module      = NULL;
    var $theme_var   = NULL;
    var $box_order   = NULL;
    var $active      = NULL;

    function Layout_Box($id=NULL){
        if (!isset($id))
            return;

        $this->setID($id);
        $result = $this->init();
        if (PEAR::isError($result))
            PHPWS_Error::log($result);
    }

    function init(){
        $DB = new PHPWS_DB('layout_box');
        return $DB->loadObject($this);
    }

    function setID($id){
        $this->id = (int)$id;
    }

    function getID(){
        return $this->id;
    }

    function setTheme($theme){
        $this->theme = $theme;
    }

    function setContentVar($content_var){
        $this->content_var = $content_var;
    }

    function setModule($module){
        $this->module = $module;
    }

    function setThemeVar($theme_var){
        $this->theme_var = $theme_var;
    }

    function getTheme(){
        return $this->theme;
    }

    function getContentVar(){
        return $this->content_var;
    }

    function getModule(){
        return $this->module;
    }

    function getThemeVar(){
        return $this->theme_var;
    }

    function getBoxOrder(){
        return $this->box_order;
    }

    function setBoxOrder($order){
        $this->box_order = $order;
    }

    function save(){
        $db = new PHPWS_DB('layout_box');
        $db->addWhere('module', $this->module);
        $db->addWhere('content_var', $this->content_var);
        $db->addWhere('theme', $this->theme);
        $result = $db->select('one');

        if (PEAR::isError($result)) {
            return $result;
        } elseif (!empty($result) && $result != $this->id) {
            return FALSE;
        }

        $db->reset();

        if (!isset($this->box_order)) {
            $this->box_order = $this->nextBox();
        }

        if (!isset($this->active))
            $this->active = 1;

        return $db->saveObject($this);
    }

    function moveUp(){
        $db = & new PHPWS_DB('layout_box');
        $db->addWhere('id', $this->getID(), '!=');
        $db->addWhere('theme', $this->getTheme());
        $db->addWhere('theme_var', $this->getThemeVar());
        $db->setIndexBy('box_order');
        $boxes = $db->getObjects('Layout_Box');

        if (!isset($boxes))
            return;

        $db->addColumn('box_order', NULL, 'min');
        $max = $db->select('one');
        $oldOrder = $this->getBoxOrder();
        $newOrder = $oldOrder - 1;

        if ($oldOrder == 1){
            $this->setBoxOrder($max + 1);
            $this->save();
        }
        else {
            $this->setBoxOrder($newOrder);
            $this->save();
            $boxes[$newOrder]->setBoxOrder($oldOrder);
            $boxes[$newOrder]->save();
        }
    }

    function moveDown(){
        $db = & new PHPWS_DB('layout_box');
        $db->addWhere('id', $this->getID(), '!=');
        $db->addWhere('theme', $this->getTheme());
        $db->addWhere('theme_var', $this->getThemeVar());
        $db->setIndexBy('box_order');
        $boxes = $db->getObjects('Layout_Box');

        if (!isset($boxes))
            return;

        $db->addColumn('box_order');
        $max = $db->select('max');
        $oldOrder = $this->getBoxOrder();
        $newOrder = $oldOrder + 1;

        if ($oldOrder == ($max + 1)){
            $this->setBoxOrder(0);
            $this->save();
        }
        else {
            $this->setBoxOrder($newOrder);
            $this->save();
            $boxes[$newOrder]->setBoxOrder($oldOrder);
            $boxes[$newOrder]->save();
        }
    }


    function reorderBoxes($theme, $themeVar){
        $db = & new PHPWS_DB('layout_box');
        $db->addWhere('theme', $theme);
        $db->addWhere('theme_var', $themeVar);
        $db->addOrder('box_order');
        $boxes = $db->getObjects('Layout_Box');

        if (!isset($boxes))
            return;

        $count = 1;
        foreach ($boxes as $box){
            $box->setBoxOrder($count);
            $box->save();
            $count++;
        }
    }

    function nextBox(){
        $DB = new PHPWS_DB('layout_box');
        $DB->addWhere('theme', $this->getTheme());
        $DB->addWhere('theme_var', $this->getThemeVar());
        $DB->addColumn('box_order');
        $max = $DB->select('max');
        if (isset($max)) {
            return $max + 1;
        } else {
            return 1;
        }
    }

    function kill(){
        $theme_var = $this->getThemeVar();
        $theme = $this->getTheme();

        $db = & new PHPWS_DB('layout_box');
        $db->addWhere('id', $this->getId());
        $result = $db->delete();
  
        if (PEAR::isError($result))
            return $result;

        Layout_Box::reorderBoxes($theme, $theme_var);
    }
  
}
?>