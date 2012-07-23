<?php
/**
 * Class for parsing Classification of Estonian administrative units and settlements xml
 * provided by http://metaweb.stat.ee/
 * 
 * Use it however you want. This is very robust approach.
 * Use at your own risk. No warranties are offered. 
 * 
 * @version 1.0.2
 * @author Janar Jürisson <janar@eagerfish.eu>
 * @link http://eagerfish.eu/projects/php-ehak-xml-parser/examples/
 */

class EhakXmlParser {
	
	/**
	 * Will hold SimpleXMLElement object
	 */
	private $xmlObject = null;
	
	/**
	 * Return all counties found in xml
	 * 
	 * @param Boolean $useIdAsKey Use county ID as key id true
	 * 
	 * @return array
	 */
	public function getCounties($useIdAsKey = false){
		return $this->getItems($useIdAsKey, 'county');
	}
	
	/**
	 * Return all rural municipalities and cities found in xml
	 * Also adds parent county ID
	 * 
	 * @param Boolean $useIdAsKey Use county ID as key id true
	 *
	 * @return array
	 */
	public function getCitiesAndMunicipalities($useIdAsKey = false){
		return $this->getItems($useIdAsKey, array('city', 'municipality'));
	}

	/**
	 * Return all cityparts and villages found in xml
	 * Also adds parents ID's and experimental shortLabel attribute
	 *
	 * @param Boolean $useIdAsKey Use county ID as key id true
	 *
	 * @return array
	 */
	public function getCitpartsAndVillages($useIdAsKey = false){
		return $this->getItems($useIdAsKey, array('citypart', 'village', 'smalltown', 'city without status'));
	}

