function createGeoJsonPolygon_nojquery(geojson,region_standard_id,clickable, regionId, regionName){
	var strokeColor = "#0F0";
	var fillColor = "#0F0";
	var strokeOpacity = 1;
	var fillOpacity = 0.5;
	if(region_standard_id == 2) {
		strokeColor = "#F00";
		fillColor = "#F00";
	} else if (region_standard_id == 3) {
		strokeColor = "#FF0";
		fillColor = "#FF0";
	}
	
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
		strokeWeight: 2,
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
	var progressbar = document.getElementById('map_progress_bar');
	progressbar.innerHTML = 'id: ' + region.id + '; name: ' + region.name;
	progressBar();
}

progressBarCounter = 0;
function progressBar() {
	var numberOfRegions = regions.length;
	progressBarCounter++;
	var progressbar = document.getElementById('map_progress_bar');
	if(progressBarCounter >= numberOfRegions) {
		progressbar.innerHTML = 'All region\'s retrieved.';
	} else {
		progressbar.innerHTML = progressBarCounter + ' out of ' + numberOfRegions + ' regions retrieved.';
	}
}

function getRegion(region_id) { // 
    dojo.xhrGet( { // 
        url: "/ACI/ajax/region/region/" + region_id, 
        handleAs: "json",
        timeout: 10000, 
        load: function(response, ioArgs) { 
    		showRegion(response);
        },
        error: function(response, ioArgs) {  
            console.error(response); 
            throwImageResponseError("Failed_to_fetch_images");
        }
    });
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
        url: "/ACI/ajax/regionlist/regionStandard/" + regionStandardId, 
        handleAs: "json",
        timeout: 1000, 
        load: function(response, ioArgs) { 
    		insertRegions(response);
        },
        error: function(response, ioArgs) {  
            console.error(response); 
            throwImageResponseError("Failed_to_fetch_regions");
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
		select.appendChild(option);
	}	
}