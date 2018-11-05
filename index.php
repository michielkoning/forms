<?php
/**
 * @package mk_forms
 * @version 1.0
 */
/*
Plugin Name: Michiel Koning Formulierplugin
Plugin URI: http://www.michielkoning.nl
Author: Michiel Koning
Version: 1.0
Author URI: http://www.michielkoning.nl
*/

require_once( __DIR__ . '/includes/install.php');
require_once( __DIR__ . '/includes/admin.php');
require_once( __DIR__ . '/includes/translations.php');

function mk_forms_load_textdomain() {
  load_plugin_textdomain( 'mk_forms', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'mk_forms_load_textdomain' );

function contact__add($message, $type, &$name = '') {
	global $wpdb;

	$result = $wpdb->insert(
		$wpdb->prefix . 'contact',
		array(
			'type'    => $type,
			'name'    => $name,
			'body'    => $message
		),
		array(
			'%s',
			'%s',
			'%s'
		)
	);

	return $result;
}

function contact__send_mail($message, $subject, $type, &$name = '', &$to = '', &$cc = false) {

	//de mailtemplate
	$mailbody =
	'<html>
	<head>
	<title></title>
	</head>
	<body>
	<style type="text/css">
		body, table  { font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:20px; color:#333; }
		td		{ padding:2px 0; vertical-align:top; }
		a		{ color:#5e5e5e; }
	</style>

	<table cellpadding="0" cellspacing="0" border="0" width="600">
		<tr>
			<td><img src="' . get_bloginfo('template_url') . '/assets/images/logo.png" alt="" /></td>
		</tr>

		<tr>
			<td style="padding:20px 0;">' . $message . '</td>
		</tr>
	</table>
	</body>
	</html>';

	if ($to == '')
		$to = get_field('email', 'option');

	if ($cc)
		$to .= ',' . $cc;

	$mailheaders = "MIME-Version: 1.0\n" .
		"From: " . get_field('email', 'option') . "\n" .
		"Content-Type: text/html; charset=\"" . get_bloginfo('charset') . "\"\n";

	wp_mail($to, $subject, $mailbody, $mailheaders);
}