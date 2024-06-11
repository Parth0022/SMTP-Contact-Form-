<?php
/*
Plugin Name: Contact Form
Description: Integrates SMTP from Microsoft Outlook with a contact form using PHPMailer.
Version: 4.0
Author: Partha Santosh
*/

session_start();

require_once ABSPATH . WPINC . '/class-phpmailer.php';
require_once ABSPATH . WPINC . '/class-smtp.php';

add_shortcode('contact_form', 'contact_form_function');
add_action('admin_menu', 'outlook_smtp_integration_menu');

function outlook_smtp_integration_menu()
{
    add_menu_page('Outlook SMTP Integration', 'Outlook SMTP', 'manage_options', 'outlook-smtp-integration', 'outlook_smtp_integration_page');
}

function outlook_smtp_integration_page()
{
    ?>
    <div class="wrap">
        <h2>Outlook SMTP Integration Settings</h2>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Outlook Email Address</th>
                    <td><input type="text" name="outlook_email"
                            value="<?php echo isset($_POST['outlook_email']) ? $_POST['outlook_email'] : ''; ?>" required>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Outlook Password</th>
                    <td><input type="password" name="outlook_password"
                            value="<?php echo isset($_POST['outlook_password']) ? $_POST['outlook_password'] : ''; ?>"
                            required></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                    value="Save Changes"></p>
        </form>
    </div>
    <?php

    if (isset($_POST['submit'])) {
        $outlook_email = isset($_POST['outlook_email']) ? $_POST['outlook_email'] : '';
        $outlook_password = isset($_POST['outlook_password']) ? $_POST['outlook_password'] : '';

        $smtp_details = new stdClass();
        $smtp_details->email = $outlook_email;
        $smtp_details->pass = $outlook_password;
        $_SESSION['smtp_details'] = $smtp_details;
    }
}


function contact_form_function()
{
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<div class="outlook-contact-form">';
    echo '<h2>Contact Us</h2>';
    echo '<input type="hidden" name="action" value="process_contact_form">';
    echo '<input type="text" name="name" placeholder="Your Name" required>';
    echo '<input type="email" name="email" placeholder="Your Email" required>';
    echo '<textarea name="message" placeholder="Your Message" required></textarea>';
    echo '<input type="submit" name="submit" value="Submit">';
    echo '</div>';
    echo '</form>';
}

add_action('admin_post_process_contact_form', 'process_contact_form_data');

function process_contact_form_data()
{
    if (isset($_POST['submit'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);

        if (isset($_SESSION['smtp_details'])) {
            $smtp_details = $_SESSION['smtp_details'];
            $Email = $smtp_details->email;
            $Password = $smtp_details->pass;

            $outlook_email = $Email;
            $outlook_password = $Password;
            $outlook_host = 'smtp.office365.com';
            $outlook_port = 587;

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $outlook_host;
            $mail->Port = $outlook_port;
            $mail->SMTPAuth = true;
            $mail->Username = $outlook_email;
            $mail->Password = $outlook_password;
            $mail->SMTPSecure = 'tls';
           // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable SMTP debugging

            // Send test email
            $mail->setFrom($outlook_email, 'Company Name');
            $mail->addAddress($Email);
            $mail->Subject = $name;
            $mail->Body =   'Name: ' . $name . PHP_EOL .
                            'Email: ' . $email . PHP_EOL .
                            'Message: ' . $message . PHP_EOL;
            


            try {
                $mail->send();
                echo '<div class="updated"><p>Thank you for reaching out to us we will get back to you soon</p></div>';
            } catch (Exception $e) {
                echo '<div class="error"><p>Error: ' . $mail->ErrorInfo . '</p></div>';
            }
        } else {
            echo "SMTP details not set";
        }
    }
}

