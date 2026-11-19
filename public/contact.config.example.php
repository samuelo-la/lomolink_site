<?php

declare(strict_types=1);

/*
 * Example settings for contact.php.
 *
 * On the server, copy this file to contact.config.php (same folder as contact.php)
 * and replace every value. Never commit contact.config.php.
 */

return [
	// The site's host name, without https:// or www. Form posts from any other host are rejected.
	'site_host' => 'lomolinkconsulting.com',

	// Mailbox that receives enquiries (the public contact address shown on the site).
	'recipient' => 'peter@lomolinkconsulting.com',

	// Fixed sender address on the site's own domain (set up SPF, DKIM and DMARC for it).
	'sender' => 'website@lomolinkconsulting.com',

	// Long random string used to hash visitor IP addresses. Generate one, for example
	// with: php -r "echo bin2hex(random_bytes(32));"
	'rate_limit_secret' => 'replace-with-a-long-random-string',

	// Writable folder OUTSIDE the public web root for short-lived rate-limit records.
	'rate_limit_dir' => '/home/ACCOUNT/contact-rate-limit',

	// true when the domain is proxied through Cloudflare (orange cloud), so the rate limit counts real
	// visitors instead of Cloudflare's servers. Leave false for a domain that is not behind Cloudflare.
	'behind_cloudflare' => false,
];
