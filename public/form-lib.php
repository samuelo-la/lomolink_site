<?php

declare(strict_types=1);

/*
 * Shared security code for the website's form handlers: contact.php, candidate.php and employer.php.
 *
 * This file is only ever included. Direct requests are refused here and also blocked in .htaccess.
 * Nothing submitted is stored: submissions (and CVs) are emailed and then discarded. The only thing
 * written to disk is a short-lived, keyed hash of the visitor's IP address for rate limiting.
 * Never log or echo submitted data: only fixed messages and generic failure codes.
 */

if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
	http_response_code(404);
	exit;
}

const FORM_MAX_PER_HOUR = 5;
const FORM_WINDOW_SECONDS = 3600;
const FORM_CV_MAX_BYTES = 5 * 1024 * 1024;
const FORM_DOCX_MAX_UNCOMPRESSED = 50 * 1024 * 1024;

// Cloudflare's published address ranges (https://www.cloudflare.com/ips/, checked 19 November 2026).
// Used only when contact.config.php sets 'behind_cloudflare' => true.
const FORM_CLOUDFLARE_RANGES = [
	'173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18',
	'108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17',
	'162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
	'2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32',
	'2a06:98c0::/29', '2c0f:f248::/32',
];

const FORM_PHONE_ERROR = 'Please enter a valid phone number, or leave it blank.';

const FORM_MESSAGES = [
	'sent' => 'Thank you. Your message has been sent and we will reply by email.',
	'sent_candidate' => 'Thank you for registering your interest. We will be in touch by email if there is a suitable opportunity.',
	'sent_employer' => 'Thank you. We have received your staffing enquiry and will reply by email.',
	'invalid' => 'Please check the highlighted fields and try again.',
	'too_large' => 'Your submission was too large. Please upload a CV under 5 MB.',
	'rate' => 'You have sent several messages recently. Please try again later, or contact us by email or telephone.',
	'forbidden' => 'Sorry, your details could not be sent. Please use the form on our website.',
	'method' => 'Please use the form on our website.',
	'server' => 'Sorry, your details could not be sent. Please try again later, or contact us by email or telephone.',
];

const FORM_CV_ERRORS = [
	'type' => 'Please upload your CV as a PDF or Word (.docx) document.',
	'size' => 'Please upload a CV smaller than 5 MB.',
	'unsafe' => 'This file contains content we cannot accept. Please save your CV again as a standard PDF or Word document.',
	'invalid' => 'Your CV could not be uploaded. Please try again.',
];

/**
 * Starts handling a form submission: security headers, POST only, settings, same-site check, honeypot.
 *
 * @param string $formPage The form's page, used for the no-JavaScript error redirect.
 * @return array{json: bool, page: string, config: array<string, mixed>}
 */
function form_begin(string $formPage): array
{
	header('Cache-Control: no-store');
	header('X-Content-Type-Options: nosniff');

	$form = [
		'json' => str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json'),
		'page' => $formPage,
		'config' => [],
	];

	if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
		header('Allow: POST');
		form_respond($form, 405, 'method');
	}

	$configFile = __DIR__ . '/contact.config.php';
	if (!is_readable($configFile)) {
		form_fail($form, 500, 'server', 'config_missing');
	}
	$config = require $configFile;
	foreach (['site_host', 'recipient', 'sender', 'rate_limit_secret', 'rate_limit_dir'] as $key) {
		if (!is_array($config) || !is_string($config[$key] ?? null) || $config[$key] === '') {
			form_fail($form, 500, 'server', 'config_invalid');
		}
	}
	if (isset($config['behind_cloudflare']) && !is_bool($config['behind_cloudflare'])) {
		form_fail($form, 500, 'server', 'config_invalid');
	}
	$form['config'] = $config;

	$source = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
	$sourceHost = is_string($source) ? parse_url($source, PHP_URL_HOST) : null;
	if (!is_string($sourceHost) || strcasecmp($sourceHost, $config['site_host']) !== 0) {
		form_fail($form, 403, 'forbidden', 'origin_rejected');
	}

	// PHP discards every field when a request exceeds post_max_size.
	if ($_POST === [] && $_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
		form_respond($form, 413, 'too_large', ['cv' => FORM_CV_ERRORS['size']]);
	}

	$honeypot = $_POST['website'] ?? '';
	if (!is_string($honeypot) || trim($honeypot) !== '') {
		form_respond($form, 200, 'sent');
	}

	return $form;
}

