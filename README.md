# Cloudflare_DDNS_v4-PHP

Usage:

require_once 'Cloudflare_DDNS_v4.class.php';

$ddns = new Cloudflare_DDNS_v4('domain.com', 'email@domain.com', 'apikey');

$ddns->UpdateDNSRecord('www', '127.0.0.1');


eg.

Email: Email address associated with your account

API Key: API key generated on the "My Account" page

