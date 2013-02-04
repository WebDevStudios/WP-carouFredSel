jQuery(document).ready(function($) {
	// make sure we were passed an element
	if ( window.wdscaroufredsel.element === undefined )
		return;
	// If we were giving configuration parameters, add them
	if ( window.wdscaroufredsel.params !== undefined )
		$(wdscaroufredsel.element).carouFredSel(wdscaroufredsel.params);
	else
		$(wdscaroufredsel.element).carouFredSel();
});