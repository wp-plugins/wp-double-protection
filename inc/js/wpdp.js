/* global ajaxurl, pwsL10n */
(function($){

	function wpdb_check_pass_strength() {
		var pass1 = $('#secondpass1').val(), pass2 = $('#secondpass2').val(), strength;

		$('#secondpass-strength-result').removeClass('short bad good strong');
		if ( ! pass1 ) {
			$('#secondpass-strength-result').html( pwsL10n.empty );
			return;
		}

		strength = wp.passwordStrength.meter( pass1, wp.passwordStrength.userInputBlacklist(), pass2 );

		switch ( strength ) {
			case 2:
				$('#secondpass-strength-result').addClass('bad').html( pwsL10n.bad );
				break;
			case 3:
				$('#secondpass-strength-result').addClass('good').html( pwsL10n.good );
				break;
			case 4:
				$('#secondpass-strength-result').addClass('strong').html( pwsL10n.strong );
				break;
			case 5:
				$('#secondpass-strength-result').addClass('short').html( pwsL10n.mismatch );
				break;
			default:
				$('#secondpass-strength-result').addClass('short').html( pwsL10n['short'] );
		}
	}

	$(document).ready( function() {
		var $colorpicker, $stylesheet, user_id, current_user_id,
			select = $( '#display_name' );

		$('#secondpass1').val('').keyup( wpdb_check_pass_strength );
		$('#secondpass2').val('').keyup( wpdb_check_pass_strength );
		$('#secondpass-strength-result').show();	
	});

})(jQuery);
