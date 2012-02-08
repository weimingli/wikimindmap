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
session_start ();

//Before loading the page, we need to tell users whether we'll render search results
//if the parameters are complated
if (isset ( $_GET ['topic'] ) && isset ( $_GET ['wiki'] )) {
	
	//	if this is a search results page
 
	if ($_GET ['topic'] != 'Next_Page' && $_GET ['topic'] != 'Previous_Page') {
		
		
		require_once 'lib/MyEngine.php';
		
		$engine_code = new MyEngine ( $_GET ["wiki"], $_GET ["topic"] );
		 
 		if ($engine_code->getEngine () === 3) {
			
			 
			$_SESSION ['search'] = 1;
			$_SESSION ['nextPageNo'] = 0;
			$_SESSION ['offset'] = 0;
            $_SESSION ['topic'] = $_GET ['topic'];
            
		} else {	
			
 			$_SESSION ['topic'] = $_GET ['topic'];
			$_SESSION ['nextPageNo'] = 0;
			$_SESSION ['search'] = 0;
			$_SESSION ['offset'] = 0;
		}
	
	} else {
		
		$_SESSION ['search'] = 1;
		$_SESSION ['topic'] = $_GET ['topic'];
		
		if ($_GET ['topic'] === 'Next_Page') {
			
		
			$_SESSION ['nextPageNo'] ++;
			$_SESSION ['offset'] = $_SESSION ['nextPageNo'] * 20;
		
		}
		
		if ($_GET ['topic'] === 'Previous_Page') {
			
			$_SESSION ['nextPageNo'] --;
			$_SESSION ['offset'] = $_SESSION ['nextPageNo'] * 20;
		
		}
	
	}
}else {

	//	if this is a initializing page
	 	$_SESSION ['search'] = 0;
		$_SESSION ['nextPageNo'] = 0;
		$_SESSION ['offset'] = 0;
	    $_SESSION ['topic']='';
	}
 

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>HPWikiMap</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords"
	content="Wiki, Wikipedia, Mindmap, Mind-Map, browse, knowledge network">
<meta name="description"
	content="WikiMindMap is a tool to browse easily and efficiently in Wiki content, inspired by the mindmap technique">
<script type="text/javascript" src="flashobject.js"></script>
<script type="text/javascript">
/**
 *	© Copyright 2011 Hewlett-Packard Development Company L.P. 
 */
function ifSearch(){

	
	var searchIndicator=<?=$_SESSION ['search']?>;
	 
	if(searchIndicator===1) {
		document.getElementById('searchIndicator').innerHTML="[Note:  HPWikiMap could not identify an article to match supplied Search string.  This Map indicates a list of articles that are a close match to the supplied search string.] <br>Page: <?php echo $_SESSION ['nextPageNo']+1;?>";
		return true;
	}
	 
	return false;
}
</script>
<style>
<!--
type       ="text /css"
.Stil1 {
	font-size: 12px
}

.Stil2 {
	font-size: 14px
}

.Stil8 {
	font-size: 10px
}

.Stil10 {
	font-size: 44px
}

.Stil11 {
	font-weight: bold;
	color: #FFCC00;
}

.Stil6 {
	font-size: 48px
}

body {
	background-color: #FFFFFF;
}
-->
</style>
</head>

<body style="font-family: Arial, Helvetica, sans-serif"
	onLoad="ifSearch();">
<?php
/**
 *	© Copyright 2011 Hewlett-Packard Development Company L.P. 
 */
if (! isset ( $_GET ['topic'] )) {
	$_GET ["topic"] = "";
}
?>
<?php
/**
 *	© Copyright 2011 Hewlett-Packard Development Company L.P. 
 */
if (! isset ( $_GET ['wiki'] )) {
	$_GET ["wiki"] = "";
}
?>

<p>


<div align="center"><img src="img/Banner.jpg" width="20%"></div>