	/**
	 * Return all items which are requested by $returnTypes found in xml
	 * This is main deeply nested (ugly) part of the script which goes trough all the items and it's childs
	 *
	 * @param Boolean $useIdAsKey Use county ID as key id true
	 * @param Mixed $returnTypes Which types of items to return
	 *
	 * @return array
	 */
	public function getItems($useIdAsKey = false, $returnTypes){
		$items = array();
		
		if(!is_array($returnTypes)){
			$returnTypes = array( (string) $returnTypes );
		}
		
		if(!$this->xmlObject || !count($returnTypes)){
			return $items;
		}

		
		foreach($this->xmlObject->Classification->Item as $k => $xmlCounty){
			$currentCountyId = (string) $xmlCounty->attributes()->id;

			//return county objects if "county" is present in return types
			if(in_array('county', $returnTypes)){
				$county = new stdClass();
				$county->id = $currentCountyId;
				$county->type = 'county';
				$county->originalLabel = (string) $xmlCounty->Label->LabelText;
				$county->shortLabel = str_replace(' county', '', $county->originalLabel);

				//Estonian variant of xml has also short form value attached and
				//we can use it instead
				if(isset($xmlCounty->Property->PropertyQualifier->PropertyText)){
					$county->shortLabel = (string) $xmlCounty->Property->PropertyQualifier->PropertyText;
					$county->shortLabel = str_replace('Lühivorm: ', '', $county->shortLabel);
				}

				if($useIdAsKey){
					$items[$county->id] = $county;
				} else {
					$items[] = $county;
				}

				unset($county);
			}


			foreach($xmlCounty->Item as $k2 => $xmlItem){
				//is it city or rural municipality?
				$test = (string) $xmlItem->Label->LabelText;
				$type = (substr($test, -8) == ': cities' || substr($test, -8) == ': linnad') ? 'city' : 'municipality';

				$addCM = false;
				$addCM = (in_array('city', $returnTypes) && $type == 'city') ? true : $addCM;
				$addCM = (in_array('municipality', $returnTypes) && $type == 'municipality') ? true : $addCM;

				foreach($xmlItem->Item as $k3 => $xmlCity){
					$currentMunicipalityOrCityId = (string) $xmlCity->attributes()->id;
					
					if($addCM){
						$item = new stdClass();
						$item->id = $currentMunicipalityOrCityId;
						$item->parentId = $currentCountyId;
						$item->type = $type;
						$item->originalLabel = (string) $xmlCity->Label->LabelText;
						$item->shortLabel = trim(str_replace(array(' rural municipality', ' city', ' linn', ' vald'), '', $item->originalLabel));

						if($useIdAsKey){
							$items[$item->id] = $item;
						} else {
							$items[] = $item;
						}
						
						unset($item);
					}
					


					foreach($xmlCity->Item as $k4 => $xmlItem2){
						$test = (string) $xmlItem2->Label->LabelText;
						
						//is it citypart?
						$isCityPart = (substr($test, -14) == ' city district' || substr($test, -9) == ' linnaosa') ? true : false;
						$isCityWithoutStatus = (substr($test, -31) == ': city without municipal status' || substr($test, -21) == ': vallasisesed linnad') ? true : false;
						
						if($isCityPart && in_array('citypart', $returnTypes)){
							$cityPart = new stdClass();
							$cityPart->id = (string) $xmlItem2->attributes()->id;
							$cityPart->parentId = $currentMunicipalityOrCityId;
							$cityPart->type = 'citypart';
							$cityPart->originalLabel = (string) $xmlItem2->Label->LabelText;
							$cityPart->shortLabel = trim(str_replace(array(' city district', ' linnaosa'), '', $cityPart->originalLabel));
							
							if($useIdAsKey){
								$items[$cityPart->id] = $cityPart;
							} else {
								$items[] = $cityPart;
							}
							
							//in citypart, there is nothing to iterate anymore
							continue;
						}
						
						if($isCityWithoutStatus && in_array('city without status', $returnTypes)){
							foreach($xmlItem2->Item as $k3 => $xmlLevel4){
								$city = new stdClass();
								$city->id = (string) $xmlLevel4->attributes()->id;
								$city->parentId = $currentMunicipalityOrCityId;
								$city->type = 'city without status';
								$city->originalLabel = (string) $xmlLevel4->Label->LabelText;
								$city->shortLabel = trim(str_replace(array(' vallasisene linn', ' city without municipal status'), '', $city->originalLabel));
								
								if($useIdAsKey){
									$items[$city->id] = $city;
								} else {
									$items[] = $city;
								}
							}
							//in city without status, there is nothing to iterate anymore
							continue;
						}

						//is it village or small town?
						$type = (substr($test, -10) == ': villages' || substr($test, -8) == ': külad') ? 'village' : 'smalltown';
						
						foreach($xmlItem2->Item as $k3 => $xmlCpartVillage){
							$addCpV = false;
							$addCpV = (in_array('smalltown', $returnTypes) && $type == 'smalltown') ? true : $addCpV;
							$addCpV = (in_array('village', $returnTypes) && $type == 'village') ? true : $addCpV;

							if($addCpV){
								$city = new stdClass();
								$city->id = (string) $xmlCpartVillage->attributes()->id;
								$city->parentId = $currentMunicipalityOrCityId;
								$city->type = $type;
								$city->originalLabel = (string) $xmlCpartVillage->Label->LabelText;
								$city->shortLabel = trim(str_replace(array(' small town', ' village', ' alevik', ' küla'), '', $city->originalLabel));

								if($useIdAsKey){
									$items[$city->id] = $city;
								} else {
									$items[] = $city;
								}
							}
						}
						
					}
					
				}
			}

		}

		return $items;
	}
	
	/**
	 * Create SimpleXMLElement from given string
	 * Return true on success
	 *
	 * @param String $input Xml content to set
	 * 
	 * @return boolean
	 */
	public function setXmlFromString($input){
		try {
			$this->xmlObject = new SimpleXMLElement($input);
		} catch (Exception $e) {
			return false;
		}
		
		return true;
	}
}
