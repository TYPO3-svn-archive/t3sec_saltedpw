No necessity to configure everything. Just install it!

Currently implemented:
 * FE authentication service that works with salted md5 hashed passwords
 * JS/PHP eval functions that allows creation of salted md5 hashed passwords in BE
 * forgot password functionality for sysext felogin
 * create user/edit user functionality in regards to password field for ext feuser_admin
 * forgot password functionality for ext feuser_admin


The provided template is exactely that one of extension feuser_admin,
but reduced by one single line which allowed an instant login by
transmitting credentials via GET.

Usage with extension feuser_admin:
to enable salted hashes for passwords, set following TypoScript
# plugin.feadmin.fe_users.parseValues.password = trim,saltedHash


Extension icon is retrieved from Mini set by Mark James
http://www.famfamfam.com/lab/icons/mini/
Distributed under GPL