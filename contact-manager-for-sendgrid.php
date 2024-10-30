<?php

/*
Plugin Name: Contact Manager for SendGrid
Plugin URI: http://www.tradelineworks.com
Description: A plugin for adding contacts to SendGrid contact lists
Version: 1.2
Author: Zeb Fross
Author URI: http://www.zebfross.com
License: GPL2
License URI: https://opensource.org/licenses/gpl-2.0.php
*/

namespace ContactManagerForSendGrid;

include "includes/SendGridApiCMFS.php";
include "includes/wpforms/SendGridWpformsCMFS.php";

class ContactManagerForSendGridPlugin
{
    private static $slug = "contacts-for-sendgrid";

    /**
     * Print a html select dropdown with given items and selected key
     *
     * @param array<string> $items items to render
     * @param string $selected selected key
     * @param string $name name of html element
     */
    public static function print_select(array $items, $selected, $name, $size=300)
    {
        ?>
        <select name="<?php echo esc_attr($name) ?>" style='width:<?php echo esc_attr($size); ?>px'>
            <?php foreach ($items as $key => $value) : ?>
                <option value="<?php echo esc_attr($key) ?>" <?php echo esc_attr(selected($key, $selected, false)); ?>><?php echo esc_html($value) ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function print_input($args)
    {
        $name = $args['field'];
        $size = $args['size'];
        $value = get_option($name, $args['default']);
        if ($args['type'] == 'textarea') {
            // textarea
            echo "<textarea name='" . esc_attr($name) . "' rows=6 cols=100>" . esc_html($value) . "</textarea>";
        } elseif ($args['type'] == 'input') {
            echo "<input type='text' name='" . esc_attr($name) . "' value='" . esc_attr($value) . "' style='width:" . esc_attr($size) . "px' />";
        } elseif ($args['type'] == 'checkbox') {
            if ($value == "1")
                echo "<input type='checkbox' checked='checked' name='" . esc_attr($name) . "' value='1' />";
            else
                echo "<input type='checkbox' name='" . esc_attr($name) . "' value='1' />";
        } elseif ($args['type'] == 'dropdown') {
            $options = $args['options'];
            ContactManagerForSendGridPlugin::print_select($options, $value, $name, $size);
        }
    }

    protected function add_setting($id, $name, $section, $desc, $default, $type = "input", $size = 300, $options = [])
    {
        add_settings_field(
            $id,
            $name,
            array(&$this, 'print_input'),
            ContactManagerForSendGridPlugin::$slug,
            $section,
            array(
                'field' => $id,
                'desc' => $desc,
                'type' => $type,
                'size' => $size,
                'default' => $default,
                'options' => $options
            )
        );

        register_setting(ContactManagerForSendGridPlugin::$slug, $id, array());
    }

    public function register_settings()
    {
        $api = new SendgridApiCMFS();
        add_settings_section('contacts-for-sendgrid', 'Contact Manager for SendGrid Options', null, ContactManagerForSendGridPlugin::$slug);
        //wp_enqueue_style('cmfs-admin-css', __DIR__ . 'css/admin.css', array(), null);

        $this->add_setting("cmfs-sendgrid-key", "SendGrid API Key", "contacts-for-sendgrid", "", "");
        // TODO Enable single send functionality?
        //$this->add_setting("cmfs-sendgrid-sender", "SendGrid Sender Id", "contacts-for-sendgrid", "", "", "dropdown", 300, $api->getAllSenders());
        $this->add_setting("cmfs-sendgrid-sandbox", "Enable Sandbox Mode (for testing)", "contacts-for-sendgrid", "", "", "checkbox");
        $this->add_setting("cmfs-sendgrid-register", "Enable adding new users to a list", "contacts-for-sendgrid", "", "", "checkbox");
        $this->add_setting("cmfs-sendgrid-register-list", "Add new users to this list", "contacts-for-sendgrid", "", "", "dropdown", 300, $api->getAllLists());
    }

    private function render_view($file, $data = [], $return = false)
    {
        if (!is_array($data))
            $data = (array)$data;

        extract($data);

        ob_start();
        $theme = "views/" . $file . ".php";
        include($theme); // PHP will be processed
        $output = ob_get_contents();
        @ob_end_clean();
        if ($return)
            return $output;
        print $output;
    }

    static function init_tables() {

    }

    static function remove_tables() {
    }

    public function render_admin_view()
    {
        $this->render_view('header');
        $this->render_view('admin', []);
    }

    public function register_menu() {
        add_submenu_page("options-general.php" /*parent_slug*/, "Contact Manager for SendGrid" /*page title*/, "Contacts for SendGrid" /*menu title*/, "edit_posts" /*capability*/,  ContactManagerForSendGridPlugin::$slug /*menu slug*/, array($this, "render_admin_view") /*function*/);
    }

    private function handle_new_user($user_id) {
        $addOnRegister = get_option('cmfs-sendgrid-register');
        $registerList = get_option('cmfs-sendgrid-register-list');
        if ($addOnRegister && $registerList) {
            if (apply_filters('cmfs-add-on-register', true, $user_id)) {
                $list = apply_filters('cmfs-register-list', $registerList, $user_id);
                $api = new SendgridApiCMFS();
                $user = get_userdata($user_id);
                $api->addToList($list, $user->user_email, $user->first_name, $user->last_name, get_user_meta($user_id, "billing_phone", true));
            }
        }
    }

    private function register_actions() {
        add_action("wpmem_post_register_data", function($post) {
            $this->handle_new_user($post['ID']);
        });
        add_action("register_new_user", function($user_id) {
            $this->handle_new_user($user_id);
        });
    }

    public function __construct()
    {
        register_activation_hook(__FILE__, 'ContactManagerForSendGrid\ContactManagerForSendGridPlugin::init_tables');
        register_uninstall_hook(__FILE__, 'ContactManagerForSendGrid\ContactManagerForSendGridPlugin::remove_tables');
        add_action('admin_init', array(&$this, 'register_settings'));
        add_action('admin_menu', array($this, 'register_menu'));

        $this->register_actions();
    }

}

new ContactManagerForSendGridPlugin();