/**
 * @param array{json: bool, page: string, config: array<string, mixed>} $form
 * @param array<string, string> $errors
 */
function form_respond(array $form, int $status, string $messageKey, array $errors = []): never
{
	if ($form['json']) {
		http_response_code($status);
		header('Content-Type: application/json; charset=utf-8');
		$body = ['ok' => $status < 400, 'message' => FORM_MESSAGES[$messageKey]];
		if ($errors) {
			$body['errors'] = $errors;
		}
		echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
		exit;
	}

	if ($status === 405) {
		http_response_code(405);
		header('Content-Type: text/plain; charset=utf-8');
		echo FORM_MESSAGES['method'];
		exit;
	}

	header($status < 400 ? 'Location: /thank-you/' : 'Location: ' . $form['page'] . '?error=1#form-error', true, 303);
	exit;
}

/** @param array{json: bool, page: string, config: array<string, mixed>} $form */
function form_fail(array $form, int $status, string $messageKey, string $logCode): never
{
	error_log('form: ' . $logCode);
	form_respond($form, $status, $messageKey);
}

/** Trimmed string with control characters removed, or null for non-string / invalid UTF-8 input. */
function form_clean(mixed $value, bool $multiline = false): ?string
{
	if (!is_string($value)) {
		return null;
	}
	$value = str_replace(["\r\n", "\r"], "\n", $value);
	$pattern = $multiline ? '/[\x00-\x08\x0B-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u';
	$value = preg_replace($pattern, '', $value);
	return $value === null ? null : trim($value);
}

/** A text field between $min and $max characters (use $min = 0 for optional fields), or null if invalid. */
function form_text(string $name, int $min, int $max, bool $multiline = false): ?string
{
	$value = form_clean($_POST[$name] ?? '', $multiline);
	if ($value === null) {
		return null;
	}
	$length = mb_strlen($value);
	return $length < $min || $length > $max ? null : $value;
}

function form_email(string $name = 'email'): ?string
{
	$value = form_text($name, 1, 150);
	return $value !== null && filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? $value : null;
}

/**
 * Optional phone number: '' when left empty, the number when valid, null when invalid.
 * Allows digits, spaces, +, brackets, dots and hyphens, with 7 to 15 digits in total.
 */
function form_phone(string $name = 'phone'): ?string
{
	$value = form_text($name, 0, 30);
	if ($value === null || $value === '') {
		return $value;
	}
	if (preg_match('/^\+?[0-9 ().-]+$/', $value) !== 1) {
		return null;
	}
	$digits = strlen((string) preg_replace('/\D/', '', $value));
	return $digits >= 7 && $digits <= 15 ? $value : null;
}

/**
 * One value from a fixed list, or null.
 *
 * @param array<string, string> $allowed value => label
 */
function form_choice(string $name, array $allowed): ?string
{
	$value = $_POST[$name] ?? null;
	return is_string($value) && isset($allowed[$value]) ? $value : null;
}

/**
 * At least $minimum values from a fixed list (checkbox group), or null.
 *
 * @param array<string, string> $allowed value => label
 * @return list<string>|null
 */
function form_choices(string $name, array $allowed, int $minimum): ?array
{
	$values = $_POST[$name] ?? [];
	if (!is_array($values)) {
		return null;
	}
	$picked = [];
	foreach ($values as $value) {
		if (!is_string($value) || !isset($allowed[$value])) {
			return null;
		}
		$picked[$value] = true;
	}
	return count($picked) >= $minimum ? array_keys($picked) : null;
}

/** True when $ip falls inside the CIDR range, for IPv4 and IPv6. */
function form_ip_in_range(string $ip, string $cidr): bool
{
	[$subnet, $bits] = explode('/', $cidr) + [1 => ''];
	$ipBin = @inet_pton($ip);
	$subnetBin = @inet_pton($subnet);
	if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
		return false;
	}
	$bits = (int) $bits;
	$whole = intdiv($bits, 8);
	if (strncmp($ipBin, $subnetBin, $whole) !== 0) {
		return false;
	}
	$rest = $bits % 8;
	if ($rest === 0) {
		return true;
	}
	$mask = (0xFF << (8 - $rest)) & 0xFF;
	return (ord($ipBin[$whole]) & $mask) === (ord($subnetBin[$whole]) & $mask);
}

