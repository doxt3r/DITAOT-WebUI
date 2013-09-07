<!-- Author: Alex Griessel -->
<!-- (c) Copyright Webnetix. 2007 All Rights Reserved. -->
<?php
/*
	Configuration Area
*/

// Update this to reflect your DITA content Directory
$root = dirname("/Users/chim3r4/shell/cmr/features");

//List of conditional processing variables
$audiences = array('user'=>'User','purchaser'=>'Purchaser','administrator'=>'Administrator','programmer'=>'Programmer','executive'=>'Executive','services'=>'Services', 'other'=>'Other');
$platforms = array('windows'=>'Windows','linux'=>'Linux','mac_os_x'=>'Mac OS X');
$products = array('product_a'=>'Product A','product_b'=>'Product B');
/*
	You should not need to edit anything below this line
*/
$root = realpath($root);
$siteRoot = dirname(str_replace("web/","",$_SERVER['SCRIPT_FILENAME']));
$tempDir = $siteRoot . "/temp/";
$antDir = $siteRoot . "/ant/";
$siteBaseUrl = sprintf("http://%s:%s%s/", $_SERVER['SERVER_NAME'],$_SERVER['SERVER_PORT'], dirname($_SERVER['REQUEST_URI']));
$transforms = getTransforms($antDir);
/*
	Available Transformations
*/
function getTransforms($path) {
	$dh = dir($path);
	while ($entry = $dh->read()) {
		if (eregi("^template_([^\.]*)\.xml",$entry, $regs)) {
			$options[$entry] = $regs[1];
		}
	}
	return $options;
}

function buildDitaVal($conditions) {
	$template = file_get_contents("templates/ditaval.xml");
	foreach ($conditions as $conditionGroup=>$conditionItems) {
		foreach ($conditionItems as $conditionItem) 
		$properties[] = sprintf('<prop att="%s" val="%s" action="exclude" />', $conditionGroup, $conditionItem);
	}
	$template = str_replace("@properties@", implode("\n",$properties), $template);
	return $template;
}

/* Collects all available DITAMAP files */
function transverse($path,$options = array()) {
	$dh = dir($path);
	while ($entry = $dh->read()) {
		if (!eregi("^\.",$entry) && is_dir($path . '/' . $entry)) {
			$options = transverse($path . '/' . $entry, $options);
		}
		if (eregi("ditamap$", $entry)) {
			$options[] = $dh->path . '/' . $entry;
		}
	}
	return $options;
}


