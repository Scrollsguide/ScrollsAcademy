(function(window, $, undefined) {
	'use strict';

	function loadVideo() {
		var $container = $(this);
		$container.addClass('playing');

		var $placeholder = $container.find('.player-placeholder');
		$placeholder.attr('id', 'video'+Math.floor(Math.random()*10000));

		new window.YT.Player($placeholder.attr('id'), {
			videoId: $placeholder.attr('data-video-id'),
			playerVars: {
				autoplay: 1
			}
		});

		return false; //stop prop and prev def
	}

	function init() {
		// Load the Youtube IFrame Player API code asynchronously.
		var tag = document.createElement('script');
		tag.src = 'https://www.youtube.com/player_api';
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

		//bind 
		$('.content-block').on('click', '.video-container', loadVideo);
	}


	$(init);
}(window, jQuery));