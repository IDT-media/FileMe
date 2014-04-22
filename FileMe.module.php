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
	public $root;
	public $path;
	public $message;
	public $data;
	public $status;
	
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
/*
	public function GetHelp()
	{
		$smarty = cmsms()->GetSmarty();
		
		$smarty->assign('module_path', $this->GetModuleURLPath());
		$smarty->assign('idt_module_help', $this->IDTHelp());

		$smarty->assign('mod', $this);

		return $this->ProcessTemplate('help.tpl');
	}
	*/
	public function GetHeaderHTML()
	{
		return <<<EOT
<script src="{$this->GetModuleURLPath()}/lib/js/fileme.functions.js"></script>
EOT;
	}

	public function AdminStyle()
	{
		return $this->addCSS();
	}

	public function DoAction($name,$id,$params,$returnid='')
	{
		$smarty = cmsms()->GetSmarty();

		$smarty->assignByRef('mod', $this);
		$smarty->assignByRef($this->GetName(), $this);
		
		parent::DoAction($name,$id,$params,$returnid);
	}

	#---------------------
	# Custom Module methods
	#---------------------

		/**
	 * @description Returns current working directory
	 */
	public function get_current_working_path()
	{
		//TODO Handle module default settings and advanced permissions
		$default = 'uploads' . DS;
		$this->path = cms_userprefs::get('fileme_working_directory', $default);
		
		$dir = fileme_utils::clean_path($this->path);

		return $dir;
	}
	
	/**
	 * @description Returns full path to working directory
	 */
	public function get_full_working_path()
	{
		$config = cms_utils::get_config();

		$this->root = $config['root_path'];
		$this->path = $this->root . DS . self::get_current_working_path();
		
		$dir = fileme_utils::clean_path($this->path);

		return $dir;
	}

	/**
	 * @description Returns directories and files from a given directory
	 */
	public function index()
	{
		$dir = self::get_full_working_path();

		if (file_exists($dir)) {
			$index = array();

			if (is_dir($this->path) && $handle = opendir($this->path)) {
				while (false !== ($object = readdir($handle))) {
					if ($object != '.' && $object != '..') {
						
						$modified   = filemtime($this->path . DS . $object);
						$size       = fileme_utils::format_bytes($this->path . DS . $object);
						$permission = substr(sprintf('%o', fileperms($this->path . DS . $object)), -4);
						
						if (is_dir($this->path . DS . $object)) {
							$type = 'directory';
							$ext  = 'dir';
							$mime = '';
						} else {
							$type = 'file';
							$ext  = pathinfo($object, PATHINFO_EXTENSION);
							$mime = fileme_utils::get_file_mime($this->path . DS . $object); 
						}
						$index[] = array(
							'modified'   => $modified,
							'name'       => $object, 
							'type'       => $type, 
							'size'       => $size,
							'ext'        => $ext,
							'mime'       => $mime,
							'permission' => $permission
						);
					}
				}

				$folders = array();
				$files = array();
				
				foreach ($index as $item => $data) {
					if ($data['type'] == 'directory') {
						$folders[] = array(
							'modified'  => $data['modified'], 
							'name'       => $data['name'], 
							'type'       => $data['type'], 
							'size'       => $data['size'],
							'ext'        => $data['ext'],
							'mime'       => $data['mime'],
							'permission' => $data['permission']
						);
					}
					if ($data['type'] == 'file') {
						$files[] = array(
							'modified'   => $data['modified'], 
							'name'       => $data['name'], 
							'type'       => $data['type'], 
							'size'       => $data['size'],
							'ext'        => $data['ext'],
							'mime'       => $data['mime'],
							'permission' => $data['permission']
						);
					}
				}

				// TODO - sort by date, filename ascending/descending, filesize??
				function sorter($a, $b, $key = 'name')
				{
					return strnatcmp($a[$key], $b[$key]);
				}

				usort($folders, 'sorter');
				usort($files, 'sorter');

				$output = array_merge($folders, $files);

				$this->status = 'success';
				
				if (!count($output)) {
					$this->message = 'Directory is empty';
				}
				$this->data = $output;
			} else {
				$this->status = 'error';
				$this->message = 'Directory does not exist';
				$this->data = null;
			}
		} else {
			$this->status = 'error';
			$this->message = 'File or path does not exist';
			$this->data = null;
		}
		
		return $this->response($this->status, $this->message, $this->data);
	}

	/**
	 * Handles a response with given information by status, message and data and returns a JSON encoded data
	 */
	public function response($status, $message, $data){
			
		if ($this->data) {
			$json = json_encode(array('status' => $this->status, 'message' => $this->message, 'data' => $this->data));
		} else {
			$json = json_encode(array('status' => $this->status, 'message' => $this->message, 'data' => null));
		}

		return $json;
	}

} // end of class

?>