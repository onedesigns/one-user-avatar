/*! One User Avatar - 2.5.0
 * Copyright One Designs
 * Copyright ProfilePress
 * Copyright Flippercode
 * Copyright Bangbay Siboliban
 * Licensed GPLv2
 */

function wpuaInsertAvatar() {
	// Custom shortcode values
	var shortcode,
		closing_tag,
		user          = document.getElementById('wp_user_avatar_user').value,
		size          = document.getElementById('wp_user_avatar_size').value,
		size_number   = document.getElementById('wp_user_avatar_size_number').value,
		align         = document.getElementById('wp_user_avatar_align').value,
		link          = document.getElementById('wp_user_avatar_link').value,
		link_external = document.getElementById('wp_user_avatar_link_external').value,
		target        = document.getElementById('wp_user_avatar_target').value,
		caption       = document.getElementById('wp_user_avatar_caption').value;

	// Add tag to shortcode only if not blank
	var user_tag = (user != '') ? ' user="' + user + '"' : '';
	var size_tag = (size != '' && size_number == '') ? ' size="' + size + '"' : '';

	size_tag = (size_number != '') ? ' size="' + size_number + '"' : size_tag;

	var align_tag = (align != '') ? ' align="' + align + '"' : '';

	var link_tag = (link != '' && link != 'custom-url' && link_external == '') ? ' link="' + link + '"' : '';

	link_tag = (link_external != '') ? ' link="' + link_external + '"' : link_tag;

	var target_tag = document.getElementById('wp_user_avatar_target').checked && (link_tag != '') ? ' target="' + target + '"' : '';

	// Assemble the shortcode
	closing_tag = (caption != '') ? "]" + caption + "[/avatar]" : " /]";
	shortcode = "<p>[avatar" + user_tag + size_tag + align_tag + link_tag + target_tag + closing_tag + "</p>";

	// Insert into Visual Editor
	if ( window.tinyMCE ) {
		var tmce_ver = window.tinyMCE.majorVersion;

		if ( tmce_ver >= "4" ) {
			window.tinyMCE.execCommand( 'mceInsertContent', false, shortcode );
		} else {
			window.tinyMCE.execInstanceCommand( window.tinyMCE.activeEditor.id, 'mceInsertContent', false, shortcode );
		}

		tinyMCEPopup.editor.execCommand( 'mceRepaint' );

		tinyMCEPopup.close();
	}

	return;
}

function wpuaInsertAvatarUpload() {
	// Upload shortcode
	var shortcode = "<p>[avatar_upload /]</p>";

	// Insert into Visual Editor
	if ( window.tinyMCE ) {
		var tmce_ver = window.tinyMCE.majorVersion;

		if ( tmce_ver >= "4" ) {
			window.tinyMCE.execCommand( 'mceInsertContent', false, shortcode );
		} else {
			window.tinyMCE.execInstanceCommand( window.tinyMCE.activeEditor.id, 'mceInsertContent', false, shortcode );
		}

		tinyMCEPopup.editor.execCommand( 'mceRepaint' );

		tinyMCEPopup.close();
	}
	return;
}

jQuery(function($) {
	// Show link input
	$('#wp_user_avatar_link').change(function() {
		$('#wp_user_avatar_link_external_section').toggle( $('#wp_user_avatar_link').val() == 'custom-url' );
	});

	// Show size input
	$('#wp_user_avatar_size').change(function() {
		$('#wp_user_avatar_size_number_section').toggle( $('#wp_user_avatar_size').val() == 'custom' );
	});

	$("#wpua-tabs li a").click(function(){
		tab_id = $(this).attr('href');

		if( tab_id == '#wpua') {
			$("#wpua-upload").hide();
		} else {
			$('#wpua').hide();
		}

		$(tab_id).show();
	});
});
