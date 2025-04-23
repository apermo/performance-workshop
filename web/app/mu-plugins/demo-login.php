<?php
/*
Plugin Name: Demo Login Credentials Display
Description: Displays demo login credentials and pre-populates the login form on wp-login.php.
Version: 1.0
Author: Christoph Daum
Author URI: https://christoph-daum.com
*/

// Display demo credentials above the login form
add_action( 'login_form', function () {
	echo '<div style="margin-bottom: 16px; padding: 10px; background: #f7f7f7; border: 1px solid #ddd;">
        <strong>Demo Login:</strong><br>
        Username: <code>admin</code><br>
        Password: <code>password</code>
    </div>';
} );

add_action( 'login_enqueue_scripts', function () {
	?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var userInput = document.getElementById('user_login');
            var passInput = document.getElementById('user_pass');
            var rememberMe = document.getElementById('rememberme');
            if (userInput && passInput) {
                userInput.value = 'admin';
                passInput.value = 'password';
            }
            if (rememberMe) {
                rememberMe.checked = true;
            }
        });
    </script>
	<?php
} );
