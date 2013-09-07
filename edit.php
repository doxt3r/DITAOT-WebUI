<?php

/*

*/
$map = isset($_GET['map'])?$_GET['map']:0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>test</title>
	<script src="js/jquery-1.2.1.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/interface/iutil.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/interface/idrag.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/interface/idrop.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/interface/isortables.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/inestedsortable-1.0.1.pack.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery.form.js" type="text/javascript" charset="utf-8"></script>
	<script type="text/javascript" charset="utf-8">
		function saveDocument() {
			document.getElementById('mapPayload').value = document.getElementById('mapCanvas').innerHTML;
			function successFn() {
				alert('Map Saved');
			}
			
			var options = {
				type: 'POST',
				success: (successFn != undefined) ? successFn : 0,
				semantic: true
			};
	
			$("#frmActions").ajaxSubmit(options);
			return false;
		}
	</script>
</head>
<body>
	<div id="wrapper">
		<form action="save.php" method="POST" accept-charset="utf-8" id="frmActions" name="frmActions">
			<div id="mapCanvas">
		<?php
		$xslt = new xsltProcessor();
		$stylesheet = (isset($_GET['stylesheet']))?$_GET['stylesheet']:'map2html';
		$xslt->importStyleSheet(DomDocument::load('./xsl/' . $stylesheet . '.xsl'));
		$xmlDoc = new DomDocument();
		$xmlDoc->resolveExternals = true;
		$xmlDoc->validateOnParse = true;
		$xmlDoc->normalizeDocument();
		
		$x = $xmlDoc->load('file://' . $map);
		
		$html = $xslt->transformToXML($xmlDoc);
		echo $html;
		?>
		</div>
		<textarea type="text" value="" name="mapPayload" id="mapPayload" rows="20" cols="30" style="display:none">
		</textarea>
		<input type="text" name="mapPath" value="<?php echo $map ?>" />
		
	</div>

		<p><input type="submit" value="Save &rarr;" onclick="return saveDocument()"></p>
	</form>
	<fieldset>
		<legend>Repository</legend>
		<input type="text" name="" value="" />
		<ul id="respository">
			<?php
			for ($i = 0; $i <= 100; $i++) {
				printf("<li class='sortable-element-class'>Topic %s</li>", $i);
			}
			?>
		</ul>
	</fieldset>
	<script type="text/javascript" charset="utf-8">
		jQuery( function($) {
			$('#list-container').NestedSortable(
		  {
		    accept: 'sortable-element-class',
		  }
		);
		});
		
		jQuery( function($) {
			$('#respository').NestedSortable(
		  {
		    accept: 'sortable-element-class',
		  }
		);
		});
	</script>
</body>
</html>