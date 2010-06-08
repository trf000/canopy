<?php

/**
 * Wiki for phpWebSite
 *
 * See docs/CREDITS for copyright information
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author      Greg Meiste <blindman1344@NOSPAM.users.sourceforge.net>
 * @version $Id: controlpanel.php,v 1.2 2007/05/28 19:00:14 blindman1344 Exp $
 */

$link[] = array('label'       => dgettext('wiki', 'Wiki'),
                'restricted'  => FALSE,
                'url'         => 'index.php?module=wiki',
                'description' => dgettext('wiki', 'Browse and maintain the Wiki on this site.'),
                'image'       => 'wiki.png',
                'tab'         => 'content'
               );

?>