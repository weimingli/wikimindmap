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
class ParsePage {
	 
	private $contents;
	private $wiki;
	private $topic;
	private $url;
	
	public function ParsePage($wiki, $topic) {
		
		$this->wiki = $wiki;
		$this->topic = $topic;
		$this->contents = $this->getContent ();
		return $this->GetResult ();
	}
	
	 
	public function GetResult() {
		
		 
		if (strlen ( $this->contents ) === 0) {
			require_once 'lib/HtmlEngine.php';
			$result = new HtmlEngine ( $this->contens );
			return $result;
		}
		
		 
		if (! $this->GetTemplateName ()) {
				
			require_once 'lib/DefaultEngine.php';
			require_once 'lib/Common.php';
			$this->contents=$this->getSearchResult();
			$result = new DefaultEngine ($this->contents );
			return $result;
		}
		
	 
		$engineName = $this->GetTemplateName ();
		require_once 'lib/' . $engineName . '.php';
		$result = new $engineName ( $this->contents );
		return $result;
	}
	
	public function GetTemplateName() {
		
		return false;
	}
	
	function getSearchResult() {
		
		$topic = urldecode ( $topic );
		$topic = str_replace ( " ", "_", $topic );
		
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
		
		$url = 'http://' . $this->wiki . $index_path . '/index.php?title=Special%3ASearch&search=' . $this->topic . '&action=raw';
	 
		$ch = curl_init ();
		$timeout = 5; // set to zero for no timeout
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		$contents = curl_exec ( $ch );
		curl_close ( $ch );
 		return $contents;
	
	}
	
 
	protected function getContent() {
		
		$topic = urldecode ( $this->topic );
		$topic = str_replace ( " ", "_", $this->topic );
		
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
		
		//echo $topic;
		$this->url = 'http://' . $this->wiki . $index_path . '/index.php?title=' . $this->topic . '&action=raw';
		
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
		$this->contents = curl_exec ( $ch );
		curl_close ( $ch );
		
		// Decode from UTF-8
		$this->contents = utf8_decode ( $this->contents );
		
		$this->contents = removeComments ( $this->contents );
		$this->contents = removeClassInfo ( $this->contents );
		return $this->contents;
	
	}
	
