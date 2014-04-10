(function($) {

	var rem;
	var $red;
	var $valTextarea;

	var $mdEditor;
	var $wysiwyg;

	var red = null;

	function init() {
		rem = new reMarked();
		$red = $('#redactor');
		red = null;
		$valTextarea = $('textarea[name=content]');

		/*
			Redactor starts inserting divs instead of p tags if the editor doesnt start
			by having a paragraph.  Don't ask me why.
		*/
		if (!$red.val()) {
			$red.val('<p></p>');
		} else {
			var html = marked($red.val());
			$red.val(html);
		}

		//init redactor
		$red.redactor({
			changeCallback: syncModel,
			plugins: ['markdownView'],
			buttons: ['bold', 'italic', 'deleted', '|',
				'unorderedlist', 'orderedlist',
				'table', 'link', '|',
				'horizontalrule'
			]
		});

		//cache the redactor instance
		red = $red.data('redactor');

		//bind to make sure we sync before submit
		$('form').on('submit', function() {
			syncModel();
			//if they have the markdown code editor open, we want that to be the value instead
			if ($mdEditor.is(':visible')) {
				$valTextarea.val($mdEditor.val());
			}
		})
	}
	//setup the plugin for editing the raw markdown
	if (typeof window.RedactorPlugins === 'undefined') {
		window.RedactorPlugins = {};
	}

	function syncModel() {
		var markdown = rem.render(red.get())
		$valTextarea.val(markdown);
	}

	window.RedactorPlugins.markdownView = {
		init: function() {
			//add the button
			this.buttonAddFirst('markdown', 'Markdown', this.showMarkdownView);
			this.buttonAwesome('markdown', 'fa-code');
			//and the textarea
			this.getBox().append('<textarea class="mdview_editor" style="display: none;"></textarea>');
		},
		showMarkdownView: function() {
			$mdEditor = this.$box.find('.mdview_editor');
			$wysiwyg = this.$box.find('.redactor_editor');

			//if its currently visible, we should update the data back to redactor
			if ($mdEditor.is(':visible')) {
				var html = marked($mdEditor.val());
				var clean = this.cleanStripTags(html);

				this.$editor.html(clean);
				this.sync();

				//hide the editor and reset toolbar
				$mdEditor.hide();
				$wysiwyg.show();
				this.$toolbar.find('.re-icon:not(.re-markdown)').removeClass('redactor_button_disabled');
				this.buttonInactive('markdown');
				syncModel();
				return;
			}

			//show the editor and disable toolbar
			$wysiwyg.hide();
			this.$toolbar.find('.re-icon:not(.re-markdown)').addClass('redactor_button_disabled');
			this.buttonActive('markdown');
			$mdEditor.val(rem.render(this.get())).height($wysiwyg.height()).css('min-height', 300).show();
		}
	}

	$(init);
}(jQuery));