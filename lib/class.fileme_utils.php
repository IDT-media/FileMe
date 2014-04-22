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

	private function __construct()
	{

	}
	
	public static function get_file_mime($file)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime  = finfo_file($finfo, $file); 
		
		finfo_close($finfo);
		
		return $mime;
	}
	
	/**
	 * Encodes a given string to a base64 string
	 */
	public static function encode($string)
	{
		return base64_encode($string);
	}
	
	/**
	 * Decodes a base64 given string back to normal string
	 */
	public static function decode($string)
	{
		return base64_decode($string);
	}

	/**
	 * @description Replaces backslash with slash for Windows setup and prevents Null Byte injection
	 */
	public static function clean_path($path)
	{
		$path = str_replace('\\', '/', $path);
		$path = str_replace(chr(0), '', $path);

		return $path;
	}
	
	/**
	 * @description Converts Bytes to human readable values
	 */
	public static function format_bytes($path)
	{
		if (is_dir($path)) {
			$bytes = self::get_directory_size($path);
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

} // end of class
?>