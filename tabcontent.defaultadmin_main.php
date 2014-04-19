<?php
#-------------------------------------------------------------------------
# Module: FileMe
# Version: 1.0
#-------------------------------------------------------------------------
#
# Copyright:
#
# IDT Media - Goran Ilic & Tapio Löytty
# Web: www.idt-media.com
# Email: hi@idt-media.com
#
#
# Authors:
#
# Goran Ilic, <ja@ich-mach-das.at>
# Web: www.ich-mach-das.at
#
# Tapio Löytty, <tapsa@orange-media.fi>
# Web: www.orange-media.fi
#
# License:
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------

if ( !is_object(cmsms()))
	exit ;

#---------------------
# Process list
#---------------------
$path = fileme_utils::get_current_working_path();
$files = fileme_utils::index();
$folders = explode('/', $path);

$items = array();
$breacrumbs = array();

// building breadcrumb navigation
for ($i = 0; $i < count($folders); $i++) {
	if ($folders[$i] == '') {
		unset($folders[$i]);
	}
}

$folders = array_values($folders);
$count = count($folders);
$prev = '';

for ($i = 0; $i < $count; $i++) {
	
	$breadcrumb = new stdClass();
	
	$breadcrumb->name = $folders[$i];
	$breadcrumb->url  = '';
	
	if ($folders[$i] != '') {
		
		$back = array($folders[$i]);
		$length = count($back);

		for ($k = 0; $k < $length; $k++) {
			$prev .= $back[$k] . DIR_SEPARATOR;
		}
		unset($back);
		
		if ($i != $count - 1) {
			$breadcrumb->url = $this->CreateLink($id, 'admin_ajax_change_directory', $returnid, $breadcrumb->name, array('previous_dir' => $prev), '', true);
		}
	}
	
	$breadcrumbs[] = $breadcrumb;
}

// building folder and file list
foreach ($files as $file) {

	$item = new stdClass();
	
	$item->modified = $file['modified'];
	$item->name     = $file['name'];
	$item->size     = $file['size'];
	$item->ext      = $file['ext'];
	$item->mime     = $file['mime'];
	$item->type     = $file['type'];
	
	if ($item->type == 'directory') {
		$item->url = $this->CreateLink($id, 'admin_ajax_change_directory', $returnid, $item->name, array('dir' => $path . $item->name), '', true);
	} else {
		$item->url = '';
	}

	$items[] = $item;
}

#---------------------
# Smarty processing
#---------------------

$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('items', $items);

echo $this->ProcessTemplate('main_tab.tpl');
?>