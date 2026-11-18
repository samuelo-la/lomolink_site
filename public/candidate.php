<?php

declare(strict_types=1);

/*
 * Candidate registration (/recruitment/register/). Emails the details, with the optional CV attached,
 * to the company mailbox. Nothing is stored; the uploaded file is discarded when the request ends.
 * Shared security checks, including the CV inspection, live in form-lib.php.
 */

require __DIR__ . '/form-lib.php';

const CANDIDATE_ROLES = [
	'care-assistant' => 'Care assistant',
	'support-worker' => 'Support worker',
	'domiciliary-care-worker' => 'Domiciliary care worker',
	'other' => 'Other (see message)',
];
const CANDIDATE_AVAILABILITY = [
	'weekdays' => 'Weekdays',
	'weekends' => 'Weekends',
	'days' => 'Day shifts',
	'nights' => 'Night shifts',
];
const CANDIDATE_DRIVING = [
	'licence-car' => 'Full UK driving licence and access to a car',
	'licence-no-car' => 'Full UK driving licence, no car',
	'no-licence' => 'No driving licence',
];

$form = form_begin('/recruitment/register/');

$name = form_text('name', 1, 100);
$email = form_email();
$role = form_choice('role', CANDIDATE_ROLES);
$location = form_text('location', 1, 100);
$availability = form_choices('availability', CANDIDATE_AVAILABILITY, 1);
$driving = form_choice('driving', CANDIDATE_DRIVING);
$message = form_text('message', 0, 2000, true);
$consent = ($_POST['consent'] ?? null) === 'yes';
[$cv, $cvError] = form_cv('cv');

$errors = [];
if ($name === null) {
	$errors['name'] = 'Please enter your name (up to 100 characters).';
}
if ($email === null) {
	$errors['email'] = 'Please enter a valid email address (up to 150 characters).';
}
if ($role === null) {
	$errors['role'] = 'Please choose your preferred role.';
}
if ($location === null) {
	$errors['location'] = 'Please enter your town or area (up to 100 characters).';
}
if ($availability === null) {
	$errors['availability'] = 'Please choose at least one option for your availability.';
}
if ($driving === null) {
	$errors['driving'] = 'Please tell us your driving status.';
}
if ($message === null) {
	$errors['message'] = 'Please keep your message to 2,000 characters or fewer.';
}
if ($cvError !== null) {
	$errors['cv'] = FORM_CV_ERRORS[$cvError];
}
if (!$consent) {
	$errors['consent'] = 'Please confirm that we may share your details with employers.';
}
if ($errors) {
	form_respond($form, 422, 'invalid', $errors);
}

form_rate_limit($form);

$availabilityText = implode(', ', array_map(static fn (string $key): string => CANDIDATE_AVAILABILITY[$key], $availability));
$body = "A new candidate registration was sent from the website.\n\n"
	. "Name: {$name}\n"
	. "Email: {$email}\n"
	. 'Preferred role: ' . CANDIDATE_ROLES[$role] . "\n"
	. "Town or area: {$location}\n"
	. "Availability: {$availabilityText}\n"
	. 'Driving: ' . CANDIDATE_DRIVING[$driving] . "\n"
	. 'CV: ' . ($cv === null ? 'not provided' : 'attached (' . $cv['filename'] . ')') . "\n"
	. "Consent to share details and CV with employers about suitable roles: yes\n\n"
	. "Anything else:\n" . ($message === '' ? '(nothing added)' : $message) . "\n";

form_send($form, 'New candidate registration from the Lomolink Consulting website', $body, $email, $cv);
form_respond($form, 200, 'sent_candidate');
