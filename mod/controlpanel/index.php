<?php

/**
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 
 */
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if ($_SESSION['User']->isLogged()) {
    Layout::add(PHPWS_ControlPanel::display());
}