<form name="search" action="index.php" method="get">
<p align="center">Select a Wiki: <select name="wiki">
	<option
		<?
		echo ($_GET ["wiki"] == "hpedia.hp.com" ? "selected" : "");
		?>>hpedia.hp.com</option>
	<option
		<?
		echo ($_GET ["wiki"] == "hpedia-dev.lnx.usa.hp.com" ? "selected" : "");
		?>>hpedia-dev.lnx.usa.hp.com</option>
</select> Enter your Topic: <input name="topic" type="text"
	value="<?
	if($_GET ['topic'] != 'Next_Page' && $_GET ['topic'] != 'Previous_Page'){
		echo $_GET ["topic"];
	}else{
		
		echo $_SESSION['topic'];
	}
	
	?>"> <input type="submit" value="Search">

</form>

<table width="100%" border="0" cellspacing="0">
	<tr bgcolor="#C9D7F1">
		<td colspan="3" style="border-top-color: #000099">
		<div align="center" class="Stil2">
		
	<?php
	/**
     *	© Copyright 2011 Hewlett-Packard Development Company L.P. 
     */
	$wiki = $_GET ['wiki'];
	$topic = $_GET ['topic'];
	
	if ($wiki != "" && $topic != "")
		echo '<div align="left"><a href="getfreemind.php?wiki=' . $wiki . '&topic=' . $topic . '">Download the wikimap as Freemind file</a></div>';
	else
		echo '<div align="Center">Result</div>'?>
		
   </div>
		</td>
	</tr>
	<tr>
		<td width="5%">&nbsp;</td>
		<td width="90%" height="400">
		<div id="searchIndicator"
			style="text-align: center; color: red;  margin-bottom: 15px; margin-top: 10px;"></div>
		<div id="flashcontent"></div>

		<script type="text/javascript">
                // <![CDATA[
				setTimeout("runFlash()", 1);
				//runFlash();
				function getWindowHeight() {
					var windowHeight = 0;
					if (typeof(window.innerHeight) == 'number') { windowHeight = window.innerHeight; }
					else {
						if (document.documentElement && document.documentElement.clientHeight)
						{ windowHeight = document.documentElement.clientHeight; }
						else {
							if (document.body && document.body.clientHeight) { windowHeight = document.body.clientHeight; }
						}
					}
					return windowHeight;
				}
				
				function runFlash() {
					var h = getWindowHeight();
					h = h-200;

					document.getElementById("flashcontent").style.height  = h;
					
					var fo = new FlashObject("visorFreemind.swf", "visorFreeMind", "100%", h, 6, "#9999ff");
					fo.addParam("quality", "high");
					fo.addParam("bgcolor", "#ffffff");
					fo.addVariable("openUrl", "_blank");
					fo.addVariable("initLoadFile", "getpages.php?wiki=<?php
					echo $_GET ['wiki'];
					?>&topic=<?php
					echo urlencode ( $_GET ['topic'] );
					?>");
					fo.addVariable("startCollapsedToLevel","1");
					fo.addVariable("mainNodeShape","bubble");
					fo.write("flashcontent");
				};
                // ]]>
				
        </script></td>
		<td width="5%">&nbsp;</td>
	</tr>
	<tr bgcolor="#C9D7F1">
		<td colspan="3"><span class="Stil2"><a
			href="mailto:hpwikimapadmin@hp.com">Contact us</a></span></td>
	</tr>



</table>
<div align="center" class="Stil1 Stil8">All content of the mindmap is
derived from the wiki selected above and is licensed under the terms of
<a href="http://www.gnu.org/copyleft/fdl.html" target="_blank">GNU Free
Documentaion Licence</a>.<br>
Sponsored by the <a href="http://opensource.professions.hp.com">Open
Source and Linux Profession<img src="img/oslptux.small.jpg"
	align="middle" border="0"></a><br>
</div>
<p>&nbsp;</p>
</body>
</html>

