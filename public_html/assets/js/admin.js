if (typeof window.RedactorPlugins === 'undefined') {
	window.RedactorPlugins = {};
}

//file upload
(function() {
	var files;

	// Grab the files and set them to our variable
	function prepareUpload(el, cb) {
		files = event.target.files;
		uploadFiles(el, cb);

		return false;
	}

	function init() {
		$('input[name=file]').on('change', function() {
			prepareUpload($(this), updateForm);
		});
	}

	function uploadFiles(el, callback) {
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
					callback(el, data);
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

	function updateForm(el, data) {
		var path = '/assets/images/user-imgs/' + data.filename;
		var $thumb = el.parent().parent().find('[img-thumbnail]');
		$thumb.attr('href', path);
		$thumb.find('img').attr('src', path);
		el.parent().parent().find('[name=image]').val(data.filename);
	}

	$(init);
}());

/* The guide editor */
(function($) {

	var MarkdownSettings = {
		nameSpace: 'markdown', // Useful to prevent multi-instances CSS conflict
		previewParserPath: '/admin/guide/precompile',
		previewParserVar: 'guide',
		previewInWindow: 'width=800, height=600, resizable=yes, scrollbars=yes',
		onShiftEnter: {
			keepDefault: false,
			openWith: '\n\n'
		},
		previewAutoRefresh: true,
		markupSet: [{
			name: 'Heading 5',
			key: "5",
			openWith: '##### ',
			className: 'hdr',
			placeHolder: 'Your title here...'
		}, {
			separator: '---------------'
		}, {
			name: 'Bold',
			key: "B",
			className: 'bold',
			openWith: '**',
			closeWith: '**'
		}, {
			name: 'Italic',
			key: "I",
			className: 'italic',
			openWith: '_',
			closeWith: '_'
		}, {
			separator: '---------------'
		}, {
			name: 'Bulleted List',
			className: 'bull',
			openWith: '- '
		}, {
			name: 'Numeric List',
			className: 'num',
			openWith: function(markItUp) {
				return markItUp.line + '. ';
			}
		}, {
			separator: '---------------'
		}, {
			name: 'Picture',
			key: "P",
			className: 'pic',
			replaceWith: function(markItUp) {
				$('#myModal').modal({});
				$('#myModal .btn-primary').unbind().on('click', function() {
					var src = $('#myModal [name=image]').val();
					$.markItUp( 
						{ replaceWith:'![](/assets/images/user-imgs/'+src+')' }
					);
					$('#myModal').modal('hide');
				});
			}
		}, {
			name: 'Picture Left',
			className: "pic-left",
			replaceWith: function(markItUp) {
				return '{.left}';
			}
		}, {
			name: 'Picture Right',
			className: "pic-right",
			replaceWith: function(markItUp) {
				return '{.right}';
			}
		}, {
			name: 'Picture Full Width',
			className: "pic-full",
			replaceWith: function(markItUp) {
				return '{.full}';
			}
		}, {
			name: 'Picture Center No Resize',
			className: "pic-center",
			replaceWith: function(markItUp) {
				return '{.center}';
			}
		},{
			separator: '---------------'
		}, {
			name: 'Link',
			key: "L",
			className: 'link',
			openWith: '[',
			closeWith: ']([![Url:!:http://]!] "[![Title]!]")',
			placeHolder: 'Your text to link here...'
		}, {
			separator: '---------------'
		}, {
			name: 'Quotes',
			className: 'quote',
			openWith: '> '
		}, {
			name: 'Line Break',
			className: "line-break",
			replaceWith: function(markItUp) {
				return "<br />";
			}
		}, {
			separator: '---------------'
		}, {
			name: 'Preview',
			call: 'preview',
			className: "preview"
		}]
	}

	// mIu nameSpace to avoid conflict.
	miu = {
		markdownTitle: function(markItUp, char) {
			heading = '';
			n = $.trim(markItUp.selection || markItUp.placeHolder).length;
			for (i = 0; i < n; i++) {
				heading += char;
			}
			return '\n' + heading + '\n';
		}
	}
	$(document).ready(function() {
		$('#markdown').markItUp(MarkdownSettings);
	});
}(jQuery));