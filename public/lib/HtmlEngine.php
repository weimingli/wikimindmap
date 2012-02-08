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
class HtmlEngine extends ParseEngine {
	
 
	public function HtmlEngine($wiki, $topic, $contents) {
		
		parent::__construct ( $wiki, $topic, $contents );
		$this->contents = $contents;
	}
	/**
	 * (non-PHPdoc)
	 * @see ParseEngine::translate()
	 */
	public function translate() {
		 
	
		$preLink='';
		$nextLink='';
		$originalContengs = $this->contents;
		
		 
		
	 	//get next page link
		 
		$nextPageResultStartMark = '" title="Next ';
		
		$findMoreNextResultArray = explode ( $nextPageResultStartMark, $originalContengs );
		if (count ( $findMoreNextResultArray ) > 1) {
			
			$StartPos = strrpos ( $findMoreNextResultArray [0], '"' );
			$nextLink = substr ( $findMoreNextResultArray [0], $StartPos+1 );
			
		}
		 
		// get previous page link
		
		$prePageResultStartMark='" title="Previous ';
	    $findMorePreResultArray = explode ( $prePageResultStartMark, $originalContengs );
		if (count ( $findMorePreResultArray ) > 1) {
			
			$PreStartPos = strrpos ( $findMorePreResultArray [0], '"' );
			$preLink = substr ( $findMorePreResultArray [0], $PreStartPos+1 );
			
		}
		 
		 
		$contentStartMarkFirst = '<li><a href="';
		$contensArray = explode ( $contentStartMarkFirst, $this->contents );
		
		 
		$newContengArray=array();
		for($i = 0; $i < count ( $contensArray ); $i ++) {
			
			if (strpos ( $contensArray [$i], "<div class='searchresult'>" )) {
				
				$findStart = strpos ( $contensArray [$i], '</a>' );
				$paraArray = explode ( '"', substr ( $contensArray [$i], 0, $findStart ) );
				$wikilink = 'http://' . $this->wiki . $paraArray [0];
				$wikiText = $paraArray [2];
				$tooltip = substr ( $paraArray [2], 0 );
 				$finalContent = "\n [[" . $this->cleanText ( $wikiText ) . "]]\n";
				$newContengArray [] = $finalContent;
			}
		}
		
		
		 
		$finalContentString = '';
		//show next navigation link
		if($nextLink){

			$finalContentString.="[[Next Page]]";
			
		}
		//show previous navigation link
		if($preLink){
			
			$finalContentString.="[[Previous Page]]";
			
		}
		for($j = 0; $j < count ( $newContengArray ); $j ++) {
			
			$finalContentString .= $newContengArray [$j];
		}
		 
		 
 		return  $this->contents = $finalContentString;
	}

}
