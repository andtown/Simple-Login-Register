## WordPress Simple Login Register Plugin

**Brief Description:**

This is log in and register plugin, this plugin works by using a shortcode, at the moment it only renders shortcode that is coming through post content, placing the shortcode into text widget will give nothing, neither will give something using do_shortcode function. Once the shortcode has been rendered it would ask the user to enter an email and if the email exists it would then ask the user for the password if the email doesn't exist it would take the user to the registration form. This plugin doesn't store any registration data to the database which also means that to validate users who are trying to enter the system this plugin will pass the data (email, password) through a WordPress filter and will receive a boolean value in return.

**Usage:**

[simple_login_register]

below are the email and password that can be used for testing out this plugin:

email: test@test.com
password: test