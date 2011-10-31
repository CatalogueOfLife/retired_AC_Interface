function createGeoJsonPolygon_nojquery(geojson,region_standard_id,clickable, regionId, regionName){
	var strokeColor = "#F00";
	var fillColor = "#F00";
	var strokeOpacity = 1;
	var fillOpacity = 0.5;
/*	if(region_standard_id == 2) {
		strokeColor = "#F00";
		fillColor = "#F00";
	} else if (region_standard_id == 3) {
		strokeColor = "#FF0";
		fillColor = "#FF0";
	}*/
	
	if(clickable == true) {
		strokeOpacity = 0;
		fillOpacity = 0;
	}
	
	/* non-jQuery function */
	var coords = geojson.coordinates;
	var paths = [];
	for (i=0;i<coords.length;i++){
		for (j=0;j<coords[i].length;j++){
			var path=[];
			for (k=0;k<coords[i][j].length;k++){
				var ll = new google.maps.LatLng(coords[i][j][k][1],coords[i][j][k][0]);
				path.push(ll);
			}
			paths.push(path);
		}
	}
	strokeOpacity = 0;
	var polygon = new google.maps.Polygon({
		paths: paths,
		strokeColor: strokeColor,
		strokeOpacity: strokeOpacity,
		strokeWeight: 1,
		fillColor: fillColor,
		fillOpacity: fillOpacity,
		id: regionId,
		name: regionName
	});
	return polygon;
}

function showRegions() {
	var numberOfRegions = regions.length;
	for (var i = 0; i < numberOfRegions; i++)
	{
		var region = getRegion(regions[i]);
	}
}

function showRegion(region) {
	var polygon = createGeoJsonPolygon_nojquery(eval('('+region.polygon+')'),region.region_standard_id,false,region.id,region.name);
	polygon.setMap(map);
	highligthedAreas[region.id] = true;
 	google.maps.event.addListener(polygon, 'mouseover', areaMouseOver);
 	google.maps.event.addListener(polygon, 'mouseout', areaMouseOut);
	progressBar();
}

progressBarCounter = 0;
function progressBar() {
	var numberOfRegions = regions.length;
	progressBarCounter++;
	var progressbar = document.getElementById('map_progress_bar');
	if(progressBarCounter >= numberOfRegions) {
		progressbar.innerHTML = translate('All_regions_retrieved');
	} else {
		progressbar.innerHTML = progressBarCounter + ' ' + translate('out_of') + ' ' + numberOfRegions + ' ' + translate('regions_retrieved');
	}
}

function getRegion(region_id) { // 
    dojo.xhrGet( { // 
        url: baseUrl+"/ajax/region/region/" + region_id, 
        handleAs: "json",
        timeout: 10000, 
        load: function(response, ioArgs) { 
    		showRegion(response);
        },
        error: function(response, ioArgs) {  
            console.error(response); 
            failedToRetrieveAjax("failed_to_retrieve_region");
        }
    });
}

function getRegions(taxon_id,rank) { // 
    dojo.xhrGet( { // 
        url: baseUrl+"/ajax/regions/taxon/" + taxon_id + "/rank/" + rank, 
        handleAs: "json",
        timeout: 10000, 
        load: function(response, ioArgs) { 
    		storeRegions(response);
        },
        error: function(response, ioArgs) {  
            console.error(response); 
            failedToRetrieveAjax("failed_to_retrieve_regions");
        }
    });
}

function storeRegions(region_ids){
	if(region_ids == '') {
		document.getElementById('map_progress_bar').innerHTML = translate('There_are_no_regions_to_show');
		return;
	}
	regions = Array();
	for(var i = 0; i < region_ids.length; i++) {
		regions[i] = region_ids[i].region_id;
	}
	showRegions();
}

function getRegionsInRegionSelect(regionStandardId) {
	createMap();
	regionStandard = regionStandardId;
	createPolygonsOnMap();
	var select = document.getElementById('regionSelectList');
	if ( select.hasChildNodes() )
	{
	    while ( select.childNodes.length >= 1 )
	    {
	    	select.removeChild( select.firstChild );       
	    }
	}
    dojo.xhrGet( { // 
        url: baseUrl+"/ajax/regionlist/regionStandard/" + regionStandardId, 
        handleAs: "json",
        timeout: 1000, 
        load: function(response, ioArgs) { 
    		insertRegions(response);
        },
        error: function(response, ioArgs) {  
            console.error(response); 
            failedToRetrieveAjax("failed_to_retrieve_regions");
        }
    });
}

function insertRegions(regions) {
	var select = document.getElementById('regionSelectList');
	for (var i = 0; i < regions.length; i++)
	{
		var option = document.createElement('option');
		option.id = 'region_' + regions[i].id;
		option.value = regions[i].id;
		option.innerHTML = regions[i].name;
		option.onclick = function () {
			var regionId = this.id;
			regionId = regionId.replace("region_","");
			highLightArea(regionId);
		};
//		option.onclick = 'javascript:highLightArea('+regions[i].id+');';
		select.appendChild(option);
	}	
}

function failedToRetrieveAjax(message) {
	alert(translate(message));
}

function clearDistributionSearchForm() {
/*	region = Array();
	createMap();
	highligthedAreas = Array();*/
	//Calling a new ajax query to retrieve a clean list.
	/*getRegionsInRegionSelect(regionStandard);*/
	document.getElementById('clear_form').submit();
}