	/**
	 * 
 	 * Enter description here ...
	 */
	function genMap() {
		
		while ( strpos ( $this->contents, $this->linkStart ) > - 1 || strpos ( $this->contents, $this->wwwLinkStart ) > - 1 || strpos ( $this->contents, $this->chapStart ) > - 1 || strpos ( $this->contents, $this->subChapStart ) > - 1 ) {
			
			//		$counter ++;
			// is the next object to parse a section or a wikilink?
			

			$iChap = strpos ( $this->contents, $this->chapStart );
			$iSubChap = strpos ( $this->contents, $this->subChapStart );
			$iLink = strpos ( $this->contents, $this->linkStart );
			$iWwwLink = strpos ( $this->contents, $this->wwwLinkStart );
			
			
			if ($iChap > - 1 && ($iChap < $iLink || ! $iLink) && ($iChap < $iWwwLink || ! $iWwwLink) && ($iChap < $iSubChap || ! $iSubChap)) {
				
				$this->contents = strstr ( $this->contents, $this->chapStart );
				$this->contents = substr ( $this->contents, strlen ( $this->chapStart ) );
				$Chap = substr ( $this->contents, 0, strpos ( $this->contents, $this->chapEnd ) );
				
				if ($Chap != "") {
					if ($this->openSubChap == TRUE) {
						echo '</node>\n';
						$openSubChap = FALSE;
					}
					if ($this->openChap == TRUE) {
						echo '</node>\n';
						$this->openChap = FALSE;
					}
					
					// Filter all the Tag information
					$Chap = str_replace ( $this->linkStart, "", $Chap );
					$Chap = str_replace ( $this->chapEnd, "", $Chap );
					$Chap = str_replace ( "=", "", $Chap );
					
					// Create Topic
					$wChap = str_replace ( " ", "_", $Chap );
					$wChap = trim ( $wChap, "_" );
					$ttorg = substr ( $this->contents, strpos ( $this->contents, $this->chapEnd ) + 2, 500 );
					$tooltip = createToolTipText ( $ttorg, 150 );
					
					$wikilink = 'http://' . $wiki . $access_path . '/' . $topic . '#' . $wChap;
					echo '<node TEXT="' . cleanText ( $Chap ) . '" WIKILINK = "' . cleanWikiLink ( $wikilink ) . '" TOOLTIPTEXT = "' . $tooltip . '" STYLE="bubble">\n';
					//echo  '<node TEXT="'.cleanText($Chap).'" WIKILINK = "'.cleanWikiLink($wikilink).'"  STYLE="bubble">/n';
					//echo 'node TEXT="'.$Chap.'" STYLE="bubble"><br>';
					$openChap = TRUE;
				
				}
				
				$this->contents = strstr ( $this->contents, $this->chapEnd );
				$this->contents = substr ( $this->contents, strlen ( $this->chapEnd ) );
			
			}
			
			//-----------------------------------------
			// Create SubChapter Nodes
			//-----------------------------------------
			if ($iSubChap > - 1 && ($iSubChap < $iLink || ! $iLink) && ($iSubChap < $iWwwLink || ! $iWwwLink) && ($iSubChap <= $iChap || ! $iChap)) {
				//echo "SUbCHap";
				$this->contents = strstr ( $this->contents, $this->subChapStart );
				$this->contents = substr ( $this->contents, strlen ( $this->subChapStart ) );
				$SubChap = substr ( $this->contents, 0, strpos ( $this->contents, $this->subChapEnd ) );
				
				//echo $Chap.'<br>';
				if ($SubChap != "") {
					if ($openSubChap == TRUE) {
						echo '</node>\n';
						$openSubChap = FALSE;
					}
					
					// Filter all the Tag information
					$SubChap = str_replace ( $this->linkStart, "", $SubChap );
					$SubChap = str_replace ( "=", "", $SubChap );
					
					// Create Topic
					$wSubChap = str_replace ( " ", "_", $SubChap );
					$wSubChap = trim ( $wSubChap, "_" );
					$ttorg = substr ( $this->contents, strpos ( $this->contents, $this->subChapEnd ) + 3, 500 );
					

					$wikilink = 'http://' . $wiki . $access_path . '/' . $topic . '#' . $wSubChap;
					echo '<node TEXT="' . cleanText ( $SubChap ) . '" WIKILINK = "' . cleanWikiLink ( $wikilink ) . '" TOOLTIPTEXT = "' . $tooltip . '" STYLE="bubble">\n';
					$openSubChap = TRUE;
				}
				
				$this->contents = strstr ( $this->contents, $this->subChapEnd );
				$this->contents = substr ( $this->contents, strlen ( $this->subChapEnd ) );
			}
			
			//-----------------------------------------
			// Create WWW Link Nodes
			//-----------------------------------------
			if ($iWwwLink > - 1 && ($iWwwLink < $iLink || ! $iLink) && ($iWwwLink < $iChap || ! $iChap) && ($iWwwLink < $iSubChap || ! $iSubChap)) {
				
				$this->contents = strstr ( $this->contents, $this->wwwLinkStart );
				$this->contents = substr ( $this->contents, strlen ( $this->wwwLinkStart ) );
				$wwwLink = substr ( $this->contents, 0, strpos ( $this->contents, $this->wwwLinkEnd ) );
				if ($wwwLink != "") {
					$wwwLinkURL = 'http:' . substr ( $wwwLink, 0, strpos ( $wwwLink, " " ) );
					$wwwLinkName = substr ( $wwwLink, strpos ( $wwwLink, " " ), strlen ( $wwwLink ) );
					echo '<node TEXT="' . cleanText ( $wwwLinkName ) . '" WEBLINK="' . $wwwLinkURL . '" STYLE="fork">\n';
					echo '</node>/n';
				}
				$this->contents = strstr ( $this->contents, $this->wwwLinkEnd );
				$this->contents = substr ( $this->contents, strlen ( $this->wwwLinkEnd ) );
			}
			
			//-----------------------------------------
			// Create WikiPage Nodes  
			//-----------------------------------------		
			if ($iLink > - 1 && ($iLink < $iWwwLink || ! $iWwwLink) && ($iLink < $iChap || ! $iChap) && ($iLink < $iSubChap || ! $iSubChap)) {
				$this->contents = strstr ( $this->contents, $this->linkStart );
				$tag = substr ( $this->contents, strlen ( $this->linkStart ), strpos ( $this->contents, $this->linkEnd ) - strlen ( $this->linkStart ) );
				
				//echo $tag;
				

				// Keine Bilder etc...
				if (strpos ( $tag, ':' ) == FALSE) {
					//No dublicates
					

					if (in_array ( $tag, $link [0] ) == FALSE) {
						if (strpos ( $tag, '|' ) != FALSE) {
							$wTag = substr ( $tag, 0, strpos ( $tag, '|' ) );
							$link [1] [$i] = str_replace ( " ", "_", $wTag );
							$link [0] [$i] = str_replace ( "|", " / ", $tag );
						} else {
							$link [1] [$i] = str_replace ( " ", "_", $tag );
							$link [0] [$i] = $tag;
						}
						$wikilink = 'http://' . $this->wiki . $access_path . '/' . $link [1] [$i];
						$mmlink = 'viewmap.php?wiki=' . $this->wiki . '&topic=' . $link [1] [$i];
						
						echo '<node TEXT="' . cleanText ( $link [0] [$i] ) . '" WIKILINK="' . cleanWikiLink ( $this->wikilink ) . '" MMLINK="' . $this->mmlink . '" STYLE="fork">\n';
						//echo '<node TEXT="T"  STYLE="fork">/n';
						echo '</node>\n';
						$i ++;
					}
				}
				$this->contents = strstr ( $this->contents, $this->linkEnd );
				$this->contents = substr ( $this->contents, strlen ( $this->linkEnd ) );
			}
		}
	
	}

}
