// ====================================================================================
// INITITALISE ALL MODULES

$(document).ready(function() {

	// Initialising the SelectionButtons GOVUK module
	var $blockLabels = $(".block-label input[type='radio'], .block-label input[type='checkbox']");
	new GOVUK.SelectionButtons($blockLabels);

	// Initialising the format currency module
	$('.js-format-currency').on('blur', function (event) {
        GOVUK.formatCurrency(event.target);
    });

    // Removal of parent element (used on prototype of OTPP notification)
    // Refactor
    $('.js-remove').click(function(){
    	$(this).parent().remove();
    });

    // Initialising the Show Hide Content GOVUK module
    var showHideContent = new GOVUK.ShowHideContent();
    showHideContent.init();
});