if (!empty($_POST)) {
	$template = ($_POST['template'])?$_POST['template']:"template_pdf.xml";
	eregi("^template_([^\.]*)\.xml",$template, $regs);
	$templateType = $regs[1];
	$target = ($_POST['target'])?$_POST['target']:"";
	$mapName = str_replace(".ditamap","", basename($target));
	$buildFile = sprintf($antDir . "/%s_%s.xml", $mapName, $templateType);
	$antTarget = file_get_contents($antDir . "/" . $template);
	$antTarget = str_replace("@DELIVERABLE.NAME@", $mapName, $antTarget);
	$antTarget = str_replace("@DITA.INPUT@", $target , $antTarget);
	$antTarget = str_replace("@OUTPUT.DIR@", $tempDir ,$antTarget);
	
	
	
	//generate ditaval file
	if (isset($_POST['condition']) and !empty($_POST['condition'])) {
		$ditaval = buildDitaVal($_POST['condition']);
		file_put_contents("temp/temp.ditaval", $ditaval);
		$antTarget = str_replace("@DITA.INPUT.VALFILE@", "../web/temp/temp.ditaval" ,$antTarget);
	}
	
	file_put_contents($buildFile, $antTarget);
	
	$output = "<div>";
	$output .= `export DITA_HOME=$siteRoot;cd \$DITA_HOME;./web/transform.sh $buildFile $mapName $templateType 2>&1`;
	$output .= "</div>";
	if (is_file(str_replace($root, "../temp/", str_replace(".ditamap",".pdf", $target)))) {
		$dFile = str_replace($root, "../temp/", str_replace(".ditamap",".pdf", $target));
	} else {
		$dFile = "../temp/" . $mapName . ".pdf";
	}
	echo "<ul>";
	echo "<li><a href='javascript:void(0)' onclick='revertHome()'>Build Another</a></li>";
	echo "<li><a href='../temp/'>Download</a></li>";
	echo "</ul>";
	echo $output;
	// header("Location: " . $siteBaseUrl . $dFile);
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>DITA Frontend Prototype</title>
	<script src="js/jquery-1.2.1.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery.ui-1.0/ui.tabs.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery.ui-1.0/jquery.dimensions.js"></script>
	<script src="js/jquery.ui-1.0/ui.mouse.js"></script>
	<script src="js/jquery.ui-1.0/ui.magnifier.js"></script>
	<link rel="stylesheet" href="js/jquery.ui-1.0/themes/default/ui.tabs.css" type="text/css" media="screen" title="Flora (Default)">
	<style type="text/css" media="screen">
		* {margin:0px;padding:0px;}
		body {
			font-family:arial, verdana;
			font-size:9pt;
			text-align:center;
		}

		#wrapper {
			text-align:left;
			width:760px;
			margin:auto;
			margin-top:20px;
		}

		.error {
			border:solid 1px #C0656A;
			background:#CF898D;
			color:#fff;
			padding:2em;
			margin:2em;
		}

		input, textarea, select,fieldset {
			border:solid 1px #6D6B5E;
			background:#F7F8F4;
		}

		legend {
			font-size:13pt;
			color:#88A3C6;
		}

		fieldset {
			padding:20px;
		}

		#heading {
			font-size:17pt;
			text-align:right;
			vertical-align:middle;
			letter-spacing:0.2em;
			color:#c0c0c0;
		}

		#header {
			height:120px;
		}

		#submit {
			text-align:right;
			margin-top:20px;
		}
		

		#footer {
			text-align:center;
			color:#1e1e1e;
			font-size:7pt;
		}
		
		#output {
			white-space:pre;
			font-size:7pt;
			height:300px;
			overflow:auto;
		}
		
		#resultContainer {
		}
	</style>
	<script type="text/javascript" charset="utf-8">
		function handlePost() {
			document.getElementById('buildmap').value = "Building...";
			$.ajax({
			  type: 'POST',
			  data: $('#frmSetup').serialize(),
			  url: 'index.php',
			  success: function(html){
				$('#output').html(html);
				$('#content > ul').tabsClick(4);
				document.getElementById('buildmap').value = "Build Deliverable  >>";
			  }
			});
			return false;
		}
		
		function revertHome() {
			$('#content > ul').tabsClick(1);
			
		}
	  $(document).ready(function(){
	    $("#content > ul").tabs({ fxSlide: true, fxFade: true, fxSpeed: 'fast' });
	  });
	

	</script>
	<style type="text/css">
	<!--
	body
		{
			font-family: verdana, sans-serif;
			font-size: 11px;
			background: #f3f3f3;
		}

	#container
		{
			padding: 30px;
			border: 1px solid #ccc;
			background: #fff;
		}

	#tabnav
		{
			height: 20px;
			margin: 0;
			padding-left: 10px;
			background: url(../images/tab_bottom.gif) repeat-x bottom;
		}

	#tabnav li
		{
			margin: 0; 
			padding: 0;
	  		display: inline;
	  		list-style-type: none;
	  	}

	#tabnav a:link, #tabnav a:visited
		{
			float: left;
			background: #f3f3f3;
			font-size: 10px;
			line-height: 14px;
			font-weight: bold;
			padding: 2px 10px 2px 10px;
			margin-right: 4px;
			border: 1px solid #ccc;
			text-decoration: none;
			color: #666;
		}

	#tabnav a:link.active, #tabnav a:visited.active
		{
			border-bottom: 1px solid #fff;
			background: #fff;
			color: #000;
		}

	#tabnav a:hover
		{
			background: #fff;
		}
		
	div.active {
		display:block;
	}
	
	div.inactive {
		display:none;
	}
	
	p {
	margin-bottom:1em;
	}
	
	h1 {
		font-size:13pt;
		color:#3A71A9;
	}
	
	a {
		color:#3A71A9;
	}
	-->
	</style>
