var leadID = '';
var loadingImg = '/wp-content/plugins/affinity-costco/public/images/ajax-loading.gif';

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */

	jQuery(window).load(function() {
		if ( jQuery('#ap_costco_form').length > 0 ) {
			jQuery('#ap_costco_form').on('submit', verifyCostcoMembership);
		}

		if ( jQuery('#leadid').length > 0 ) {
			jQuery('#leadid').on('blur', validateLeadID);
		}

		if ( jQuery('#mid').length > 0 ) {
			jQuery('#mid').on('keyup', verifyConfirm);
		}

		if ( jQuery('#apc_iagree').length > 0 ) {
			jQuery('#apc_iagree').on('click', function() { verifyConfirm(); });
		}
	});
})( jQuery );


function validateLeadID(event) {
	var lid = jQuery(this).val().replace(/[^0-9-_]/g, '');

	if ( lid.length > 4 && lid != leadID) {
		jQuery(this).css({
			'background-image': 'url(' + loadingImg + ')',
			'background-repeat': 'no-repeat',
			'background-position': '98% center'
		});
		jQuery('#leadResult').html('Searching...');

		leadID = lid;
		jQuery.get('/wp-content/plugins/affinity-costco/proxy.php?api=lead&leadid=' + lid + '&lender=' + lenderID, leadCallback);
	}
	else if ( lid != leadID ) {
		leadCallback('');
	}
}

function leadCallback(result) {
	jQuery('#leadid').css('background-image', '');

	if ( result == '' ) {
		leadID = null;
		jQuery('#confirmOpenMarket').css('display','block');
		jQuery('#leadResult').html((jQuery('#leadid').val().trim() == '' ? '&nbsp;' : 'No lead found matching the ID you provided.'));
	} else {
		jQuery('#confirmOpenMarket').css('display','none');
		jQuery('#leadResult').html('Lead Found: <strong>' + result.fname + ' ' + result.lname + '</strong>');
	}

	verifyConfirm();
}

function verifyConfirm() {
	var aia = '#apc_iagree:';

	if ( jQuery('#mid').val().trim().length != 12 || (leadID == null && jQuery(aia + 'checked').length < 1) || (jQuery(aia + 'visible').length > 0 && jQuery(aia + 'visible:checked').length < 1) ) {
		jQuery('#apc_submit').attr('disabled', 'disabled');
		return false;
	} else {
		jQuery('#apc_submit').removeAttr('disabled');
		return true;
	}
}

function verifyCostcoMembership(event) {
	event.preventDefault();

	var mid = jQuery('#mid').val().replace(/\D/g, '');

	if ( mid.length == 12 && verifyConfirm() ) {
		jQuery('#apc_submit').attr('disabled', 'disabled');
		jQuery('.apc-waiting').css('display', 'inline-block');
		jQuery.get('/wp-content/plugins/affinity-costco/proxy.php?summary=0&api=membership&leadid=' + leadID + '&mid=' + mid + '&lender=' + lenderID, membershipCallback);
	} else {

	}

	return false;	
}

function membershipCallback(result) {
	var elem = jQuery('#costco_member_results');

   	jQuery('#apc_submit').removeAttr('disabled');
	jQuery('.apc-waiting').css('display', 'none');

	if ( typeof result == 'object' && 'valid' in result ) {
		if (result['valid'] == false) {
			jQuery('<div><strong>Invalid Membership</strong><br>Lead ID: ' + (leadID == null ? '' : leadID) + '<br>Membership ID: ' + jQuery('#mid').val() + ' is invalid.</div>').prependTo(elem);
			leadID = null;
			jQuery('#ap_costco_form')[0].reset();
		} else { 
			var leadHTML = '<div>Lead ID: ' + (leadID == null ? '' : leadID) + '<br>Membership ID: ' + result.id  + (result.executive ? ' (Executive Member - ' : ' (') + result.memberType + ')<br>Name: ' + result.lname + ', ' + result.fname + '</div>';
			jQuery(leadHTML).prependTo(elem);			

			leadID = null;
			jQuery('#ap_costco_form')[0].reset();
		}
	 }
}