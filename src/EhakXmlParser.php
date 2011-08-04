<?php
/**
 * Class for parsing Classification of Estonian administrative units and settlements xml
 * provided by http://metaweb.stat.ee/
 * It currently parses only counties, cities and rural municipalities. 
 * 
 * Use it however you want. 
 * Use at your own risk. No warranties are offered. 
 * 
 * @author Janar Jürisson <janar@eagerfish.eu>
 * @version 1.0
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
	 * @return array
	 */
	public function getCounties($useIdAsKey = false){
		$counties = array();	
		
		if(!$this->xmlObject){
			return $counties;
		}
		
		foreach($this->xmlObject->Classification->Item as $k => $xmlCounty){
			
			$county = new stdClass();
			$county->id = (string) $xmlCounty->attributes()->id;
			$county->type = 'county';
			$county->originalLabel = (string) $xmlCounty->Label->LabelText;
			$county->shortLabel = str_replace(' county', 'maa', $county->originalLabel);
			
			if($useIdAsKey){
				$counties[$county->id] = $county;
			} else {
				$counties[] = $county;
			}
			
			unset($county);
		}
		
		return $counties;
	}
	
	/**
	 * Return all rural municipalities and cities found in xml
	 * Also adds parent county ID
	 * 
	 * @param Boolean $useIdAsKey Use county ID as key id true
	 * @return array
	 */
	public function getCitiesAndMunicipalities($useIdAsKey = false){
		$citiesAndMunicipalities = array();	
		
		if(!$this->xmlObject){
			return $citiesAndMunicipalities;
		}
		
		foreach($this->xmlObject->Classification->Item as $k => $xmlCounty){
			$currentCountyId = (string) $xmlCounty->attributes()->id;
			
			foreach($xmlCounty->Item as $k2 => $xmlItem){
				
				//is it city or rural municipality?
				$test = (string) $xmlItem->Label->LabelText;
				$type = substr($test, -8) == ': cities' ? 'city' : 'municipality';
				
				foreach($xmlItem->Item as $k3 => $xmlCity){
					
					$city = new stdClass();
					$city->id = (string) $xmlCity->attributes()->id;
					$city->parentCountyId = $currentCountyId;
					$city->type = $type;
					$city->originalLabel = (string) $xmlCity->Label->LabelText;
					$city->shortLabel = str_replace(array(' rural municipality', ' city'), '', $city->originalLabel);
					
					if($useIdAsKey){
						$citiesAndMunicipalities[$city->id] = $city;
					} else {
						$citiesAndMunicipalities[] = $city;
					}
					
				}
			}
			
		}
		
		return $citiesAndMunicipalities;
	}
	
	/**
	 * Create SimpleXMLElement from given string
	 * Return true on success
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
