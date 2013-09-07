<?php
$xslt = new xsltProcessor();
$stylesheet = (isset($_GET['stylesheet']))?$_GET['stylesheet']:'html2map';
$xslt->importStyleSheet(DomDocument::load('./xsl/' . $stylesheet . '.xsl'));
$xmlDoc = new DomDocument();
$xmlDoc->resolveExternals = true;
$xmlDoc->validateOnParse = true;
$xmlDoc->normalizeDocument();

$x = $xmlDoc->loadXML(stripslashes($_POST['mapPayload']));

$html = $xslt->transformToXML($xmlDoc);

$fp = fopen($_POST['mapPath'], 'w');
fwrite($fp, $html);
fclose($fp);
?>