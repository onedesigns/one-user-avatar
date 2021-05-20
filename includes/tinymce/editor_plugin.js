(function () {
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
                title: "Insert One User Avatar",
                cmd:   'mceWpUserAvatar',
                image: url + '/../../images/wpua-20x20.png',
            });

			ed.onNodeChange.add(function(ed, cm, n) {
				b.setActive('wpUserAvatar', n.nodeName == 'IMG');
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
                version:   '1.9.13',
            };
		},
	});

	tinymce.PluginManager.add('wpUserAvatar', tinymce.plugins.wpUserAvatar);
})();
