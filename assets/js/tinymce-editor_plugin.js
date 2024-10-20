/*! One User Avatar - 2.3.9
 * Copyright One Designs
 * Copyright ProfilePress
 * Copyright Flippercode
 * Copyright Bangbay Siboliban
 * Licensed GPLv2
 */

(function () {
	var args = typeof one_user_avatar_tinymce_editor_args != 'undefined' ?
		one_user_avatar_tinymce_editor_args :
		{
			insert_avatar: 'Insert Avatar',
		};

	tinymce.create('tinymce.plugins.wpUserAvatar', {
		init: function (ed, url) {
			ed.addCommand('mceWpUserAvatar', function() {
				ed.windowManager.open({
					file:   ajaxurl + '?action=wp_user_avatar_tinymce',
					width:  500,
					height: 360,
					inline: 1
				}, {
					plugin_url: url,
				});
			});

			ed.addButton('wpUserAvatar', {
				title: args.insert_avatar,
				cmd:   'mceWpUserAvatar',
				image: url + '/../images/wpua-20x20.png',
				onPostRender: function() {
					var ctrl = this;

					ed.on('NodeChange', function(e) {
						ctrl.active(e.element.nodeName == 'IMG');
					});
				}
			});
		},
		createControl: function(n, cm) {
			return null;
		},
		getInfo: function () {
			return {
				longname:  'One User Avatar',
				author:    'One Designs',
				authorurl: 'https://onedesigns.com/',
				infourl:   'https://onedesigns.com/plugins/one-user-avatar/',
				version:   '2.3.9',
			};
		},
	});

	tinymce.PluginManager.add('wpUserAvatar', tinymce.plugins.wpUserAvatar);
})();
