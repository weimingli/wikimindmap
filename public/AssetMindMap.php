<!--
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
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Asset Mind Map</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords" content="Wiki, Wikipedia, Mindmap, Mind-Map, browse, knowledge network">
<meta name="description" content="WikiMindMap is a tool to browse easily and efficiently in Wiki content, inspired by the mindmap technique">
<script type="text/javascript" src="flashobject.js"></script>

<style >
<!--type="text/css"
.Stil1 {font-size: 12px}
.Stil2 {font-size: 14px}
.Stil8 {font-size: 10px}
.Stil10 {font-size: 44px}
.Stil11 {font-weight: bold; color: #FFCC00;}
.Stil6 {font-size: 48px}
body {
	background-color: #FFFFFF;
}
-->
</style>

	<script type="text/javascript">
		// <![CDATA[
		
		var wiki	= 'hpedia-dev.fc.hp.com';
		
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
			if (h>300){h = h-200;}

			document.getElementById("flashcontent").style.height  = h;
			
			var fo = new FlashObject(
				"http://wikimap.corp.hp.com/visorFreemind.swf", "visorFreeMind", "100%", h, 6, "#9999ff");
			fo.addParam("quality", "high");
			fo.addParam("bgcolor", "#ffffff");
			fo.addVariable("openUrl", "_blank");
			fo.addVariable("initLoadFile", "http://wikimap.corp.hp.com/getpages.php?wiki=<?php echo $_GET['wiki']; ?>&topic=<?php echo $_GET['topic']; ?>" );
			fo.addVariable("startCollapsedToLevel","1");
			fo.addVariable("mainNodeShape","bubble");
			fo.write("flashcontent");
			
		};

		// ]]>		
	</script>
</head>

<body style="font-family:Arial, Helvetica, sans-serif ">

<table width="100%"  border="0" cellspacing="0">
  <tr>
    <td width="5%">&nbsp;</td>
    <td width="90%" height="200" valign="TOP">
	        <div id="flashcontent"></div>
	</td>
    <td width="5%">&nbsp;</td>
  </tr>
</table>

<table width="100%"  border="0" cellspacing="0">
  <tr bgcolor="#C9D7F1">
    
    <td colspan="2">
      <span class="Stil2">
        <a href="mailto:hpwikimapadmin@hp.com">Contact us</a>
      </span>
    </td>
	
	<td align="right">
		<a href="http://wikimap.corp.hp.com/">
			<img src="http://wikimap.corp.hp.com/img/Banner.jpg" width="10%" align="middle" border="0">
		</a>
	</td>
	
  </tr>
</table>

<div align="center" class="Stil1 Stil8">All content of the mindmap is derived from the wiki selected above and is licensed under the terms of <a href="http://www.gnu.org/copyleft/fdl.html" target="_blank">GNU Free Documentaion Licence</a>.<br> Sponsored by the <a href="http://opensource.professions.hp.com">Open Source and Linux Profession<img src="http://wikimap.corp.hp.com/img/oslptux.small.jpg" align="middle" border="0"></a><br>
</div>
<p>&nbsp;</p>

	<script type="text/javascript">
		// <![CDATA[
		setTimeout("runFlash()", 1);
		
	</script>
</body>
</html>

