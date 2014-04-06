var $j = jQuery.noConflict();

(function ($) {
	var addFlattr, showHide, showHideSelect, shortcodeValues, shortcodeGenerator, deleteImageButton;

	// Adds the flattr button
	addFlattr = function () {
		var s = document.createElement('script'),
				t = document.getElementsByTagName('script')[0];

		s.type = 'text/javascript';
		s.async = true;
		s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
		t.parentNode.insertBefore(s, t);
	};

	// Show/Hide a div
	showHide = function ($div, title, alt, id) {
		var $link;

		$div.hide();
		$link = $('<a />').attr('href', '#').addClass(id).text(title).insertBefore($div);
		$link.click(function (e) {
			$div.slideToggle().toggleClass('open');
			if ($div.hasClass('open')) {
				$link.text(alt);
			} else {
				$link.text(title);
			}
			e.preventDefault();
		});
	};

	// Show/Hide slidet plugin info
	showHideSelect = function ($select) {
		var val = $select.val();

		if (val.length) {
			$('.plugin-info.' + val).show();
		}

		$select.change(function () {
			val = $select.val();
			$('.plugin-info').slideUp();
			if (val.length) {
				$('.plugin-info.' + val).slideDown();
			}
		});
	};

	// Retreives values for the shortcode generator
	shortcodeValues = function () {
		var value = '';

		$('input[type="text"], select').each(function () {
			var $this = $(this);
			if ($this.val().length) {
				value += ' ' + $this.attr('id') + '="' + $this.val() + '"';
			}
		});

		$('input[type="checkbox"]').each(function () {
			var $this = $(this);
			if ($this.is(':checked')) {
				value += ' ' + $this.attr('id') + '="' + $this.val() + '"';
			} else {
				value += ' ' + $this.attr('id') + '="' + $this.prev().val() + '"';
			}
		});

		$('#good-old-gallery-shortcode code').text('[good-old-gallery' + value + ']');
		value = '';
	};

	// Shortcode generator
	shortcodeGenerator = function () {
		$('#go-gallery-generator input[type="checkbox"], #go-gallery-generator select').each(function () {
			$this = $(this);
			$this.change(shortcodeValues);
		});

		$('#go-gallery-generator input[type="text"]').each(function () {
			$this = $(this);
			$this.keyup(shortcodeValues);
		});
	};

	deleteImageButton = function () {
		$('a.submitdelete').each(function () {
			var $this = $(this).click(function () {
					attID = $this.attr('data-id');

					$.ajax({
							type: 'post',
							url: 'admin-ajax.php',
							data: {
									action: 'delete_attachment',
									att_ID: attID,
									_ajax_nonce: $this.attr('data-nonce'),
									post_type: 'attachment'
							},
							success: function (data, textStatus, jqXHR) {
									$this.parents('li').html(data).addClass('deleted');
							}
					});

					return false;
			});
		});
	};

	$(function () {
		var $this, body = $('body');

		// Shortcode generator tab
		if (body.attr('id') == 'media-upload') {
			shortcodeValues();
			shortcodeGenerator();
		}

		console.log(body);
		deleteImageButton();

		// Themes page
		if (body.hasClass('goodoldgallery_page_gog_themes')) {
			addFlattr();
			showHide($('.goodoldgallery_page_gog_themes .themes-available'), 'View installed themes', 'Hide installed themes', 'themes-link');
		}

		// Settings page
		if (body.hasClass('goodoldgallery_page_gog_settings')) {
			addFlattr();
			showHideSelect($('select#plugin'));

			// Make fields sortable
			if ($.isFunction($.fn.sortable)) {
				$('#order').sortable({
					update : function (event, ui) {
						var order = $(this).sortable('toArray');
						$.each(order, function(index) {
							$('#order_' + order[index]).val(index+1);
						});
					}
				}).next('table').hide();
			}
		}
	});
}($j));