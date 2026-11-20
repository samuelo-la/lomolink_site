<?php

declare(strict_types=1);

/*
 * Employer staffing enquiry (/recruitment/employers/). Emails the details to the company mailbox;
 * stores nothing. Shared security checks live in form-lib.php.
 */

require __DIR__ . '/form-lib.php';

$form = form_begin('/recruitment/employers/');

$organisation = form_text('organisation', 1, 150);
$name = form_text('name', 1, 100);
$email = form_email();
$phone = form_phone();
$role = form_text('role', 1, 150);
$location = form_text('location', 1, 100);
$shifts = form_text('shifts', 1, 200);
$requirements = form_text('requirements', 10, 2000, true);

$errors = [];
if ($organisation === null) {
	$errors['organisation'] = 'Please enter your organisation name (up to 150 characters).';
}
if ($name === null) {
	$errors['name'] = 'Please enter a contact name (up to 100 characters).';
}
if ($email === null) {
	$errors['email'] = 'Please enter a valid email address (up to 150 characters).';
}
if ($phone === null) {
	$errors['phone'] = FORM_PHONE_ERROR;
}
if ($role === null) {
	$errors['role'] = 'Please tell us the role you need to fill (up to 150 characters).';
}
if ($location === null) {
	$errors['location'] = 'Please enter the location (up to 100 characters).';
}
if ($shifts === null) {
	$errors['shifts'] = 'Please describe the shift pattern (up to 200 characters).';
}
if ($requirements === null) {
	$errors['requirements'] = 'Please describe the essential requirements (10 to 2,000 characters).';
}
if ($errors) {
	form_respond($form, 422, 'invalid', $errors);
}

form_rate_limit($form);

$body = "A new staffing enquiry was sent from the website.\n\n"
	. "Organisation: {$organisation}\n"
	. "Contact name: {$name}\n"
	. "Email: {$email}\n"
	. 'Phone: ' . ($phone === '' ? 'not given' : $phone) . "\n"
	. "Role: {$role}\n"
	. "Location: {$location}\n"
	. "Shift pattern: {$shifts}\n\n"
	. "Essential requirements:\n{$requirements}\n";

form_send($form, 'New staffing enquiry from the Lomolink Consulting website', $body, $email);
form_respond($form, 200, 'sent_employer');
