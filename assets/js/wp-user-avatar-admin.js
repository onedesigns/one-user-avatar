/*! One User Avatar - 2.5.0
 * Copyright One Designs
 * Copyright ProfilePress
 * Copyright Flippercode
 * Copyright Bangbay Siboliban
 * Licensed GPLv2
 */

jQuery(function($) {
	// Show size info only if allow uploads is checked
	$('#wp_user_avatar_allow_upload').change(function() {
		$('#wpua-contributors-subscribers').slideToggle($('#wp_user_avatar_allow_upload').is(':checked'));
	});

	// Show resize info only if resize uploads is checked
	$('#wp_user_avatar_resize_upload').change(function() {
		 $('#wpua-resize-sizes').slideToggle($('#wp_user_avatar_resize_upload').is(':checked'));
	});

	// Hide Gravatars if disable Gravatars is checked
	$('#wp_user_avatar_disable_gravatar').change(function() {
		if( $('#wp-avatars').length ) {
			$('#wp-avatars, #avatar-rating').slideToggle(!$('#wp_user_avatar_disable_gravatar').is(':checked'));
			$('#wp_user_avatar_radio').trigger('click');
		}
	});

	// Update readable size on keyup
	$('#wp_user_avatar_upload_size_limit').on('input', function() {
		var wpuaUploadSizeLimit = $(this).val();

		wpuaUploadSizeLimit = wpuaUploadSizeLimit.replace(/\D/g, "");

		$(this).val(wpuaUploadSizeLimit);

		$('#wpua-readable-size').html( Math.floor( wpuaUploadSizeLimit / 1024 ) + 'KB' );
		$('#wpua-readable-size-error').toggle( wpuaUploadSizeLimit > parseInt( wpua_admin.max_upload_size ) );
		$('#wpua-readable-size').toggleClass( 'wpua-error', wpuaUploadSizeLimit > parseInt( wpua_admin.max_upload_size ) );
	});

	// Confirm deleting avatar
	$('.wpua-media-form .show-confirmation').on('click', function() {
		if ( typeof showNotice == 'object' && typeof showNotice.warn == 'function' ) {
			return showNotice.warn();
		}
	});
});
