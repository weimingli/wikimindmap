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
class ParseEngine {
	
	public $contents;
	public $catStart = '{';
	public $catEnd = '}';
	public $chapStart = '==';
	public $chapEnd = '==';
	public $subChapStart = '===';
	public $subChapEnd = '===';
	public $linkStart = '[[';
	public $linkEnd = ']]';
	public $wwwLinkStart = '[http:';
	public $wwwLinkEnd = ']';
	public $sWWWLinkStart='[https:';
	public $sWWWLinkEnd = ']';
	public $chapterMarks;
	public $articleDepth;
	public $allMarks;
	public $openMarks;
	
    /**
     * 
     * this is the constructor  
     *  
     * @param String $wiki
     * @param String $topic
     * @param String $contents
     */
	public function ParseEngine($wiki, $topic, $contents) {
		$this->wiki = $wiki;
		$this->topic = $topic;
		$this->contents = $contents;
	
	}
	
	/**
	 * 
	 * this function will show the mind map file
	 */
	public function show() {
		
		$this->showHeader ();
		$this->showBody ();
		$this->showFooter ();
	}
	
	/**
	 * 
	 * this function will show the mind map header
	 */
	public function showHeader() {
		
		echo '<map version="0.8.0">\n';
		echo '<edge STYLE="bezier"/>\n';
	}
	
	/**
	 * 
	 * this function will show the body
	 *  
	 */
	public function showBody() {
		
		echo $this->parse ();
	}
	
	/**
	 * this function will translate the content into the language that $this->parse()could understand
	 *  
	 */
	public function translate() {
		
		return $this->contents;
	}
	
