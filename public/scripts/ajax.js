function parseImageResponse(response, noResultMessage) {
	var topRow = '';
	var bottomRow = '';
	var minHeight = getImageMinHeight(response);
	var maxWidth = 250;
	if (response.numberOfResults == 0) {
		var response = noResultMessage;
	} else {
		for ( var i = 0; i < response.numberOfResults; i++) {
			var image = response.results[i];
			imageDimensions = getImageDimensions(image.height, image.width,
					minHeight, maxWidth);
			topRow += '<td class="thumbnail-image" style="width: ' + (imageDimensions['width'] + 2) + 'px;">';
			topRow += '<a href="' + image.href + '" target="_blank">';
			topRow += '<img class="thumbnail" ';
			topRow += 'height="' + imageDimensions['height'] + '" ';
			topRow += 'width="' + imageDimensions['width'] + '" ';
			topRow += 'src="' + image.src + '" ';
			topRow += 'alt="' + image.caption + '" ';
			topRow += 'title ="' + image.caption + '" />';
			topRow += '</a></td>';
			bottomRow += '<td class="thumbnail-caption" style="width: ' + (imageDimensions['width'] + 2) + 'px;">';
			bottomRow += image.source + '</td>'
		}
		var response = '<table id="thumbnails-table"><tr>' + topRow + '</tr><tr>' + bottomRow + '</tr></table>';
	}
	dojo.byId("thumbnails-placeholder").innerHTML = response;
}

function throwImageResponseError(message) {
	dojo.byId("thumbnails-placeholder").innerHTML = message;
}

function getImageMinHeight(response) {
	var minHeight = 250;
	for ( var i = 0; i < response.numberOfResults; i++) {
		var currentHeight = parseInt(response.results[i].height);
		if (minHeight > currentHeight) {
			minHeight = currentHeight;
		}
	}
	return parseInt(minHeight);
}

function getImageDimensions(height, width, minHeight, maxWidth) {
	var dimensions = new Array();
	for ( var i = 0; i < arguments.length; i++) {
		parseInt(arguments[i]);
	}
	if (height > minHeight || width > maxWidth) {
		var heightRatio = minHeight / height;
		var widthRatio = maxWidth / width;
		var ratio = (heightRatio < widthRatio) ? heightRatio : widthRatio;
		height = Math.round(ratio * height);
		width = Math.round(ratio * width);
	}
	dimensions['height'] = parseInt(height);
	dimensions['width'] = parseInt(width);
	return dimensions;
}