</head>
<body>
	<form action="index.php" method="POST" accept-charset="utf-8" id="frmSetup">
	
	<div id="wrapper">
			<div id="container">
				<div id="header">
					<img src="dita-logo.jpg" style="vertical-align:middle" width="264px" height="120px"/><span id="heading">DITA Open Toolkit WebGUI</span>
				</div>
				<div id="content" class="flora">
					<ul>
	                	<li><a href="#setup" class="active"><span>Basic Setup</span></a></li>
						<li><a href="#conditional"><span>Conditional Processing</span></a></li>
						<li><a href="#advanced"><span>Advanced Options</span></a></li>
						<li><a href="#ouput_tab"><span>Output</span></a></li>
						<li><a href="#about"><span>About</span></a></li>
						<li><a href="#release"><span>Release Notes</span></a></li>
	            	</ul>
				    <div id="setup">
	               	<?php
					if (substr(sprintf('%o', fileperms($siteRoot . "/ant")), -4) != "0777") {
						echo "<div class='error'>Your Ant Directory is not writable</div>";
					}

					if ( !is_dir($siteRoot . "/temp") || substr(sprintf('%o', fileperms($siteRoot . "/temp")), -4) != "0777") {
						echo "<div class='error'>Your Temp Directory is not writable</div>";
					}
					?>

						<fieldset>
							<?php
								$options = transverse($root);
							?>
							<legend>Ditamap to be transformed ( <?php echo sizeof($options)?> available)</legend>
							<select name="target" id="target">
								<?php
							foreach ($options as $option) {
								printf("<option value='%s'>%s</option>" , $option, basename($option));
							}
							?>
							</select>
							<p><a href='javascript:void(0)' onclick="window.open('edit.php?map=' + document.getElementById('target').value ,'editor')">Edit Map</a></p>
							<p style='font-size:7pt;margin-top:1em'>For a list of your maps update your default repository directory by editing this PHP script.</p>
						</fieldset>
						<fieldset>
							<legend>Output Format</legend>
							<select name="template">
								<?php
								foreach ($transforms as $transform=>$transformLabel) {
									if ($transformLabel == "pdf") {
										printf("<option value='%s' selected='true'>%s</option>" , $transform, $transformLabel);
									} else { 
										printf("<option value='%s'>%s</option>" , $transform, $transformLabel);
									}
								}
								?>
							</select>
						</fieldset>
						<p id="submit"><input type="submit" value="Build Deliverable  >>" id="buildmap" onclick="return handlePost();"></p>
	            </div>
	            <div id="conditional">
					<h1>Conditional Processing</h1>
					<p>The default filter will <strong>EXCLUDE</strong> the options selected.</p>
					<fieldset style="width:160px;float:left;">
						<legend>Audience</legend>
						<?php
							foreach ($audiences as $val=>$label) {
								printf("<input type='checkbox' name='condition[audience][]' value='%s'> %s<br />" , $val, $label);
							}
						?>
					</fieldset>
					<fieldset style="width:160px;float:left;margin-left:20px">
						<legend>Platform</legend>
						<?php
							foreach ($platforms as $val=>$label) {
								printf("<input type='checkbox' name='condition[platform][]' value='%s'> %s<br />" , $val, $label);
							}
						?>
					</fieldset>
					<fieldset style="width:160px;float:left;margin-left:20px">
						<legend>Product</legend>
						<?php
							foreach ($products as $val=>$label) {
								printf("<input type='checkbox' name='condition[product][]' value='%s'> %s<br />" , $val, $label);
							}
						?>
					</fieldset>
					<br style="clear:both" />
	            </div>
	            <div id="advanced">
				<p>Still under development</p>
	            </div>
	            <div id="ouput_tab">
					<h1>Output</h1>
					<div id="output">
						<p>There is currently no output available. Build and debug information will appear on this tab once you have submitted a ditamap for processing.</p>
					</div>
	            </div>
	            <div id="about" style="position:relative">
					<h1>About the DITA Open Toolkit WebGUI</h1>
				 	<p>DITA Open Toolkit WebGUI is a Web-front end to the DITA Open Toolkit<br /><img src='webnetix.jpg' style="position:absolute;top:10px;right:10px"/>
				    Copyright (C) 2007 Alex Griessel ( <a href='http://www.webnetix.co.za'>Webnetix</a> )</p>

				    <p>This program is free software: you can redistribute it and/or modify
				    it under the terms of the GNU General Public License as published by
				    the Free Software Foundation, either version 3 of the License, or
				    (at your option) any later version.</p>

				    <p>This program is distributed in the hope that it will be useful,
				    but WITHOUT ANY WARRANTY; without even the implied warranty of
				    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
				    GNU General Public License for more details.</p>

				    <p>You should have received a copy of the GNU General Public License
				    along with this program.  If not, see <a href='http://www.gnu.org/licenses/'>http://www.gnu.org/licenses/</a>.</p>
	            </div>
	            <div id="release" style="position:relative">
					<h1>DITA Open Toolkit WebGUI Release Notes</h1>
					<br />
					<pre><?php include_once './RELEASE_NOTES';?></pre>
					
	            </div>
	        </div>
		<div id="footer">
		copyright &copy; 2007 Webnetix
		</div>
	</div>
	</form>
	
</body>
</html>
<?php
}
?>
