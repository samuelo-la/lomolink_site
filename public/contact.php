<?php

declare(strict_types=1);

/*
 * General contact form (/contact/). Emails the enquiry to the company mailbox; stores nothing.
 * Shared security checks live in form-lib.php.
 */

require __DIR__ . '/form-lib.php';

$form = form_begin('/contact/');

$name = form_text('name', 1, 100);
$email = form_email();
$message = form_text('message', 10, 2000, true);

$errors = [];
if ($name === null) {
	$errors['name'] = 'Please enter your name (up to 100 characters).';
}
if ($email === null) {
	$errors['email'] = 'Please enter a valid email address (up to 150 characters).';
}
if ($message === null) {
	$errors['message'] = 'Please write a message between 10 and 2,000 characters.';
}
if ($errors) {
	form_respond($form, 422, 'invalid', $errors);
}

form_rate_limit($form);

$body = "A new enquiry was sent from the contact form.\n\n"
	. "Name: {$name}\n"
	. "Email: {$email}\n\n"
	. "Message:\n{$message}\n";

form_send($form, 'New enquiry from the Lomolink Consulting website', $body, $email);
form_respond($form, 200, 'sent');
