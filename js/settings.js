(function ($) {
	$(document).ready(function () {
		const wpOpenGallery = function (o, callback) {
			const options = typeof o === 'object' ? o : {};

			// Predefined settings
			const defaultOptions = {
				title: 'Select Media',
				fileType: 'image',
				multiple: false,
				currentValue: '',
			};

			const opt = { ...defaultOptions, ...options };

			let image_frame;

			if (image_frame) {
				image_frame.open();
			}

			// Define image_frame as wp.media object
			image_frame = wp.media({
				title: opt.title,
				multiple: opt.multiple,
				library: {
					type: opt.fileType,
				},
			});

			image_frame.on('open', function () {
				// On open, get the id from the hidden input
				// and select the appropiate images in the media manager
				const selection = image_frame.state().get('selection');
				const ids = opt.currentValue.split(',');

				ids.forEach(function (id) {
					const attachment = wp.media.attachment(id);
					attachment.fetch();
					selection.add(attachment ? [attachment] : []);
				});
			});

			image_frame.on('close', function () {
				// On close, get selections and save to the hidden input
				// plus other AJAX stuff to refresh the image preview
				const selection = image_frame.state().get('selection');
				const files = [];

				selection.each(function (attachment) {
					files.push({
						id: attachment.attributes.id,
						filename: attachment.attributes.filename,
						url: attachment.attributes.url,
						type: attachment.attributes.type,
						subtype: attachment.attributes.subtype,
						sizes: attachment.attributes.sizes,
					});
				});

				callback(files);
			});

			image_frame.open();
		};

		// Set the logo attachment ID and preview on click.
		$('#orbit_ui_login_logo_button').click(function(event) {
			wpOpenGallery(null, function(data) {
				$('#orbit_ui_login_logo').val(data[0].id);
				$('#login-logo-preview').attr('src', data[0].url);
			});
		});
	});
})(jQuery);
