<?php
/*
Plugin Name: QR code for short URLs
Plugin URI: https://github.com/SebastianLang-GER/YOURLS-QR-code
Description: Add ".qr" to the shorturl or click the button in the admin panel to display a QR code.
Version: 1.0
Author: Sebastian Lang
Author URI: https://sebastianlang.net
*/

// Kick in if the loader does not recognize a valid pattern
yourls_add_action('redirect_keyword_not_found', 'yourls_qrcode_get', 1);

function yourls_qrcode_get($request) {
	// Get authorized charset in keywords and make a regexp pattern
	$pattern = yourls_make_regexp_pattern(yourls_get_shorturl_charset());

	// Shorturl is like test.qr?
	if (preg_match("@^([$pattern]+)\.qr?/?$@", $request[0], $matches)) {
		// this shorturl exists?
		$keyword = yourls_sanitize_keyword($matches[1]);
		if (yourls_is_shorturl($keyword)) {
			// Show the QR code then!
			// API documentation: https://goqr.me/api/doc/create-qr-code/
			// Terms: Requesting IP and referrer are loged, but not requested QR data. Limit 10,000 requests per day.
			header('Location: https://api.qrserver.com/v1/create-qr-code/?format=svg&qzone=2&ecc=M&data='.YOURLS_SITE.'/'.$keyword);
			exit;
		}
	}
}

// Add our QR Code Button to the Admin interface
yourls_add_filter( 'action_links', 'yourls_qrcode_add_button' );
function yourls_qrcode_add_button( $action_links, $keyword, $url, $ip, $clicks, $timestamp ) {
	$surl = yourls_link( $keyword );
	$id = yourls_string2htmlid( $keyword ); // used as HTML #id

	// Add our QR code generator button to the action links list
  $action_links .= '<a href="" id="qrlink-' . $id . '" class="button button_qrcode" title="QR Code" onclick="window.open(\'' . $surl . '.qr\', \'_blank\', \'height=275,width=275,left=50,top=100,scrollbars=0,location=no,status=no\'); return false;">QR Code</a>';

  return $action_links;
}

// Add the CSS to <head>
yourls_add_action( 'html_head', 'yourls_qrcode_add_css_head' );
function yourls_qrcode_add_css_head( $context ) {

	// expose what page we are on
	foreach($context as $k):

		// If we are on the index page, use this css code for the button
		if( $k == 'index' ):
?>
			<style type="text/css">
				td.actions .button_qrcode {
					margin-right: 0;
					background: url(data:image/png;base64,R0lGODlhEAAQAIAAAAAAAP///yH5BAAAAAAALAAAAAAQABAAAAIvjI9pwIztAjjTzYWr1FrS923NAymYSV3borJW26KdaHnr6UUxd4fqL0qNbD2UqQAAOw==) no-repeat 2px 50%;
	  visibility: visible !important;
				}
			</style>

<?php
		endif;
	endforeach;
}