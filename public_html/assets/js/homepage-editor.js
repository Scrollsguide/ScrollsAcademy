(function($) {
	var originalBlockNum = 0;
	function init() {
		originalBlockNum = $('.block').length;
		$('body').on('click', '[data-add-block]', addBlock);
		$('body').on('click', '[data-remove-block]', removeBlock);
	}

	function addBlock() {
		var $blocks = $('.block');
		
		var newBlockIndex = $blocks.length > originalBlockNum ? $blocks.length : originalBlockNum++;
		
		var $newBlock = $('#blockTemplate').html();

		var str = $newBlock.replace(/blocks\[0\]/g, 'blocks['+newBlockIndex+']');
		console.log(str)
		$($('.form-group')[$('.form-group').length-1]).before(str);

		return false;
	}

	function removeBlock() {
		$(this).parents('.block').remove();
	}

	$(init);
}(jQuery))