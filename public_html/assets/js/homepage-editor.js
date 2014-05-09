(function($) {
	var originalBlockNum = 0;
	function init() {
		originalBlockNum = $('.block').length;
		$('body').on('click', '[data-add-block]', addBlock);
		$('body').on('click', '[data-remove-block]', removeBlock);

		$('body').on('change', 'select[name$="[layout]"]', function(){
			var blockId = $(this).attr("name").match(/blocks\[(\d+)\]/)[1];

			var numOfBlocks = $(this).find(":selected").attr("data-guides");
			var blockParent = $(this).parents(".block");
			var guideRowsContainer = blockParent.find(".guiderows");
			var guidesInContainer = guideRowsContainer.find(".form-group");

			if (guidesInContainer.length > numOfBlocks){
				guidesInContainer.slice(numOfBlocks).remove();
			} else if (guidesInContainer.length < numOfBlocks) {
				var newGuideRow = $("#guideSelector").html();

				var str = newGuideRow.replace(/blocks\[0\]/g, 'blocks[' + blockId + ']');

				for (var i = guidesInContainer.length; i < numOfBlocks; i++){
					guideRowsContainer.append(str);
				}
			}

			var hasHeader = $(this).find(":selected").attr("data-header");
			if (typeof hasHeader !== 'undefined' && hasHeader === "0"){
				blockParent.find(".headerinput").css("display", "none");
			} else {
				blockParent.find(".headerinput").css("display", "block");
			}
		});

		$('.guiderows').each(function(){
			var newHtml = $("#guideSelector").html().replace(/blocks\[0\]/g, 'blocks[' + $(this).attr("data-row-id") + ']');
			var newRow = $(newHtml);

			var guides = $(this).attr("data-guides").split(",");
			for (var i = 0; i < guides.length; i++){
				var cpy = newRow.clone();
				var childs = cpy.find('select[name$="[guides][]"] option');
				for (var j = 0; j < childs.length; j++){
					if (childs[j].value === guides[i]){
						cpy.find('select[name$="[guides][]"]').val(guides[i]);
					}
				}
				$(this).append(cpy).removeAttr("data-guides");
			}
		});
	}

	function addBlock() {
		var $blocks = $('.block');
		
		var newBlockIndex = $blocks.length > originalBlockNum ? $blocks.length : originalBlockNum++;
		
		var $newBlock = $('#blockTemplate').html();

		var str = $newBlock.replace(/blocks\[0\]/g, 'blocks[' + newBlockIndex + ']');

		// add guide rows
		var newRowHtml = $("#guideSelector").html().replace(/blocks\[0\]/g, 'blocks[' + newBlockIndex + ']');

		var jObj = $($.parseHTML(str));
		jObj.find(".guiderows").html(newRowHtml);

		$($('.form-group')[$('.form-group').length-1]).before(jObj);

		return false;
	}

	function removeBlock() {
		$(this).parents('.block').remove();
	}

	$(init);
}(jQuery))