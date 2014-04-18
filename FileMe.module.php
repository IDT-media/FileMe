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

/*****************************************************************
 MAIN CLASS
*****************************************************************/

class FileMe extends CMSModule
{
	#---------------------
	# Magic methods
	#---------------------		
	
	public function __construct()
	{	
		spl_autoload_register(array(&$this, '_autoloader'));
		
		parent::__construct();	
	}
	
	public function __call($method, $args = array())
	{
		array_unshift($args, '');
		$args[0] = &$this;
		
		return call_user_func_array(array($this->GetName() .'\\ModuleExtensions', $method), $args);	
	}
	
	#---------------------
	# Internal autoloader
	#---------------------	

	final private function _autoloader($classname)
	{	
		$parts = explode('\\', $classname);
		$classname = end($parts);
	
		$fn = $this->GetModulePath()."/lib/class.{$classname}.php";
		if(file_exists($fn)) {
		
			require_once($fn);
		}	
	}
	
	#---------------------
	# Module API methods
	#---------------------	

	public function GetName()
	{
		return get_class($this);
	}

	public function GetFriendlyName()
	{
		return $this->Lang('modulename');
	}

	public function GetAuthor()
	{
		return 'IDT Media Team';
	}

	public function GetAuthorEmail()
	{
		return 'hi@idt-media.com';
	}	
	
	public function GetVersion()
	{
		return '1.0';
	}

	public function MinimumCMSVersion()
	{
		return '1.11';
	}	
	
	public function AllowAutoInstall()
	{
		return false;
	}

	public function AllowAutoUpgrade()
	{
		return false;
	}

	public function IsPluginModule()
	{
		return true;
	}

	public function HasAdmin()
	{
		return true;
	}

	public function GetAdminDescription()
	{
		return $this->Lang('module_description');
	}

	public function VisibleToAdminUser()
	{
		return $this->CheckPermission($this->GetName() . '_manage_fileme');
	}

	public function InstallPostMessage()
	{
		return $this->Lang('postinstall');
	}

	public function UninstallPostMessage()
	{
		return $this->Lang('postuninstall');
	}

	public function UninstallPreMessage()
	{
		return $this->Lang('preuninstall');
	}

	public function GetAdminSection()
	{
		return $this->GetPreference('adminsection', 'extensions');
	}
	
	public function LazyLoadFrontend()
	{
		return true;
	}

	public function LazyLoadAdmin()
	{
		return false; // <- false because stylesheets bug - Core bug, look into it.
	}	
	
	public function InitializeFrontend()
	{
		//$this->RestrictUnknownParams();
	
		// Set allowed parameters
		//$this->SetParameterType('notification', CLEAN_INT);
		//$this->SetParameterType(CLEAN_REGEXP.'/var_.*/',CLEAN_STRING);
	}
	
	public function InitializeAdmin()
	{
		// parameters that can be called in the module tag
		//$this->CreateParameter('notification', '', $this->Lang('help_param_notification'));	
		//$this->CreateParameter('var_*', '', $this->Lang('help_param_var_'));	
	}		
	
	public function GetChangeLog()
	{
		return @file_get_contents(dirname(__FILE__).'/changelog.html');
	}

	public function GetHelp()
	{
		$smarty = cmsms()->GetSmarty();
		
		$smarty->assign('module_path', $this->GetModuleURLPath());
		$smarty->assign('idt_module_help', $this->IDTHelp());

		$smarty->assign('fm_mod', $this);

		return $this->ProcessTemplate('help.tpl');
	}
	
	public function GetHeaderHTML()
	{
		return <<<EOT

EOT;
	}

	public function AdminStyle()
	{
		return $this->addCSS();
	}

	public function DoAction($name,$id,$params,$returnid='')
	{
		$smarty = cmsms()->GetSmarty();

		$smarty->assignByRef('fm_mod', $this);
		$smarty->assignByRef($this->GetName(), $this);
		
		parent::DoAction($name,$id,$params,$returnid);
	}

} // end of class

?>