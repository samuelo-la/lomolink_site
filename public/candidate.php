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
	'other' => 'Other',
	'open' => 'Open to opportunities',
];
const CANDIDATE_EXPERIENCE = [
	'new' => 'New to care',
	'experienced' => 'Has care experience',
];
const CANDIDATE_HOURS = [
	'full-time' => 'Full-time',
	'part-time' => 'Part-time',
	'either' => 'Full-time or part-time',
];
const CANDIDATE_SHIFTS = [
	'days' => 'Day shifts',
	'nights' => 'Night shifts',
	'weekends' => 'Weekends',
	'flexible' => 'Flexible',
];
const CANDIDATE_YES_NO = ['yes' => 'Yes', 'no' => 'No'];

/** Optional start date: '' when blank, 'YYYY-MM-DD' when valid (today to two years ahead), null when invalid. */
function candidate_start_date(): ?string
{
	$value = form_text('start', 0, 10);
	if ($value === null || $value === '') {
		return $value;
	}
	$date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('Europe/London'));
	if ($date === false || $date->format('Y-m-d') !== $value) {
		return null;
	}
	// One day of leeway for visitors in other time zones.
	$today = new DateTimeImmutable('today', new DateTimeZone('Europe/London'));
	if ($date < $today->modify('-1 day') || $date > $today->modify('+2 years')) {
		return null;
	}
	return $value;
}

$form = form_begin('/recruitment/register/');

$name = form_text('name', 1, 100);
$email = form_email();
$phone = form_phone();
$location = form_text('location', 1, 100);
$travel = form_text('travel', 0, 200);
$role = form_choice('role', CANDIDATE_ROLES);
$experience = form_choice('experience', CANDIDATE_EXPERIENCE);
$experienceDetails = form_text('experience_details', 0, 1000, true);
$hours = form_choice('hours', CANDIDATE_HOURS);
$shifts = form_choices('shifts', CANDIDATE_SHIFTS, 1);
$start = candidate_start_date();
$consent = ($_POST['consent'] ?? null) === 'yes';
[$cv, $cvError] = form_cv('cv');

// Driving questions apply only to domiciliary care; answers for other roles are ignored.
$isDomiciliary = $role === 'domiciliary-care-worker';
$licence = $isDomiciliary ? form_choice('licence', CANDIDATE_YES_NO) : null;
$car = $isDomiciliary ? form_choice('car', CANDIDATE_YES_NO) : null;

$errors = [];
if ($name === null) {
	$errors['name'] = 'Please enter your full name (up to 100 characters).';
}
if ($email === null) {
	$errors['email'] = 'Please enter a valid email address (up to 150 characters).';
}
if ($phone === null) {
	$errors['phone'] = FORM_PHONE_ERROR;
}
if ($location === null) {
	$errors['location'] = 'Please enter your town or postcode (up to 100 characters).';
}
if ($travel === null) {
	$errors['travel'] = 'Please keep this to 200 characters or fewer.';
}
if ($role === null) {
	$errors['role'] = 'Please choose your preferred role.';
}
if ($isDomiciliary && $licence === null) {
	$errors['licence'] = 'Please tell us whether you have a full UK driving licence.';
}
if ($isDomiciliary && $car === null) {
	$errors['car'] = 'Please tell us whether you have access to a car for work.';
}
if ($experience === null) {
	$errors['experience'] = 'Please tell us whether you are new to care or have care experience.';
}
if ($experienceDetails === null) {
	$errors['experience_details'] = 'Please keep this to 1,000 characters or fewer.';
}
if ($hours === null) {
	$errors['hours'] = 'Please choose full-time, part-time or either.';
}
if ($shifts === null) {
	$errors['shifts'] = 'Please choose at least one shift option.';
}
if ($start === null) {
	$errors['start'] = 'Please enter a date from today onwards, or leave it blank.';
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

$shiftsText = implode(', ', array_map(static fn (string $key): string => CANDIDATE_SHIFTS[$key], $shifts));
$startText = $start === '' ? 'as soon as possible' : DateTimeImmutable::createFromFormat('!Y-m-d', $start)->format('j F Y');
$body = "A new candidate registration was sent from the website.\n\n"
	. "Name: {$name}\n"
	. "Email: {$email}\n"
	. 'Phone: ' . ($phone === '' ? 'not given' : $phone) . "\n"
	. "Town or postcode: {$location}\n"
	. 'Areas they can travel to: ' . ($travel === '' ? 'not given' : $travel) . "\n\n"
	. 'Preferred role: ' . CANDIDATE_ROLES[$role] . "\n"
	. ($isDomiciliary
		? 'Full UK driving licence: ' . CANDIDATE_YES_NO[$licence] . "\n" . 'Access to a car for work: ' . CANDIDATE_YES_NO[$car] . "\n"
		: '')
	. 'Care experience: ' . CANDIDATE_EXPERIENCE[$experience] . "\n"
	. 'Hours: ' . CANDIDATE_HOURS[$hours] . "\n"
	. "Preferred shifts: {$shiftsText}\n"
	. "Earliest start date: {$startText}\n"
	. 'CV: ' . ($cv === null ? 'not provided' : 'attached (' . $cv['filename'] . ')') . "\n"
	. "Consent to share details and CV with employers about suitable roles: yes\n\n"
	. "Experience and relevant training:\n" . ($experienceDetails === '' ? '(nothing added)' : $experienceDetails) . "\n";

form_send($form, 'New candidate registration from the Lomolink Consulting website', $body, $email, $cv);
form_respond($form, 200, 'sent_candidate');
