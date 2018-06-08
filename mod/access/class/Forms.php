<?php

/**
 * Administrative forms for the Access module
 *
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 
 */
class Access_Forms {

    public static function shortcuts()
    {
        if (!Current_User::allow('access')) {
            Current_User::disallow();
            return;
        }

        $modal = new \phpws2\Modal('access-shortcut', null,
                'Shortcuts');
        $modal->sizeSmall();
        $button = '<button class="btn btn-success" id="save-shortcut">Save</button>';
        $modal->addButton($button);
        \Layout::add((string) $modal);
        \Layout::includeJavascript('mod/access/javascript/access.min.js');

        \phpws\PHPWS_Core::initModClass('access', 'Shortcut.php');
        \phpws\PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('access_shortcuts', 'Access_Shortcut');
        $pager->setModule('access');
        $pager->setTemplate('forms/shortcut_list.tpl');
        $pager->setLink('index.php?module=access&amp;tab=shortcuts');
        $pager->addToggle('class="bgcolor1"');
        $pager->setSearch('keyword');

        $form = new PHPWS_Form('shortcut_list');
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_shortcut_list');

        $options['none'] = '';
        if (Current_User::allow('access', 'admin_options')) {
            $options['active'] = 'Activate';
            $options['deactive'] = 'Deactivate';
        }

        $options['delete'] = 'Delete';
        $form->addSelect('list_action', $options);

        $page_tags = $form->getTemplate();

        $page_tags['MENU_FIX'] = PHPWS_Text::secureLink('Update menu links', 'access',
                        array('command' => 'menu_fix'));
        $page_tags['PAGE_FIX'] = PHPWS_Text::secureLink('Shortcut all pages', 'access',
                        array('command' => 'page_fix'));

        if (PHPWS_Settings::get('access', 'forward_ids')) {
            $page_tags['PAGE_FORWARDING'] = PHPWS_Text::secureLink('Turn OFF autoforwarding of Pagesmith id pages',
                            'access', array('command' => 'autoforward_off'));
        } else {
            $page_tags['PAGE_FORWARDING'] = PHPWS_Text::secureLink('Turn ON autoforwarding of Pagesmith id pages',
                            'access', array('command' => 'autoforward_on'));
        }

        $page_tags['MENU_WARNING'] = 'This change is irreversable. Please backup menu_links prior to running it.';
        $page_tags['URL_LABEL'] = 'Url';
        $page_tags['ACTIVE_LABEL'] = 'Active?';
        $page_tags['ACTION_LABEL'] = 'Action';
        $page_tags['CHECK_ALL_SHORTCUTS'] = javascript('check_all',
                array('checkbox_name' => 'shortcut[]'));

        $js_vars['value'] = 'Go';
        $js_vars['select_id'] = $form->getId('list_action');
        $js_vars['action_match'] = 'delete';
        $js_vars['message'] = 'Are you sure you want to delete the checked shortcuts?';
        $page_tags['SUBMIT'] = javascript('select_confirm', $js_vars);

        $pager->addPageTags($page_tags);
        $pager->addRowTags('rowTags');

        $content = $pager->get();
        return $content;
    }

    public static function denyAllowForm()
    {
        if (!Current_User::allow('access', 'admin_options')) {
            Current_User::disallow();
            return;
        }

        \phpws\PHPWS_Core::initModClass('access', 'Allow_Deny.php');

        $form = new PHPWS_Form('allow_deny');
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_deny_allow');

        $form->addCheck('allow_deny_enabled', 1);
        $form->setMatch('allow_deny_enabled',
                PHPWS_Settings::get('access', 'allow_deny_enabled'));
        $form->setLabel('allow_deny_enabled',
                'Allow/Deny enabled');
        $form->addSubmit('go', 'Go');

        $result = Access::getAllowDeny();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
        }

        $form->addText('allow_address');
        $form->addText('deny_address');
        $form->addSubmit('add_allow_address',
                'Add allowed IP');
        $form->addSubmit('add_deny_address', 'Add denied IP');

        $db = new PHPWS_DB('access_allow_deny');
        $result = $db->getObjects('Access_Allow_Deny');

        $options['none'] = '-- Choose option --';
        $options['active'] = 'Activate';
        $options['deactive'] = 'Deactivate';
        $options['delete'] = 'Delete';

        if (PHPWS_Settings::get('access', 'allow_all')) {
            $allow_all = TRUE;
            $options['allow_all'] = 'Do not allow all';
        } else {
            $allow_all = FALSE;
            $options['allow_all'] = 'Allow all';
        }

        $form->addSelect('allow_action', $options);

        unset($options['allow_all']);

        if (PHPWS_Settings::get('access', 'deny_all')) {
            $deny_all = TRUE;
            $options['deny_all'] = 'Do not deny all';
        } else {
            $deny_all = FALSE;
            $options['deny_all'] = 'Deny all';
        }
        $form->addSelect('deny_action', $options);

        $template = $form->getTemplate();

