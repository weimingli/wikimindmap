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
define ('DEFAULT_ENGINE_CODE',1);
define ('TEMPLATE_ENGINE_CODE',2);
define ('HTML_ENGINE_CODE',3);
define ('UNKNOWN_TEMPLATE',4);
class MyEngine {
	
	function __construct($wiki, $topic) {
		$this->wiki = $wiki;
		$this->topic = str_replace(' ','_',$topic);
	 
	}
	
	function getEngine() {
		
		//Wiki specific Variables
		$index_path = "";
		$access_path = "";
		
		switch ($this->wiki) {
			case "hpedia.hp.com" :
				$index_path = "/w";
				$access_path = "/wiki";
				break;
			case "hpedia-dev.fc.hp.com" :
				$index_path = "/w";
				$access_path = "/wiki";
				break;
			default :
				$index_path = "/w";
				$access_path = "/wiki";
				break;
		}
		
		 
		 		
		$url = 'http://' . $this->wiki . $index_path . '/index.php?title=' . $this->topic . '&action=raw';
	  
		  
		//-------------------------------------------------------------------------------------------
		// Defaults for the Parser
		//-------------------------------------------------------------------------------------------
		

		$catStart = '{';
		$catEnd = '}';
		
		$chapStart = '==';
		$chapEnd = '==';
		$subChapStart = '===';
		$subChapEnd = '===';
		$linkStart = '[[';
		$linkEnd = ']]';
		$wwwLinkStart = '[http:';
		$wwwLinkEnd = ']';
		
		//added by thomas
		$templateNameStart = '{{';
		$defaultTemplateStart = '{{Shortcut';
		$defaultTemplateEnd = '}}';
		
		//-------------------------------------------------------------------------------------------
		// Extract the main Topic from the Wikki
		// This code works only for mediawiki type of wikis later following changes are to be done:
		// replace $wiki by a wiki class, representing a wiki-tpye
		// ad an extract_topc(wikiclass) function to get the wiki page
		// Typical WikiMedia URL http://de.wikipedia.org/w/index.php?title=Automobil&action=edit
		//-------------------------------------------------------------------------------------------
		

		$ch = curl_init ();
		$timeout = 5; // set to zero for no timeout
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		$contents = curl_exec ( $ch );
		curl_close ( $ch );
		
		// Decode from UTF-8
		$contents = utf8_decode ( $contents );
		
		  
		 if( trim($contents)==''){
		 	return HTML_ENGINE_CODE;		 	
		 }
		if(strpos($contents, 'There is currently no text in this page')>-1 ||strpos($contents, 'Are you looking for')>-1 || strpos($contents, 'The requested page title was invalid, empty, or an incorrectly linked inter-language or inter-wiki title')>-1){
			
			return HTML_ENGINE_CODE;
		}
		// If this is a search result page, we'll parse it directly
		if ($this->topic == 'Pre_Page' || $this->topic == 'Next_Page') {
			
			return HTML_ENGINE_CODE;
		  
		}
		
		$contents = $this->removeComments ( $contents );
		
		//we will know whether this is a page using default template or not, added by thomas
		if (! strpos ( $contents, $defaultTemplateStart ) && strpos ( $contents, $templateNameStart ) > - 1) {
			
			//	get the template file name  
			return TEMPLATE_ENGINE_CODE;
 
		}
		
 		$contents = $this->removeClassInfo ( $contents );
		//if the source of the page uses the default template 
		if (strlen ( $contents ) > 0) {
			
			return DEFAULT_ENGINE_CODE;
			 
		}
		
		if (strlen ( $contents ) == 0) {
			
			return HTML_ENGINE_CODE;
		}
	}
	
	//-------------------------------------------------------------------------------------------
	// Functions to clean text from special caracters
	//-------------------------------------------------------------------------------------------
	

	static function cleanText($text) {
		$trans = array ("=" => "", "[" => "", "]" => "", "{" => "", "}" => "", "_" => " ", "'" => "", "|" => "/", "?" => "", "*" => "-", "\"" => "'" );
		$clean = strtr ( $text, $trans );
		// Experimental remove a lot of reutrns (\n)
		$transW = array ("\n\n\n" => "" );
		$clean = strtr ( $clean, $transW );
		return $clean;
	}
	
	static function cleanWikiLink($text) {
		$trans = array ("=" => "", "[" => "", "]" => "", "{" => "", "}" => "" );
		$clean = strtr ( $text, $trans );
		return $clean;
	}
	
	//-------------------------------------------------------------------------------------------
	// Functions to create ToolTip Text
	// Strategy: Text until the next chapter starts, but no more than n (100?) characters.
	//-------------------------------------------------------------------------------------------
	

	static function createToolTipText($text, $len) {
		global $chapStart;
		//echo '<br> TTTEXT: '.$text;
		$tttext = removeTags ( $text );
		//echo '<br> TTTEXT: '.$tttext;
		$i = $len;
		if (strpos ( $text, $chapStart ) > - 1) {
			$i = min ( strpos ( $text, $chapStart ), $len );
		}
		$tttext = substr ( $tttext, 0, $i );
		$tttext = cleanText ( $tttext );
		$tttext = trim ( $tttext );
		//echo '<br> TTTEXT: '.$tttext;
		//echo $tttext;
		return $tttext . ' [...]';
	}
	
