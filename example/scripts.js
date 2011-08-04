
function ehakFilterCities(countiesObj, citiesSelectId){
	var selectedCountyId = countiesObj.options[countiesObj.selectedIndex ].value;
	var citiesObj = document.getElementById(citiesSelectId);
	
	citiesObj.options.length = 0;
	for (var i in cities) {
		if(cities[i].parentId == selectedCountyId){
			citiesObj.options[citiesObj.options.length] = new Option(cities[i].label, cities[i].id, false, false);
		}
	}
	
}
