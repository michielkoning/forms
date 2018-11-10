<?php
function contact__show_mails()
{
    $type = isset($_GET['type']) ? $_GET['type'] : null; ?>
  <div class="wrap">
    <h2>Maillogs</h2>

    <?php contact__show_list($type); ?>
  </div>
  <?php
}

function contact__show_list($type = '')
{
    global $wpdb;

    $wpdb->contact = $wpdb->prefix . 'contact';
    $where = $type ? "WHERE type = '$type'" : '';

    $contactlist = $wpdb->get_results(
        "SELECT * FROM $wpdb->contact $where ORDER BY enterdate DESC"
    );
    foreach ($contactlist as $contactitem) { ?>
    <a href="#" onclick="jQuery('#contact-<?php echo $contactitem->id; ?>').slideToggle();return false"><strong><?php echo $contactitem->name; ?></strong></a> (<?php echo mysql2date(
    'j F Y H:i',
    $contactitem->enterdate
); ?>)<br />
    <div id="contact-<?php echo $contactitem->id; ?>" style="display:none;">
      <?php echo $contactitem->body; ?>
    </div>
  <?php }
}

add_action('admin_menu', 'add_contact_block');
function add_contact_block()
{
    add_menu_page(
        'Maillogs',
        'Maillogs',
        'edit_pages',
        'contact_handle',
        'contact__show_mails',
        'dashicons-email-alt'
    );
}
