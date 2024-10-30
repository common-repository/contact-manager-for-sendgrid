
<form method="post" action="options.php">

    <?php
    settings_fields('contacts-for-sendgrid');
    do_settings_sections('contacts-for-sendgrid');

    submit_button();
    ?>

</form>
