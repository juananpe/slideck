<?php

$manifestHeader = '<?xml version="1.0" encoding="UTF-8"?>
<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.2">
<manifest:file-entry manifest:full-path="/" manifest:version="1.2" manifest:media-type="application/vnd.oasis.opendocument.presentation"/>
<manifest:file-entry manifest:full-path="meta.xml" manifest:media-type="text/xml"/>';
$manifestFooter = '<manifest:file-entry manifest:full-path="settings.xml" manifest:media-type="text/xml"/>
<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>
<manifest:file-entry manifest:full-path="Thumbnails/thumbnail.png" manifest:media-type="image/png"/>
<manifest:file-entry manifest:full-path="Configurations2/accelerator/current.xml" manifest:media-type=""/>
<manifest:file-entry manifest:full-path="Configurations2/" manifest:media-type="application/vnd.sun.xml.ui.configuration"/>
<manifest:file-entry manifest:full-path="styles.xml" manifest:media-type="text/xml"/>
</manifest:manifest>';

$destination = "template/Pictures/";

if (is_dir("template"))
exec("rm -rf template");

exec("unzip cleantemplate.zip") or die("Can't unzip cleantemplate.zip");

if ($handle = opendir($argv[1])) {
	echo "copying pictures from $argv[1] to template/Pictures :\n";

	if (!is_dir($destination))
		mkdir($destination);

	$picNum = 0;
	while (false !== ($entry = readdir($handle))) {
		if (!is_dir($entry) && preg_match ("/\.(jpeg|jpg|png)$/i", $entry))
		{

			$img = getimagesize($argv[1] . "/" . $entry);
			$fileExt = image_type_to_extension($img[2]);
			$picNum++;
			$destImg = $picNum.$fileExt;
			echo $destination . $destImg . "\n";
			copy($argv[1]."/".$entry, $destination . $destImg);
		}
	}

	closedir($handle);

	$header = "header.txt";
	$footer = "footer.txt";
	$pictureTemplate = "picture.txt";
	$templateContent = "template/content.xml";
	$templateManifest = "template/META-INF/manifest.xml";

	if (file_exists($header) && file_exists($footer) && file_exists($pictureTemplate))
	{
		$headerLines = file_get_contents($header);
		$footerLines = file_get_contents($footer);
		$pictureContent = file_get_contents($pictureTemplate);
		$pictures = array();
		$patterns = array();
		$replacements = array();
		$manifest = array();

		for($i = 1; $i <= $picNum; $i++){
			// one picture per page
			$patterns[0] = "/%PAGENUM%/";
			$patterns[1] = "/%PICTURENAME%/";
			$replacements[0] = "$i";
			$replacements[1] = $i.$fileExt;
			$manifest[] = '<manifest:file-entry manifest:full-path="Pictures/' . $i . $fileExt . '" manifest:media-type=""/>' . "\n";

			$pictures[] = preg_replace($patterns,$replacements, $pictureContent);

		}

		// generate content.xml
		file_put_contents($templateContent, $headerLines, FILE_APPEND);
		file_put_contents($templateContent, $pictures, FILE_APPEND);
		file_put_contents($templateContent, $footerLines ,FILE_APPEND);

		// generate manifest.xml
		file_put_contents($templateManifest, $manifestHeader, FILE_APPEND);
		file_put_contents($templateManifest, $manifest, FILE_APPEND);
		file_put_contents($templateManifest, $manifestFooter, FILE_APPEND);

	}
	exec("(cd template ; zip -9 -r ../slideck.odp .)");
}
