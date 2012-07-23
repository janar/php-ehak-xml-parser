
function ehakFilter(currentObj, nextId, level){
	var selectedCountyId = currentObj.options[currentObj.selectedIndex ].value;
	var childObj = document.getElementById(nextId);

	childObj.options.length = 0;
	childObj.options[childObj.options.length] = new Option('-', '', false, false);

	if(level == 1){
		for (var i in citiesAndMunicipalities) {
			if(citiesAndMunicipalities[i].parentId == selectedCountyId){
				childObj.options[childObj.options.length] = new Option(citiesAndMunicipalities[i].shortLabel, citiesAndMunicipalities[i].id, false, false);
			}
		}
	} else if(level == 2) {
		for (var i in cityPartsAndVillages) {
			if(cityPartsAndVillages[i].parentId == selectedCountyId){
				childObj.options[childObj.options.length] = new Option(cityPartsAndVillages[i].originalLabel, cityPartsAndVillages[i].id, false, false);
			}
		}
	}
}