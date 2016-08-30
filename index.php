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

//Tabel aanmaken voor de contact
function contact_install () {
   	global $wpdb;

	// Check for capability
	if ( !current_user_can('activate_plugins') )
		return;

	// upgrade function changed in WordPress 2.3
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// add charset & collate like wp core
	$charset_collate = '';

	if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}

	$tbl_contact = $wpdb->prefix . 'contact';

	if($wpdb->get_var("show tables like '$tbl_contact'") != $tbl_contact) {

		$sql = "CREATE TABLE " . $tbl_contact . " (
		id BIGINT(20) NOT NULL AUTO_INCREMENT ,
		type VARCHAR(255) DEFAULT '' NOT NULL ,
		name VARCHAR(255) DEFAULT '' NOT NULL ,
		body text NULL ,
		enterdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY id (id)
		) $charset_collate;";
		dbDelta($sql);
	}
}

//Bij het activeren van de plugin wordt de installatie functie aangeroepen
register_activation_hook(__FILE__, 'contact_install' );

function show_mails(){
	$type = (isset($_GET['type'])) ? $_GET['type'] : null;
	?>
	<div class="wrap">
		<h2>Maillogs</h2>

		<div class="tablenav top">
			<div class="alignleft actions">
				<form method="get">
					<input type="hidden" name="page" value="contact_handle" />
					<select name="type">
						<option value="">Toon alle reacties</option>
						<option value="contact" <?php if ($type == 'contact') echo 'selected="selected"'; ?>>Contact</option>
						<option value="ondernemingsrecht" <?php if ($type == 'ondernemingsrecht') echo 'selected="selected"'; ?>>Ondernemingsrecht</option>
						<option value="arbeidsrecht" <?php if ($type == 'arbeidsrecht') echo 'selected="selected"'; ?>>Arbeidsrecht</option>
						<option value="bestuursrecht" <?php if ($type == 'bestuursrecht') echo 'selected="selected"'; ?>>Bestuursrecht</option>
						<option value="strafrecht" <?php if ($type == 'strafrecht') echo 'selected="selected"'; ?>>Strafrecht</option>
						<option value="personen-+en+familierecht" <?php if ($type == 'personen-+en+familierecht') echo 'selected="selected"'; ?>>Personen- en familierecht</option>

					</select>
					<input type="submit" value="Filter" class="button-secondary" id="post-query-submit" name="">
				</form>
			</div>
			<br class="clear">
		</div>

		<br />
		<?php show_list_mails($type); ?>
	</div>
	<?php
}

function show_list_mails($type = ''){
	global $wpdb;

	$wpdb->contact = $wpdb->prefix . 'contact';
	$where = ($type) ? "WHERE type = '$type'" : '';

	$contactlist = $wpdb->get_results("SELECT * FROM $wpdb->contact $where ORDER BY enterdate DESC");
	foreach ($contactlist as $contactitem) {
		?>
		<a href="#" onclick="jQuery('#contact-<?php echo $contactitem->id; ?>').slideToggle();return false"><strong><?php echo $contactitem->name; ?></strong></a> (<?php echo mysql2date('j F Y H:i',$contactitem->enterdate); ?>)<br />
		<div id="contact-<?php echo $contactitem->id; ?>" style="display:none;">
			<?php echo $contactitem->body; ?>
		</div>
	<?php
	}
}

add_action('admin_menu', 'add_contact_block');
function add_contact_block() {
	add_menu_page( 'Maillogs', 'Maillogs', 'edit_pages', 'contact_handle', 'show_mails', 'dashicons-email-alt');
}

add_action( 'wp_ajax_submit_ajax_form', 'submit_ajax_form' );
add_action( 'wp_ajax_nopriv_submit_ajax_form', 'submit_ajax_form' );

//Indien in frontend, roep de function update_newsletter op, zodra post[newsletter] gevuld is
function submit_ajax_form() {

	check_ajax_referer( 'submit-form', 'security' );

	$fields  = array();

	$fields[] = array(
		'label' => 'Naam',
		'value' =>  $_POST['contact-name']
	);

	$fields[] = array(
		'label' => 'E-mail',
		'value' =>  $_POST['contact-email']
	);

	$fields[] = array(
		'label' => 'Opmerkingen',
		'value' =>  $_POST['contact-comments']
	);

	$fields[] = array(
		'label' => 'Formulier',
		'value' =>  $_POST['formkey']
	);

	$fullname = $_POST['contact-name'];
	$subject = 'Reactie via website ' . get_bloginfo('url');

	$message =	'Er is een reactie binnengekomen op <a href="' . get_bloginfo('url') . '">' . get_bloginfo('url') . '</a>.
	<br /><br />
	De volgende gegevens zijn ingevuld:
	<br /><br />
	<table width="100%">';
	foreach ($fields as $field) {
		$message .= '<tr><td width="150">' . $field['label'] . '</td><td>' . $field['value'] . '</td></tr>';
	}
	$message .= '<tr><td colspan="2">&nbsp;</td></tr>
	</table>';

	contact_send_mail($message, $subject, $_POST['formkey'], $log = true, $fullname);

	echo '<p>Hartelijk dank voor uw reactie. Wij nemen zo snel mogelijk contact met u op.</p>';

	if (isset($_POST['ajax'])) {
		die();
	}
}

function contact_send_mail($message, $subject, $type, $log = false, &$name = '', &$to = '', &$cc = false) {

	if ($log) {
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

		if (!$result) return;
	}

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
		$to = MAILTO;

	if ($cc)
	    $to .= ',' . $cc;

	$mailheaders = "MIME-Version: 1.0\n" .
		"From: michielkoning@gmail.com\n" .
		"Content-Type: text/html; charset=\"" . get_bloginfo('charset') . "\"\n";

	mail($to, $subject, $mailbody, $mailheaders);
}