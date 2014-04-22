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
$files = json_decode(fileme_utils::index());
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
			$prev .= $back[$k] . DS;
		}
		unset($back);
		
		if ($i != $count - 1) {
			$breadcrumb->url = $this->CreateLink($id, 'admin_ajax_change_directory', $returnid, '', array('previous_dir' => $prev), '', true);
		}
	}
	
	$breadcrumbs[] = $breadcrumb;
}

// building folder and file list
if ($files->data !== null) {
	foreach ($files->data as $file) {
		
		if ($file->type == 'directory') {
			$file->url = $this->CreateLink($id, 'admin_ajax_change_directory', $returnid, '', array('dir' => $path . $file->name), '', true);
		} else {
			$file->url = $this->CreateLink($id, 'admin_download_file', $returnid, '', array('dir' => $path, 'filename' => $this->encode($file->name), 'mime' => $this->encode($file->mime)), '', true) . '&showtemplate=false';
		}
	}
}

#---------------------
# Smarty processing
#---------------------

$smarty->assign('breadcrumbs', $breadcrumbs);
$smarty->assign('items', $files->data);

echo $this->ProcessTemplate('fileme.main_tab.tpl');
?>