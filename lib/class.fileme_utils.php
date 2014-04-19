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

class fileme_utils
{
	public $root;
	public $path;

	private function __construct()
	{

	}
	
	private function get_file_mime($file)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime  = finfo_file($finfo, $file); 
		
		finfo_close($finfo);
		
		return $mime;
	}

	/**
	 * @description Replaces backslash with slash for Windows setup and prevents Null Byte injection
	 */
	final static public function clean_path($path)
	{
		$path = str_replace('\\', '/', $path);
		$path = str_replace(chr(0), '', $path);

		return $path;
	}
	
	/**
	 * @description Converts Bytes to human readable values
	 */
	final static public function format_bytes($path)
	{
		if (is_dir($path)) {
			$bytes = fileme_utils::get_directory_size($path);
		} else {
			$bytes = sprintf('%u', filesize($path));
		}
		
		if ($bytes > 0) {
			$unit = intval(log($bytes, 1024));
			$units = array('B', 'KB', 'MB', 'GB', 'TB');

			if (array_key_exists($unit, $units) === true) {
				return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
			}
		}
		
		return $bytes;
	}

	/**
	 * @description Iterates through folder of specified directory and returns size of directory
	 */
	public static function get_directory_size($path)
	{
		$size = 0;
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
			$size += $file->getSize();
		}
		
		return $size;
	}
	
	/**
	 * @description Returns current working directory
	 */
	public function get_current_working_path()
	{
		$default = 'uploads' . DIR_SEPARATOR;
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
		$this->path = $this->root . DIR_SEPARATOR . fileme_utils::get_current_working_path();
		
		$dir = fileme_utils::clean_path($this->path);

		return $dir;
	}

	/**
	 * @description Returns directories and files from a given directory
	 */
	public function index()
	{
		$dir = fileme_utils::get_full_working_path();

		if (file_exists($dir)) {
			$index = array();

			if (is_dir($this->path) && $handle = opendir($this->path)) {
				while (false !== ($object = readdir($handle))) {
					if ($object != '.' && $object != '..') {
						if (is_dir($this->path . '/' . $object)) {
							$modified = filemtime($this->path . DIR_SEPARATOR . $object);
							$type     = 'directory';
							$size     = fileme_utils::format_bytes($this->path . DS . $object);
							$ext      = 'dir';
							$mime     = '';
						} else {
							$modified = filemtime($this->path . DIR_SEPARATOR . $object);
							$type     = 'file';
							$size     = fileme_utils::format_bytes($this->path . DS . $object);
							$ext      = pathinfo($object, PATHINFO_EXTENSION);
							$mime     = fileme_utils::get_file_mime($this->path . DS . $object); 
						}
						$index[] = array(
							'modified' => $modified,
							'name'     => $object, 
							'type'     => $type, 
							'size'     => $size,
							'ext'      => $ext,
							'mime'     => $mime
						);
					}
				}

				$folders = array();
				$files = array();
				
				foreach ($index as $item => $data) {
					if ($data['type'] == 'directory') {
						$folders[] = array(
							'modified' => $data['modified'], 
							'name'     => $data['name'], 
							'type'     => $data['type'], 
							'size'     => $data['size'],
							'ext'      => $data['ext'],
							'mime'     => $data['mime']
						);
					}
					if ($data['type'] == 'file') {
						$files[] = array(
							'modified' => $data['modified'], 
							'name'     => $data['name'], 
							'type'     => $data['type'], 
							'size'     => $data['size'],
							'ext'      => $data['ext'],
							'mime'     => $data['mime']
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

				return $output;
			}
		}
	}

} // end of class
?>