	/**
	 * 
	 * this function will parse the content
	 */
	public function parse() {
		
		 
		$this->contents = $this->translate ();
		
		 
		 $mychapterMarks=array();
		// first we have to know how deep the article is 
		preg_match_all ( '/={2,10}/', $this->contents, $matches );
		
		$marks = array_unique ( $matches [0] );
		
		foreach ( $marks as $key => $value ) {
			
			$mychapterMarks [] = $value;
		}
		
		$this->chapterMarks = $mychapterMarks;
		
		$this->articleDepth = count ( $this->chapterMarks );
		
		rsort ( $this->chapterMarks );
		
		//		get array of all marks 
		$this->allMarks = $this->chapterMarks;
		$this->allMarks [] = $this->wwwLinkStart;
		$this->allMarks [] = $this->linkStart;
		$this->allMarks [] = $this->sWWWLinkStart;
		$i = 0;
		$link [0] [0] = "";
		
		//	create the bubble
		$wikilink = 'http://' . $this->wiki . '/wiki/' . $this->topic;
		
		if (get_class ( $this ) ===	 'HtmlEngine') {
			
			$wikilink='http://'.$this->wiki.'/w/index.php?title=Special:Search&limit=20&offset='.$_SESSION['offset'].'&ns0=1&redirs=0&search='.$_SESSION['topic'];
			$this->topic=$_SESSION['topic'];
 		}
		
		$ttorg = substr ( $this->contents, 0, 500 );
		
		$tooltip = $this->createToolTipText ( $ttorg, 300 );
		  
		
		$this->contents .= '<node STYLE="bubble" TEXT="' . $this->topic . '" WIKILINK = "' . $wikilink . '" >\n';
		$this->contents .= '<edge STYLE="sharp_bezier" WIDTH="2"/>\n';
		
		$counter = 0;
		
		$this->openMarks = array ();
		
		while ( true ) {
			
			$counter ++;
			$ifContrinue = false;
			$myMarks = array ();
			
			//			first, we'll get all markers' position in article 
			foreach ( $this->allMarks as $key => $value ) {
				
				if (strpos ( $this->contents, $value ) > - 1) {
					
					$myMarks [$key] ['marks'] = $value;
					$myMarks [$key] ['pos'] = strpos ( $this->contents, $value );
					$ifContrinue = true;
				}
			
			}
			
			//		if we cannot find the marks any more, we'll exit 
			if (! $ifContrinue || count ( $myMarks ) === 0) {
				break;
			}
			
			//			we order according to the position 
			usort ( $myMarks, array ($this, "my_sort" ) );
			
			$chapterArray=array();
			//if the first is chapter marks, we'll beging anaylize the string and get the correct chapter mark:$chapterMark	
			if (strpos ( $myMarks [0] ['marks'], '=' ) > - 1) {
				
				foreach ( $myMarks as $key => $value ) {
					
					if (strpos ( $value['marks'], '=' ) > - 1 && $value ['pos'] === $myMarks [0] ['pos'] && $value ['marks'] != $myMarks [0] ['marks']) {
						
						$chapterArray [] = $value ['marks'];
					}
				
				}
				$chapterMark = $myMarks [0] ['marks'];
				if ($chapterArray && count ( $chapterArray ) > 0) {
					foreach ( $chapterArray as $key => $value ) {
						
						if (strlen ( $value ) > strlen ( $chapterMark )) {
							
							$chapterMark = $value;
						}
					}
				
				}
				
				//	  	we'll analyze the chapter marks we got 
				$this->contents = strstr ( $this->contents, $chapterMark );
				$this->contents = substr ( $this->contents, strlen ( $chapterMark ) );
				$Chap = substr ( $this->contents, 0, strpos ( $this->contents, $chapterMark ) );
				
				//					adding close tag 
				if ($Chap != "") {
					
					//	if there is such a element in this array, it means there should be a close tag 
					if (in_array ( $chapterMark, $this->openMarks )) {
						
						$closeMarksNum = 0;
						
						foreach ( $this->openMarks as $key => $value ) {
							
							if (strlen ( $value ) >= strlen ( $chapterMark )) {
								
								unset ( $this->openMarks [$key] );
								
								$closeMarksNum ++;
							}
						
						}
						
						sort ( $this->openMarks );
						if ($closeMarksNum > 0) {
							
							for($k = 0; $k < $closeMarksNum; $k ++) {
								
								$this->contents .= "</node>/n";
							}
						
						}
					
					}
					
					// Filter all the Tag information
					$Chap = str_replace ( $this->linkStart, "", $Chap );
					$Chap = str_replace ( $chapterMark, "", $Chap );
					$Chap = str_replace ( "=", "", $Chap );
					$ChapText = $Chap;
					
					$Chap = str_replace ( '&', '.26', $Chap );
					$Chap = str_replace ( '(', '.28', $Chap );
					$Chap = str_replace ( ')', '.29', $Chap );
					$Chap = str_replace ( '/', '.2F', $Chap );
					$Chap = str_replace ( "'", "", $Chap );
					
					// Create Topic
					$wChap = str_replace ( " ", "_", $Chap );
					$wChap = trim ( $wChap, "_" );
					$ttorg = substr ( $this->contents, strpos ( $this->contents, $chapterMark ) + strlen ( $chapterMark ), 500 );
					$tooltip = $this->createToolTipText ( $ttorg, 150 );
					
					$wikilink = 'http://' . $this->wiki . '/wiki/' . $this->topic . '#' . $wChap;
					$this->contents .= '<node TEXT="' . $this->cleanText ( $ChapText ) . '" WIKILINK = "' . $this->cleanWikiLink ( $wikilink ) . '"  STYLE="bubble">\n';					
					$this->openMarks [] = $chapterMark;				
				}
				
				$this->contents = strstr ( $this->contents, $chapterMark );
				$this->contents = substr ( $this->contents, strlen ( $chapterMark ) );
			
			}
			
			//if it is a www link
	 
			if ($myMarks [0] ['marks'] == $this->wwwLinkStart) {
				
				$this->contents = strstr ( $this->contents, $this->wwwLinkStart );
				$this->contents = substr ( $this->contents, strlen ( $this->wwwLinkStart ) );
				$wwwLink = substr ( $this->contents, 0, strpos ( $this->contents, $this->wwwLinkEnd ) );
				if ($wwwLink != "") {
					$wwwLinkURL = 'http:' . substr ( $wwwLink, 0, strpos ( $wwwLink, " " ) );
					$wwwLinkName = substr ( $wwwLink, strpos ( $wwwLink, " " ), strlen ( $wwwLink ) );
					
					if(trim($wwwLink)==='http'){
						
						$wwwLinkURL=$wwwLinkName='https:'.trim($wwwLink);
						if(strlen($wwwLinkURL)>50){
							$wwwLinkURL=$wwwLinkName=substr('https:'.trim($wwwLink), 0,47).'...';
							
						}
					}
					
					$this->contents .= '<node TEXT="' . $this->cleanText ( $wwwLinkName ) . '" WEBLINK="' . $wwwLinkURL . '" STYLE="fork">\n';
					$this->contents .= '</node>/n';
				}
				$this->contents = strstr ( $this->contents, $this->wwwLinkEnd );
				$this->contents = substr ( $this->contents, strlen ( $this->wwwLinkEnd ) );
			
			}
			
		//		     if it is a www link
		 		if ($myMarks [0] ['marks'] == $this->sWWWLinkStart) {
				
				$this->contents = strstr ( $this->contents, $this->sWWWLinkStart );
				$this->contents = substr ( $this->contents, strlen ( $this->sWWWLinkStart ) );
				$swwwLink = substr ( $this->contents, 0, strpos ( $this->contents, $this->sWWWLinkEnd ) );
				 
				if ($swwwLink != "") {
					$swwwLinkURL = 'https:' . substr ( $swwwLink, 0, strpos ( $swwwLink, " " ) );
                    $swwwLinkName = substr ( $swwwLink, strpos ( $swwwLink, " " ), strlen ( $swwwLink ) );
					
					if(trim($swwwLinkURL)==='https:'){
						
						$swwwLinkURL=$swwwLinkName='https:'.trim($swwwLink);
						if(strlen($swwwLinkURL)>50){
							$swwwLinkURL=$swwwLinkName=substr('https:'.trim($swwwLink), 0,47).'...';
							
						}
					}
					
					$this->contents .= '<node TEXT="' . $this->cleanText ( $swwwLinkName ) . '" WEBLINK="' . $swwwLinkURL . '" STYLE="fork">\n';
					$this->contents .= '</node>/n';
				}
				$this->contents = strstr ( $this->contents, $this->sWWWLinkEnd );
				$this->contents = substr ( $this->contents, strlen ( $this->sWWWLinkEnd ) );
			
			}
			
			
			//			if it is a wiki article  
	 
			if ($myMarks [0] ['marks'] == $this->linkStart) {
				
				$this->contents = strstr ( $this->contents, $this->linkStart );
				$tag = substr ( $this->contents, strlen ( $this->linkStart ), strpos ( $this->contents, $this->linkEnd ) - strlen ( $this->linkStart ) );
				
				// Keine Bilder etc...
//				if (strpos ( $tag, ':' ) == FALSE) {
					//No duplicates
					

					if (in_array ( $tag, $link [0] ) == FALSE) {
						if (strpos ( $tag, '|' ) != FALSE) {
							$wTag = substr ( $tag, 0, strpos ( $tag, '|' ) );
							$link [1] [$i] = str_replace ( " ", "_", $wTag );
							$link [0] [$i] = str_replace ( "|", " / ", $tag );
						} else {
							$link [1] [$i] = str_replace ( " ", "_", $tag );
							$link [0] [$i] = $tag;
						}
						$wikilink = 'http://' . $this->wiki . '/wiki/' . $link [1] [$i];
						$mmlink = 'viewmap.php?wiki=' . $this->wiki . '&topic=' . $link [1] [$i];
						
						$this->contents .= '<node TEXT="' . $this->cleanText ( $link [0] [$i] ) . '" WIKILINK="' . $this->cleanWikiLink ( $wikilink ) . '" MMLINK="' . $mmlink . '" STYLE="fork">\n';
						$this->contents .= '</node>\n';
						$i ++;
					}
//				}
				$this->contents = strstr ( $this->contents, $this->linkEnd );
				$this->contents = substr ( $this->contents, strlen ( $this->linkEnd ) );
			}
		
		}
 
		$this->contents=str_replace('</noinclude>','',$this->contents);
 		return $this->contents;
	}
	
