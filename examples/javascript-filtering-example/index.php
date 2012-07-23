<?php
header('Content-type: text/html; charset=utf-8');

require '../../src/EhakXmlParser.php';

$inputXmlPath = '../../input-xml/EHAK2012v1.xml';

$xml = new EhakXmlParser();
if(!$xml->setXmlFromString(file_get_contents($inputXmlPath))){
	die("Problem with input");
}
$counties = $xml->getCounties(true);

?><!DOCTYPE html 
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>PHP ehakXmlParser example</title>
		<meta name="description" content="Classification of Estonian administrative units and settlements parser example" />
		<meta name="keywords" content="estonian, settlements, parser, example" />
		<script type="text/javascript" src="scripts.js"></script>
	</head>
	<body>
		<h1>PHP EhakXmlParser example</h1>
		<p>This is sample usage of ehakXmlParser class  written in PHP. After selecting county it refreshes list of "City or rural municipality" to corresponding values. Same with third level. </p>
		<p>Read <a href="http://eagerfish.eu/estonian-ehak-xml-parser-written-in-php/">blog post</a> about it or <a href="https://github.com/janar/php-ehak-xml-parser">download</a> it from github.</p>
		
		County:
		<select id="counties" onchange="ehakFilter(this, 'citiesAndMunicipalities', 1);">
			<option value="">-</option>
			<?php foreach($counties as $k => $county){ ?>
				<option value="<?php echo $county->id; ?>"><?php echo $county->originalLabel; ?></option>
			<?php } ?>
		</select>
		<br />
		
		City or rural municipality:
		<select id="citiesAndMunicipalities" onchange="ehakFilter(this, 'citypartsAndVillages', 2);">
			<option value="">-</option>
		</select>
		<br />

		Citypart/village/smalltown and other:
		<select id="citypartsAndVillages">
			<option value="">-</option>
		</select>
		
		<script type="text/javascript">
			var citiesAndMunicipalities = <?php echo json_encode($xml->getCitiesAndMunicipalities(true)); ?>;
			var cityPartsAndVillages = <?php echo json_encode($xml->getCitpartsAndVillages(true)); ?>;
		</script>
	</body>
</html>