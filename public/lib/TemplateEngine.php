<?php
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 1, or (at your option)
# any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
/**
 *	© Copyright 2011 Hewlett-Packard Development Company L.P. 
 */

require_once 'ParseEngine.php';
require_once 'Common.php';
class TemplateEngine extends ParseEngine {
	
	public function TemplateEngine($wiki, $topic, $contents,$templateName) {
		parent::ParseEngine ( $wiki, $topic, $contents );
		$this->templateName=$templateName;
	}
	
	public function translate() {
		 

		$this->templateName=trim($this->templateName);
		
		$this->templateName=str_replace(' ', '_', $this->templateName);
	 
 
		$templateWikiUrl = 'http://' . $this->wiki .  '/w/index.php?title=Template:' . $this->templateName . '&action=raw';
		 
		$templateFileSource=GetContentsByUrl($templateWikiUrl);
   	 
		return $this->contents=$this->removeClassInfo($templateFileSource);
	     
 
		//we will tranlate '| something = some value' to '== something  ==' at here
		$templateParaStartMark = '|';
		$templateParaEndMark = ' = ';
		 
		
		$this->contents = str_replace ( '{{ Product Brief', '', $this->contents );
		$this->contents = str_replace ( '{{Product Brief', '', $this->contents );
		$this->contents = str_replace ( '}}', '', $this->contents );
		
		$this->contents = str_replace ( $templateParaEndMark, ' ==', $this->contents );
		$this->contents = str_replace ( $templateParaStartMark, '==', $this->contents );
		
		return $this->contents;
	}

}