	public function my_sort($a, $b) {
		if ($a ['pos'] == $b ['pos'])
			return 0;
		return ($a ['pos'] > $b ['pos']) ? 1 : - 1;
	}
	
	function showFooter() {
		
		echo '</node>\n';
		echo '</map>\n';
	}
	
	//-------------------------------------------------------------------------------------------
	// Functions to clean text from special caracters
	//-------------------------------------------------------------------------------------------
	

	public function cleanText($text) {
		$trans = array ("=" => "", "[" => "", "]" => "", "{" => "", "}" => "", "_" => " ", "'" => "", "|" => "/", "?" => "", "*" => "-", "\"" => "'" );
		$clean = strtr ( $text, $trans );
		// Experimental remove a lot of reutrns (\n)
		$transW = array ("\n\n\n" => "" );
		$clean = strtr ( $clean, $transW );
		return $clean;
	}
	
	public function cleanWikiLink($text) {
		$trans = array ("=" => "", "[" => "", "]" => "", "{" => "", "}" => "" );
		$clean = strtr ( $text, $trans );
		return $clean;
	}
	
	//-------------------------------------------------------------------------------------------
	// Functions to create ToolTip Text
	// Strategy: Text until the next chapter starts, but no more than n (100?) characters.
	//-------------------------------------------------------------------------------------------
	