	// This alghoritm maybe should by used for all the parsing
	static function removeClassInfo($text) {
		
		global $catStart, $catEnd;
		
		$n = strpos ( $text, $catStart );
		while ( $n > - 1 ) {
			$o = strpos ( $text, $catStart, $n + 1 );
			$c = strpos ( $text, $catEnd, $n + 1 );
			
			if ($c > - 1 && ($c < $o || ! $o)) {
				
				$stringNeedsToBeCleared = substr ( $text, $n, $c + 1 - $n );
 				preg_match_all ( '/={2,10}/', $stringNeedsToBeCleared, $matches );
				$chapterMarksArray = $matches [0];
				
 				$delimiterNumber = count ( $chapterMarksArray ) / 2;
				if ($chapterMarksArray > 0 && is_int ( $delimiterNumber )) {
					
					$chapterMarksArray = array_unique ( $chapterMarksArray );
					
					foreach ( $chapterMarksArray as $key => $value ) {
						
						preg_match_all ( "[$value.*?$value]", $stringNeedsToBeCleared, $stringNeedsToBeSavedArray );
						
 						

						$newStringNeedsToBeCleared = str_replace ( $stringNeedsToBeSavedArray [0] [0], '', $stringNeedsToBeCleared );
 						

						$textArray = explode ( $stringNeedsToBeCleared, $text );
						$text = $textArray [0] . $newStringNeedsToBeCleared . $stringNeedsToBeSavedArray [0] [0] . $textArray [1];
					
					}
				
				}
				
				$o = strpos ( $text, $catStart, $n + 1 );
				$c = strpos ( $text, $catEnd, $n + 1 );
				
				$text = substr_replace ( $text, "", $n, $c + 1 - $n );
				$n = strpos ( $text, $catStart );
			} else {
				$n = $o;
			}
		}
		
		return $text;
	}
	
	static function removeComments($text) {
		$cStart = "<";
		$cEnd = ">";
		$n = strpos ( $text, $cStart );
		
		while ( $n > - 1 ) {
			
			$o = strpos ( $text, $cStart, $n + strlen ( $cStart ) );
			$c = strpos ( $text, $cEnd, $n + strlen ( $cStart ) );
			if ($c > - 1 && ($c < $o || ! $o)) {
				
				$text = substr_replace ( $text, "", $n, $c + strlen ( $cEnd ) - $n );
				$n = strpos ( $text, $cStart );
			} else {
				$n = $o;
			}
		}
		return $text;
	}
	
	static function removeTags($text) {
		$linkStart = "[";
		$linkEnd = "]";
		$n = strpos ( $text, $linkStart );
		while ( $n > - 1 ) {
			$o = strpos ( $text, $linkStart, $n + 1 );
			$c = strpos ( $text, $linkEnd, $n + 1 );
			if ($c > - 1 && ($c < $o || ! $o)) {
				$tag = substr ( $text, $n + strlen ( $linkStart ), $c - $n - strlen ( $linkEnd ) );
				$s = strpos ( $tag, '|' );
				$spec = strpos ( $tag, ':' );
				if ($spec > - 1) {
					$tag = "";
				
				} elseif ($s > - 1) {
					$tag = substr ( $tag, $s + 1, strlen ( $tag ) - $s );
				
				}
				$text = substr_replace ( $text, $tag, $n, $c + 1 - $n );
				$n = strpos ( $text, $linkStart );
			} else {
				$n = $o;
			}
		}
		
		return $text;
	}
	
	static function getSearchResult($wiki, $topic) {
		
		$topic = urldecode ( $topic );
		$topic = str_replace ( " ", "_", $topic );
		
		//Wiki specific Variables
		$index_path = "";
		$access_path = "";
		
		switch ($wiki) {
			case "hpedia.hp.com" :
				$index_path = "/w";
				$access_path = "/wiki";
				break;
			case "hpedia-dev.fc.hp.com" :
				$index_path = "/w";
				$access_path = "/wiki";
				break;
			default :
				$index_path = "/w";
				$access_path = "/wiki";
				break;
		}
		
		$url = 'http://' . $wiki . $index_path . '/index.php?title=Special%3ASearch&search=' . $topic . '&action=raw';
		
		//----------------------	---------------------------------------------------------------------
		// Extract the main Topic from the Wikki
		// This code works only for mediawiki type of wikis later following changes are to be done:
		// replace $wiki by a wiki class, representing a wiki-tpye
		// ad an extract_topc(wikiclass) function to get the wiki page
		// Typical WikiMedia URL http://de.wikipedia.org/w/index.php?title=Automobil&action=edit
		//-------------------------------------------------------------------------------------------
		

		$ch = curl_init ();
		$timeout = 5; // set to zero for no timeout
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		$contents = curl_exec ( $ch );
		curl_close ( $ch );
		
		// Decode from UTF-8
		$contents = utf8_decode ( $contents );
		//	$contents = removeComments ( $contents );
		// 	$contents = removeClassInfo ( $contents );
		return $contents;
	
	}

}
