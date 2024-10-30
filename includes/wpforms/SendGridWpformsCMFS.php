<?php

namespace ContactManagerForSendGrid;

class SendGridWpformsCMFS
{

    public function add_settings_section($sections, $form_data)
    {
        $sections['sendgrid'] = 'SendGrid';
        return $sections;
    }

    function render_section_content($instance)
    {
        $sendgrid = new \ContactManagerForSendGrid\SendgridApiCMFS();
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-sendgrid">';
        echo '<div class="wpforms-panel-content-section-title">SendGrid</div>';
        if (!$sendgrid->hasKey())
            echo '<h3>Missing SendGrid API Key. Set the key in "Settings->Contacts for SendGrid" in your admin dashboard</h3>';
        $lists = $sendgrid->getAllLists();
        wpforms_panel_field(
            'select',
            'settings',
            'sendgrid_list_id',
            $instance->form_data,
            'SendGrid List ID',
            array(
                'options' => $lists
            )
        );
        wpforms_panel_field(
            'select',
            'settings',
            'sendgrid_field_first_name',
            $instance->form_data,
            'Contact First Name',
            array(
                'field_map' => array('text', 'number', 'phone', 'hidden'),
                'placeholder' => '-- Select Field --',
            )
        );
        wpforms_panel_field(
            'select',
            'settings',
            'sendgrid_field_last_name',
            $instance->form_data,
            'Contact Last Name',
            array(
                'field_map' => array('text', 'name', 'hidden'),
                'placeholder' => '-- Select Field --',
            )
        );
        wpforms_panel_field(
            'select',
            'settings',
            'sendgrid_field_phone',
            $instance->form_data,
            'Contact Phone',
            array(
                'field_map' => array('text', 'phone', 'hidden'),
                'placeholder' => '-- Select Field --',
            )
        );
        wpforms_panel_field(
            'select',
            'settings',
            'sendgrid_field_email',
            $instance->form_data,
            'Email Address',
            array(
                'field_map' => array('text', 'email', 'hidden'),
                'placeholder' => '-- Select Field --',
            )
        );
        echo '</div>';
    }

    function send_to_sendgrid($fields, $entry, $form_data, $entry_id)
    {
        $list_id = esc_html($form_data['settings']['sendgrid_list_id'] ?? "");

        // Get email and first name
        $email_field_id = $form_data['settings']['sendgrid_field_email'] ?? "";
        $name_field_id = $form_data['settings']['sendgrid_field_first_name'] ?? "";
        $last_name_field_id = $form_data['settings']['sendgrid_field_last_name'] ?? "";
        $phone_field_id = $form_data['settings']['sendgrid_field_phone'] ?? "";

        if (!$email_field_id)
            return;

        $email = "";
        $name = "";
        $last_name = "";
        $phone = "";
        if (!empty($fields[$email_field_id]['value']))
            $email = $fields[$email_field_id]['value'];
        if (!empty($fields[$name_field_id]['first']))
            $name = $fields[$name_field_id]['first'];
        elseif (!empty($fields[$name_field_id]['value']))
            $name = $fields[$name_field_id]['value'];
        if (!empty($fields[$last_name_field_id]['last']))
            $last_name = $fields[$last_name_field_id]['last'];
        elseif (!empty($fields[$last_name_field_id]['value']))
            $last_name = $fields[$last_name_field_id]['value'];
        if (isset($fields[$phone_field_id]['value']))
            $phone = $fields[$phone_field_id]['value'];

        if (empty($email)) {
            return;
        }

        $sendgrid = new SendgridApiCMFS();
        $sendgrid->addToList($list_id, $email, $name, $last_name, $phone);
    }

    public function __construct()
    {
        add_filter('wpforms_builder_settings_sections', array($this, 'add_settings_section'), 20, 2);
        add_filter('wpforms_form_settings_panel_content', array($this, 'render_section_content'), 20);
        add_action('wpforms_process_complete', array($this, 'send_to_sendgrid'), 10, 4);
    }
}

new SendGridWpformsCMFS();