	public function createToolTipText($text, $len) {
		global $chapStart;
		
		$tttext = removeTags ( $text );
		
		$i = $len;
		if (strpos ( $text, $chapStart ) > - 1) {
			$i = min ( strpos ( $text, $chapStart ), $len );
		}
		$tttext = substr ( $tttext, 0, $i );
		$tttext = cleanText ( $tttext );
		$tttext = trim ( $tttext );
		
		return $tttext . ' [...]';
	}
	
	// This alghoritm maybe should by used for all the parsing engines;
	public function removeClassInfo($text) {
		global $catStart, $catEnd;
		$n = strpos ( $text, $catStart );
		while ( $n > - 1 ) {
			$o = strpos ( $text, $catStart, $n + 1 );
			$c = strpos ( $text, $catEnd, $n + 1 );
			if ($c > - 1 && ($c < $o || ! $o)) {
				$text = substr_replace ( $text, "", $n, $c + 1 - $n );
				$n = strpos ( $text, $catStart );
			} else {
				$n = $o;
			}
		}
		return $text;
	}
	
	public function removeComments($text) {
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
	
	public function removeTags($text) {
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
	
	public function checkMarksInContens() {
		
		foreach ( $this->chapterMarks as $key => $value ) {
			if (! strpos ( $this->contents, $value )) {
				
				return false;
			}
			
			return true;
		}
	}
	
	public function checkSub($depth) {
		
		//		got the position of this level and its sublevel's nodes 
 
		$chapterMark = '=';
		$subChapterMark = '';
		$chapterMarkStart = 0;
		$subChapterMarkStart = 0;
		
		for($i = 0; $i < $depth; $i ++) {
			
			$chapterMark .= '=';
		
		}
		
		$subChapterMark = $chapterMark . '=';
		$chapterMarkStart = strpos ( $this->contents, $chapterMark );
		$subChapterMarkStart = strpos ( $this->contents, $subChapterMark );
		
 		if ($chapterMarkStart > - 1 && ($chapterMarkStart < $subChapterMarkStart || ! $subChapterMarkStart)) {
			
			return true;
		}
		
		return false;
	}
	
	public function checkUpper($depth) {
		
 		 
		$chapterMark = '=';
		$parentChapterMark = '';
		$chapterMarkStart = 0;
		$parentChapterMarkStart = 0;
		
		for($i = 0; $i < $depth; $i ++) {
			
			$chapterMark .= '=';
		
		}
		
		$parentChapterMarkLength = strlen ( $chapterMark ) - 1;
		$parentChapterMark = substr ( $chapterMark, 0, $parentChapterMarkLength );
		
		$chapterMarkStart = strpos ( $this->contents, $chapterMark );
		$parentChapterMarkStart = strpos ( $this->contents, $parentChapterMark );
		  
		if ($chapterMarkStart > - 1 && ($chapterMarkStart <= $parentChapterMark || ! $parentChapterMark)) {
			
			return true;
		}
		return false;
	}
	
	public function formerThanOtherMarks($mark) {
		  
		if (strpos ( $mark, '=' ) > - 1) {
			
			foreach ( $this->chapterMarks as $key => $value ) {
				
 				if (! strpos ( $this->contents, $value )) {
					
					continue;
				}
				
 				if (strpos ( $this->contents, $value ) > - 1 && strpos ( $this->contents, $mark ) > strpos ( $this->contents, $value )) {
					
					return false;
				}
			}
			
			return true;
		}
		
		if (! strpos ( $mark, '=' )) {
			
			foreach ( $this->allMarks as $key => $value ) {
				
				if (! strpos ( $this->contents, $value )) {
					
					continue;
				
				}
				
				if (strpos ( $this->contents, $value ) > - 1 && $mark != $value && strpos ( $this->contents, $mark ) > strpos ( $this->contents, $value )) {
					
					return false;
				
				}
			
			}
			
			return true;
		}
		
		return true;
	
	}
	
	public function checkPos($depth) {
		
		if ($depth == $this->articleDepth) {
			
			return $this->checkUpper ( $depth );
		}
		
		if ($depth == 1) {
			
			return $this->checkSub ( $depth );
		
		}
		
		return ($this->checkSub ( $depth ) && $this->checkUpper ( $depth )) ? true : false;
	
	}

}
