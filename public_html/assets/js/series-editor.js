(function($) {

	function init() {
		$('body').on('click', '[data-add-guide]', addGuide);
	}

	function addGuide() {
		var $newBlock = $('#guideTemplate').html();

		$("#guides").append($newBlock);

		return false;
	}

	$(init);
}(jQuery))