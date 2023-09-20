# vip-login-limit-debug

This is a plug to be installed as either a regular plugin or mu-plugin, to be used to expose useful information and wp cli command examples when a login limit has been imposed on a VIP Go WordPress site.

It is intended as a temporary drop-in.

Once installed, visiting the login page with a querystring appended will expose some useful info in a notice, e.g.:-

`/wp-login.php?vip_login_debug&username=yourusername`

The plugin will use your remote IP address by default, but you can specify an IP address using the `ip` query paramater e.g. `&ip=<any valid IP address>`