        if ($allow_all) {
            $template['ALLOW_ALL_MESSAGE'] = 'You have "Allow all" enabled. All rows below will be ignored.';
        }

        if ($deny_all) {
            $template['DENY_ALL_MESSAGE'] = 'You have "Deny all" enabled. All rows below will be ignored.';
        }

        $js_vars['value'] = 'Go';
        $js_vars['action_match'] = 'delete';
        $js_vars['message'] = 'Are you sure you want to delete the checked ips?';

        $js_vars['select_id'] = 'allow_deny_allow_action';
        $template['ALLOW_ACTION_SUBMIT'] = javascript('select_confirm', $js_vars);

        $js_vars['select_id'] = 'allow_deny_deny_action';
        $template['DENY_ACTION_SUBMIT'] = javascript('select_confirm', $js_vars);


        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
            return 'An error occurred when trying to access the allowed and denied ip records. Please check your logs.';
        } elseif (empty($result)) {
            $template['DENY_MESSAGE'] = 'No denied ip addresses found.';
            $template['ALLOW_MESSAGE'] = 'No allowed ip addresses found.';
        } else {
            foreach ($result as $allow_deny) {
                $action = PHPWS_Text::secureLink('Delete',
                                'access',
                                array('ad_id' => $allow_deny->id, 'command' => 'delete_allow_deny'));
                if ($allow_deny->active) {
                    $active = 'Yes';
                } else {
                    $active = 'No';
                }

                if ($allow_deny->allow_or_deny) {
                    $check = sprintf('<input type="checkbox" name="allows[]" value="%s" />',
                            $allow_deny->id);
                    $template['allow_rows'][] = array('ALLOW_CHECK' => $check,
                        'ALLOW_IP_ADDRESS' => $allow_deny->ip_address,
                        'ALLOW_ACTIVE' => $active,
                        'ALLOW_ACTION' => $action);
                } else {
                    $check = sprintf('<input type="checkbox" name="denys[]" value="%s" />',
                            $allow_deny->id);
                    $template['deny_rows'][] = array('DENY_CHECK' => $check,
                        'DENY_IP_ADDRESS' => $allow_deny->ip_address,
                        'DENY_ACTIVE' => $active,
                        'DENY_ACTION' => $action);
                }
            }

            if (empty($template['allow_rows'])) {
                $template['ALLOW_MESSAGE'] = 'No allowed ip addresses found.';
            }

            if (empty($template['deny_rows'])) {
                $template['DENY_MESSAGE'] = 'No denied ip addresses found.';
            }
        }

        $template['CHECK_ALL_ALLOW'] = javascript('check_all',
                array('checkbox_name' => 'allows'));
        $template['CHECK_ALL_DENY'] = javascript('check_all',
                array('checkbox_name' => 'denys'));
        $template['ACTIVE_LABEL'] = 'Active?';
        $template['ALLOW_TITLE'] = 'Allowed IPs';
        $template['DENY_TITLE'] = 'Denied IPs';
        $template['ACTION_LABEL'] = 'Action';
        $template['IP_ADDRESS_LABEL'] = 'IP Address';
        $template['WARNING'] = 'Remember to "Update" your access file when finished changing IP rules.';

        return PHPWS_Template::process($template, 'access',
                        'forms/allow_deny.tpl');
    }

    public static function shortcut_menu()
    {
        \phpws\PHPWS_Core::initModClass('access', 'Shortcut.php');

        $sch_id = filter_input(INPUT_GET, 'sch_id', FILTER_SANITIZE_NUMBER_INT);

        if ($sch_id === false) {
            $sch_id = 0;
        }

        if (!$sch_id) {
            @$key_id = $_REQUEST['key_id'];
            if (!$key_id) {
                javascript('close_window');
                return;
            } else {
                $shortcut = new Access_Shortcut;
                $key = new \Canopy\Key($key_id);
                if (!$key->id) {
                    javascript('close_window');
                    return;
                }
                $shortcut->keyword = trim(preg_replace('/[^\w\s\-]/', '',
                                $key->title));
            }
        } else {
            $shortcut = new Access_Shortcut($sch_id);
            if (!$shortcut->id) {
                return 'Error: shortcut not found';
            }
        }

        $form = new \phpws2\Form;
        $form->setAction('index.php');
        $form->appendCSS('bootstrap');
        $form->setId('shortcut-menu');
        $form->addHidden('authkey', \Current_User::getAuthKey());
        $form->addHidden('module', 'access');
        $form->addHidden('command', 'post_shortcut');
        if (isset($key_id)) {
            $form->addHidden('key_id', $key_id);
        } else {
            $form->addHidden('sch_id', $shortcut->id);
        }

        $keyword = $form->addTextField('keyword', $shortcut->keyword)->setRequired();
        $keyword->setPlaceholder('Type in a keyword');
        $tpl = $form->getInputStringArray();

        $template = new \phpws2\Template($tpl);
        $template->setModuleTemplate('access', 'shortcut_menu.tpl');
        $content = $template->render();
        return $content;
    }

}
