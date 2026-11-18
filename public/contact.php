<?php

declare(strict_types=1);

/*
 * Contact form handler for the Lomolink Consulting website.
 *
 * Emails the enquiry to the company mailbox and stores nothing except a short-lived,
 * keyed hash of the visitor's IP address used for rate limiting.
 * Settings come from contact.config.php (see contact.config.example.php).
 * Never log or echo submitted data: only fixed messages and generic failure codes.
 */

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

const MAX_PER_HOUR = 5;
const WINDOW_SECONDS = 3600;

const MESSAGES = [
	'sent' => 'Thank you. Your message has been sent and we will reply by email.',
	'invalid' => 'Please check the highlighted fields and try again.',
	'rate' => 'You have sent several messages recently. Please try again later, or contact us by email or telephone.',
	'forbidden' => 'Sorry, your message could not be sent. Please send it from the contact page on our website.',
	'method' => 'Please use the contact form on our website.',
	'server' => 'Sorry, your message could not be sent. Please try again later, or contact us by email or telephone.',
];

const FIELD_ERRORS = [
	'name' => 'Please enter your name (up to 100 characters).',
	'email' => 'Please enter a valid email address (up to 150 characters).',
	'message' => 'Please write a message between 10 and 2,000 characters.',
];

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

/** @param array<string, string> $errors */
function respond(bool $wantsJson, int $status, string $messageKey, array $errors = []): never
{
	if ($wantsJson) {
		http_response_code($status);
		header('Content-Type: application/json; charset=utf-8');
		$body = ['ok' => $status < 400, 'message' => MESSAGES[$messageKey]];
		if ($errors) {
			$body['errors'] = $errors;
		}
		echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
		exit;
	}

	if ($status === 405) {
		http_response_code(405);
		header('Content-Type: text/plain; charset=utf-8');
		echo MESSAGES['method'];
		exit;
	}

	header($status < 400 ? 'Location: /thank-you/' : 'Location: /contact/?error=1#form-error', true, 303);
	exit;
}

function fail(bool $wantsJson, int $status, string $messageKey, string $logCode): never
{
	error_log('contact.php: ' . $logCode);
	respond($wantsJson, $status, $messageKey);
}

/** Returns a trimmed string with control characters removed, or null for non-string / invalid UTF-8 input. */
function clean(mixed $value, bool $multiline = false): ?string
{
	if (!is_string($value)) {
		return null;
	}
	$value = str_replace(["\r\n", "\r"], "\n", $value);
	$pattern = $multiline ? '/[\x00-\x08\x0B-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u';
	$value = preg_replace($pattern, '', $value);
	return $value === null ? null : trim($value);
}

/** Keeps every line under the 998-byte mail limit without splitting a multibyte character. */
function limitLineLength(string $text, int $maxBytes = 900): string
{
	$lines = [];
	foreach (explode("\n", $text) as $line) {
		while (strlen($line) > $maxBytes) {
			$chunk = mb_strcut($line, 0, $maxBytes, 'UTF-8');
			$lines[] = $chunk;
			$line = substr($line, strlen($chunk));
		}
		$lines[] = $line;
	}
	return implode("\n", $lines);
}

// 1. POST only.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
	header('Allow: POST');
	respond($wantsJson, 405, 'method');
}

// 2. Server-side settings (never committed).
$configFile = __DIR__ . '/contact.config.php';
if (!is_readable($configFile)) {
	fail($wantsJson, 500, 'server', 'config_missing');
}
$config = require $configFile;
foreach (['site_host', 'recipient', 'sender', 'rate_limit_secret', 'rate_limit_dir'] as $key) {
	if (!is_array($config) || !is_string($config[$key] ?? null) || $config[$key] === '') {
		fail($wantsJson, 500, 'server', 'config_invalid');
	}
}

// 3. The request must come from this site.
$source = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$sourceHost = is_string($source) ? parse_url($source, PHP_URL_HOST) : null;
if (!is_string($sourceHost) || strcasecmp($sourceHost, $config['site_host']) !== 0) {
	fail($wantsJson, 403, 'forbidden', 'origin_rejected');
}

// 4. Honeypot: pretend success and send nothing.
$honeypot = $_POST['website'] ?? '';
if (!is_string($honeypot) || trim($honeypot) !== '') {
	respond($wantsJson, 200, 'sent');
}

// 5. Validate.
$name = clean($_POST['name'] ?? null);
$email = clean($_POST['email'] ?? null);
$message = clean($_POST['message'] ?? null, true);

$errors = [];
if ($name === null || $name === '' || mb_strlen($name) > 100) {
	$errors['name'] = FIELD_ERRORS['name'];
}
if ($email === null || $email === '' || mb_strlen($email) > 150 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
	$errors['email'] = FIELD_ERRORS['email'];
}
if ($message === null || mb_strlen($message) < 10 || mb_strlen($message) > 2000) {
	$errors['message'] = FIELD_ERRORS['message'];
}
if ($errors) {
	respond($wantsJson, 422, 'invalid', $errors);
}

// 6. Rate limit: at most MAX_PER_HOUR per visitor, counted by a keyed hash of the IP address.
$directory = rtrim($config['rate_limit_dir'], '/\\');
if (!is_dir($directory) && !@mkdir($directory, 0700, true)) {
	fail($wantsJson, 500, 'server', 'ratelimit_dir_unavailable');
}
$visitorKey = hash_hmac('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? ''), $config['rate_limit_secret']);
$handle = @fopen($directory . '/' . $visitorKey, 'c+');
if ($handle === false || !flock($handle, LOCK_EX)) {
	fail($wantsJson, 500, 'server', 'ratelimit_unavailable');
}
$now = time();
$recent = array_values(array_filter(
	array_map('intval', preg_split('/\s+/', (string) stream_get_contents($handle), -1, PREG_SPLIT_NO_EMPTY) ?: []),
	static fn (int $time): bool => $time > $now - WINDOW_SECONDS,
));
if (count($recent) >= MAX_PER_HOUR) {
	flock($handle, LOCK_UN);
	fclose($handle);
	respond($wantsJson, 429, 'rate');
}
$recent[] = $now;
ftruncate($handle, 0);
rewind($handle);
fwrite($handle, implode("\n", $recent));
fflush($handle);
flock($handle, LOCK_UN);
fclose($handle);

// Delete records older than the window (the folder only ever holds a handful of tiny files).
foreach (glob($directory . '/*') ?: [] as $file) {
	if (is_file($file) && filemtime($file) < $now - WINDOW_SECONDS) {
		@unlink($file);
	}
}

// 7. Send. Visitor input appears only in the body and (once validated) in Reply-To.
$replyTo = str_replace(["\r", "\n"], '', $email);
$subject = 'New enquiry from the Lomolink Consulting website';
$body = "A new enquiry was sent from the contact form.\n\n"
	. "Name: {$name}\n"
	. "Email: {$replyTo}\n\n"
	. "Message:\n{$message}\n";
$headers = [
	'From' => $config['sender'],
	'Reply-To' => $replyTo,
	'MIME-Version' => '1.0',
	'Content-Type' => 'text/plain; charset=UTF-8',
	'Content-Transfer-Encoding' => '8bit',
];
$envelope = filter_var($config['sender'], FILTER_VALIDATE_EMAIL) !== false ? '-f' . $config['sender'] : '';

// @ keeps PHP's own warning out of the log; the generic code below is logged instead.
if (!@mail($config['recipient'], $subject, limitLineLength($body), $headers, $envelope)) {
	fail($wantsJson, 500, 'server', 'mail_failed');
}

respond($wantsJson, 200, 'sent');