/**
 * The visitor's address for rate limiting. Behind Cloudflare every request arrives from a Cloudflare
 * address, so the real one is read from CF-Connecting-IP, but only when the request really came through
 * Cloudflare (otherwise anyone could fake the header by contacting the server directly).
 *
 * @param array<string, mixed> $config
 */
function form_visitor_ip(array $config): string
{
	$remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
	if (($config['behind_cloudflare'] ?? false) !== true) {
		return $remote;
	}
	$forwarded = trim((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
	if ($forwarded === '' || filter_var($forwarded, FILTER_VALIDATE_IP) === false) {
		return $remote;
	}
	foreach (FORM_CLOUDFLARE_RANGES as $range) {
		if (form_ip_in_range($remote, $range)) {
			return $forwarded;
		}
	}
	return $remote;
}

/**
 * At most FORM_MAX_PER_HOUR submissions per visitor across all forms, counted by a keyed hash of the IP
 * address. Records older than the window are deleted on every submission.
 *
 * @param array{json: bool, page: string, config: array<string, mixed>} $form
 */
function form_rate_limit(array $form): void
{
	$config = $form['config'];
	$directory = rtrim($config['rate_limit_dir'], '/\\');
	if (!is_dir($directory) && !@mkdir($directory, 0700, true)) {
		form_fail($form, 500, 'server', 'ratelimit_dir_unavailable');
	}
	$visitorKey = hash_hmac('sha256', form_visitor_ip($config), $config['rate_limit_secret']);
	$handle = @fopen($directory . '/' . $visitorKey, 'c+');
	if ($handle === false || !flock($handle, LOCK_EX)) {
		form_fail($form, 500, 'server', 'ratelimit_unavailable');
	}
	$now = time();
	$recent = array_values(array_filter(
		array_map('intval', preg_split('/\s+/', (string) stream_get_contents($handle), -1, PREG_SPLIT_NO_EMPTY) ?: []),
		static fn (int $time): bool => $time > $now - FORM_WINDOW_SECONDS,
	));
	if (count($recent) >= FORM_MAX_PER_HOUR) {
		flock($handle, LOCK_UN);
		fclose($handle);
		form_respond($form, 429, 'rate');
	}
	$recent[] = $now;
	ftruncate($handle, 0);
	rewind($handle);
	fwrite($handle, implode("\n", $recent));
	fflush($handle);
	flock($handle, LOCK_UN);
	fclose($handle);

	foreach (glob($directory . '/*') ?: [] as $file) {
		if (is_file($file) && filemtime($file) < $now - FORM_WINDOW_SECONDS) {
			@unlink($file);
		}
	}
}

/**
 * Checks the optional CV upload. The type is decided from the file's contents, never its name.
 *
 * @return array{0: array{data: string, filename: string, mime: string}|null, 1: string|null} [file, error code]
 */
function form_cv(string $field): array
{
	$upload = $_FILES[$field] ?? null;
	if ($upload === null) {
		return [null, null];
	}
	// A multi-file upload (cv[]) arrives with arrays here: reject it.
	if (!is_array($upload) || !is_int($upload['error'] ?? null) || !is_string($upload['tmp_name'] ?? null)) {
		return [null, 'invalid'];
	}
	if ($upload['error'] === UPLOAD_ERR_NO_FILE) {
		return [null, null];
	}
	if ($upload['error'] === UPLOAD_ERR_INI_SIZE || $upload['error'] === UPLOAD_ERR_FORM_SIZE) {
		return [null, 'size'];
	}
	if ($upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
		return [null, 'invalid'];
	}
	$size = filesize($upload['tmp_name']);
	if ($size === false || $size === 0) {
		return [null, 'invalid'];
	}
	if ($size > FORM_CV_MAX_BYTES) {
		return [null, 'size'];
	}
	$data = file_get_contents($upload['tmp_name']);
	if ($data === false) {
		return [null, 'invalid'];
	}

	if (str_starts_with($data, '%PDF-')) {
		// Basic check for active content. The mailbox's virus scanning is the main malware defence.
		if (preg_match('#/(JavaScript|JS|Launch|EmbeddedFile|RichMedia)\b#', $data) === 1) {
			return [null, 'unsafe'];
		}
		return [['data' => $data, 'filename' => 'CV.pdf', 'mime' => 'application/pdf'], null];
	}

	if (str_starts_with($data, "PK\x03\x04")) {
		$verdict = form_docx_verdict($upload['tmp_name']);
		if ($verdict === 'ok') {
			return [[
				'data' => $data,
				'filename' => 'CV.docx',
				'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			], null];
		}
		return [null, $verdict];
	}

	return [null, 'type'];
}

/** 'ok' for a plain Word .docx; 'unsafe' for macros or embedded objects; 'type' for anything else. */
function form_docx_verdict(string $path): string
{
	if (!class_exists(ZipArchive::class)) {
		error_log('form: zip_extension_missing');
		return 'type';
	}
	$zip = new ZipArchive();
	if ($zip->open($path) !== true) {
		return 'type';
	}
	$count = $zip->numFiles;
	if ($count < 1 || $count > 1000) {
		$zip->close();
		return 'type';
	}
	$names = [];
	$uncompressed = 0;
	for ($i = 0; $i < $count; $i++) {
		$stat = $zip->statIndex($i);
		if ($stat === false) {
			$zip->close();
			return 'type';
		}
		$uncompressed += (int) $stat['size'];
		if ($uncompressed > FORM_DOCX_MAX_UNCOMPRESSED) {
			$zip->close();
			return 'unsafe';
		}
		if (preg_match('#(^|/)vbaProject\.bin$|^word/(embeddings|activeX)/#i', $stat['name']) === 1) {
			$zip->close();
			return 'unsafe';
		}
		$names[$stat['name']] = true;
	}
	$types = $zip->getFromName('[Content_Types].xml');
	$zip->close();

	if (!is_string($types) || !isset($names['word/document.xml'])) {
		return 'type';
	}
	if (stripos($types, 'macroEnabled') !== false) {
		return 'unsafe';
	}
	return stripos($types, 'wordprocessingml.document.main+xml') !== false ? 'ok' : 'type';
}

/** Keeps every line under the 998-byte mail limit without splitting a multibyte character. */
function form_limit_lines(string $text, int $maxBytes = 900): string
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

/**
 * Sends the submission to the configured mailbox. Visitor input appears only in the body and,
 * once validated, in Reply-To. An attachment (the CV) is added as a fixed file name.
 *
 * @param array{json: bool, page: string, config: array<string, mixed>} $form
 * @param array{data: string, filename: string, mime: string}|null $attachment
 */
function form_send(array $form, string $subject, string $body, string $replyTo, ?array $attachment = null): void
{
	$config = $form['config'];
	$headers = [
		'From' => $config['sender'],
		'Reply-To' => str_replace(["\r", "\n"], '', $replyTo),
		'MIME-Version' => '1.0',
	];
	$text = form_limit_lines($body);

	if ($attachment === null) {
		$headers['Content-Type'] = 'text/plain; charset=UTF-8';
		$headers['Content-Transfer-Encoding'] = '8bit';
		$message = $text;
	} else {
		$boundary = 'lomolink-' . bin2hex(random_bytes(12));
		$headers['Content-Type'] = 'multipart/mixed; boundary="' . $boundary . '"';
		$message = "This is a multi-part message in MIME format.\n\n"
			. "--{$boundary}\n"
			. "Content-Type: text/plain; charset=UTF-8\n"
			. "Content-Transfer-Encoding: 8bit\n\n"
			. $text . "\n\n"
			. "--{$boundary}\n"
			. "Content-Type: {$attachment['mime']}; name=\"{$attachment['filename']}\"\n"
			. "Content-Transfer-Encoding: base64\n"
			. "Content-Disposition: attachment; filename=\"{$attachment['filename']}\"\n\n"
			. rtrim(chunk_split(base64_encode($attachment['data']), 76, "\n")) . "\n"
			. "--{$boundary}--\n";
	}

	$envelope = filter_var($config['sender'], FILTER_VALIDATE_EMAIL) !== false ? '-f' . $config['sender'] : '';
	// @ keeps PHP's own warning out of the log; the generic code is logged instead.
	if (!@mail($config['recipient'], $subject, $message, $headers, $envelope)) {
		form_fail($form, 500, 'server', 'mail_failed');
	}
}
