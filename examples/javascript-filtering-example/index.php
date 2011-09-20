<?php
header('Content-type: text/html; charset=utf-8');

require '../../src/EhakXmlParser.php';

$inputXmlPath = '../../input-xml/EHAK2011v1_en.xml';

$xml = new EhakXmlParser();
if($xml->setXmlFromString(file_get_contents($inputXmlPath))){
	$counties = $xml->getCounties(true);
	$citiesAndMunicipalities = $xml->getCitiesAndMunicipalities(true);
}

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
		<h1>EhakXmlParser example</h1>
		<p>This is sample usage of ehakXmlParser class  written in PHP. After selecting county it refreshes list of "City or rural municipality" to corresponding values. </p>
		<p>Read <a href="http://eagerfish.eu/estonian-ehak-xml-parser-written-in-php/">blog post</a> about it or <a href="https://github.com/janar/php-ehak-xml-parser">download</a> it from github.</p>
		
		County:
		<select id="counties" onchange="ehakFilterCities(this, 'citiesAndMunicipalities')">
			<option value="">-</option>
			<?php foreach($counties as $k => $county){ ?>
				<option value="<?php echo $county->id; ?>"><?php echo $county->shortLabel; ?></option>
			<?php } ?>
		</select>
		<br />
		
		City or rural municipality:
		<select id="citiesAndMunicipalities">
			<option value="">-</option>
		</select>
		
		<script type="text/javascript">
			
			//create array of cities for filtering script
			var key = 0;
			var cities = new Array();
			<?php foreach($citiesAndMunicipalities as $k => $city){ ?>
				var city = new Object();
				city.label = '<?php echo $city->shortLabel; ?>';
				city.id = '<?php echo $city->id; ?>';
				city.parentId = '<?php echo $city->parentCountyId; ?>';
				cities[key] = city;
				key++;
			<?php } ?>
			
		</script>
	</body>
</html>