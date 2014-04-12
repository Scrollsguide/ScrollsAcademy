if (typeof window.RedactorPlugins === 'undefined') {
	window.RedactorPlugins = {};
}

//file upload
(function() {
	var files;

	// Grab the files and set them to our variable
	function prepareUpload(cb) {
		files = event.target.files;
		uploadFiles(cb);

		return false;
	}

	function init() {
		$('input[name=file]').on('change', function() {
			prepareUpload(updateForm);
		});
	}

	function uploadFiles(callback) {
		// Create a formdata object and add the files
		var data = new FormData();
		$.each(files, function(key, value) {
			data.append(key, value);
		});

		$.ajax({
			url: '/admin/image/save',
			type: 'POST',
			data: data,
			cache: false,
			dataType: 'json',
			processData: false, // Don't process the files
			contentType: false, // Set content type to false as jQuery will tell the server its a query string request
			success: function(data, textStatus, jqXHR) {
				if (typeof data.error === 'undefined') {
					// Success so call function to process the form
					callback(data);
				} else {
					// Handle errors here
					alert('error uploading image, see console for details');
					console.log(data, textStatus, jqXHR);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				// Handle errors here
				alert('error uploading image, see console for details');
				console.log(jqXHR, textStatus, errorThrown);
			}
		});
	}

	function updateForm(data) {
		var path = '/assets/images/user-imgs/' + data.filename;
		var $thumb = $('[img-thumbnail]');
		$thumb.attr('href', path);
		$thumb.find('img').attr('src', path);
		$('input[name=image]').val(data.filename);
	}

	$(init);

	window.RedactorPlugins.imgUploader = {
		init: function() {
			var self = this;

			this.buttonAdd('imgUploader', 'Upload Image', this.imgUploader);
			this.buttonAwesome('imgUploader', 'fa-camera');
		},
		imgUploader: function() {
			var cb = $.proxy(function() {
				this.selectionSave();
				$('#redactor_modal #imageuploader-insert').click($.proxy(this.insertImage, this))

				$('input[name=inline-file]').on('change', function(event) {
					files = event.target.files;
					prepareUpload(function(data) {
						var path = '/assets/images/user-imgs/' + data.filename;
						var $thumb = $('[thumbnail]');
						$thumb.attr('href', path);
						$thumb.find('img').attr('src', path);
						$('#redactor_modal input[name=imagepath]').val(path);
					});
				});
			}, this);

			this.modalInit('Image Uploader', '#imageuploader', 500, cb);
		},
		insertImage: function(html) {
			this.selectionRestore();
			this.insertHtml('<img src="' + $('#redactor_modal input[name=imagepath]').val() + '" />');
			this.modalClose();
		}
	}
}());

/* The guide editor */
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
			plugins: ['markdownView', 'imgUploader'],
			buttons: ['bold', 'italic', 'deleted', '|',
				'unorderedlist', 'orderedlist',
				'table', 'link', '|',
				'horizontalrule', 'imgUploader'
			]
		});

		//cache the redactor instance
		red = $red.data('redactor');

		//bind to make sure we sync before submit
		$('form').on('submit', function() {
			syncModel();
			//if they have the markdown code editor open, we want that to be the value instead
			if ($mdEditor && $mdEditor.is(':visible')) {
				$valTextarea.val($mdEditor.val());
			}
		})
	}

	function syncModel() {
		var markdown = rem.render(red.get())
		$valTextarea.val(markdown);
	}

	//setup the plugin for editing the raw markdown
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