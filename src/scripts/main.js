const header = document.querySelector('[data-navbar]');
const menu = document.querySelector('[data-mobile-menu]');
const summary = document.querySelector('[data-menu-summary]');

const updateScrollState = () => {
	if (header) header.toggleAttribute('data-scrolled', window.scrollY > 8);
};

const closeMenu = () => {
	if (!menu || !summary || !menu.open) return;
	menu.open = false;
	summary.focus();
};

window.addEventListener('scroll', updateScrollState, { passive: true });
updateScrollState();

menu?.querySelectorAll('[data-menu-link]').forEach((link) => link.addEventListener('click', closeMenu));
document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') closeMenu();
});

// Forms: inline validation and fetch submission. Without JavaScript they post normally and the server checks everything.
document.querySelectorAll('[data-enquiry-form]').forEach(upgradeForm);

function upgradeForm(form) {
	const genericError = 'Sorry, your details could not be sent. Please try again, or contact us by email or telephone.';
	const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	const status = form.querySelector('[data-form-status]');
	const submit = form.querySelector('[data-submit]');
	const fields = [...form.querySelectorAll('[data-field]')];
	const fieldByName = (name) => fields.find((field) => field.dataset.field === name);

	// ?topic= prefills the message, but only with a sentence from the fixed list the page provides.
	if (form.dataset.topics) {
		let topicSentences = {};
		try {
			topicSentences = JSON.parse(form.dataset.topics);
		} catch {}
		const topic = new URLSearchParams(window.location.search).get('topic');
		const message = fieldByName('message');
		if (topic && Object.hasOwn(topicSentences, topic) && typeof topicSentences[topic] === 'string' && message && !message.value) {
			message.value = topicSentences[topic];
		}
	}

	// Keep each field's hint in aria-describedby; the error id is added only while an error shows.
	fields.forEach((field) => {
		field.dataset.baseDescribedby = field.getAttribute('aria-describedby') ?? '';
	});

	const isValid = (field) => {
		if (field.tagName === 'FIELDSET') return field.querySelectorAll('input:checked').length >= Number(field.dataset.min || 1);
		if (field.type === 'checkbox') return !field.required || field.checked;
		if (field.type === 'file') {
			const file = field.files?.[0];
			if (!file) return !field.required;
			return /\.(pdf|docx)$/i.test(file.name) && file.size > 0 && file.size <= Number(field.dataset.maxBytes);
		}
		const value = field.value.trim();
		if (!value) return !field.required;
		if (field.maxLength > 0 && value.length > field.maxLength) return false;
		if (field.minLength > 0 && value.length < field.minLength) return false;
		if (field.type === 'email' && !emailPattern.test(value)) return false;
		if (field.type === 'tel') {
			const digits = value.replace(/\D/g, '').length;
			if (!/^\+?[0-9 ().-]+$/.test(value) || digits < 7 || digits > 15) return false;
		}
		return true;
	};

	const showError = (field, text) => {
		const error = form.querySelector(`[data-error-for="${field.dataset.field}"]`);
		if (!error) return;
		error.textContent = text;
		error.classList.toggle('hidden', !text);
		const targets = field.tagName === 'FIELDSET' ? field.querySelectorAll('input') : [field];
		targets.forEach((input) => input.setAttribute('aria-invalid', text ? 'true' : 'false'));
		const describedBy = [field.dataset.baseDescribedby, text ? error.id : ''].filter(Boolean).join(' ');
		if (describedBy) field.setAttribute('aria-describedby', describedBy);
		else field.removeAttribute('aria-describedby');
	};

	const check = (field) => {
		const valid = isValid(field);
		showError(field, valid ? '' : field.dataset.errorMessage);
		return valid;
	};

	const setStatus = (text, state) => {
		status.textContent = text;
		if (state) status.dataset.state = state;
		else delete status.dataset.state;
	};

	form.noValidate = true;

	fields.forEach((field) => {
		const recheck = () => {
			const invalidNow = field.tagName === 'FIELDSET' ? field.querySelector('[aria-invalid="true"]') : field.getAttribute('aria-invalid') === 'true';
			if (invalidNow) check(field);
		};
		field.addEventListener('input', recheck);
		field.addEventListener('change', field.type === 'file' ? () => check(field) : recheck);
	});

	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		const invalid = fields.filter((field) => !check(field));
		if (invalid.length) {
			setStatus('Please check the highlighted fields.', 'error');
			const first = invalid[0];
			(first.tagName === 'FIELDSET' ? first.querySelector('input') : first).focus();
			return;
		}

		submit.disabled = true;
		setStatus('Sending…');
		try {
			const response = await fetch(form.action, {
				method: 'POST',
				body: new FormData(form),
				headers: { Accept: 'application/json' },
				credentials: 'same-origin',
			});
			const data = await response.json().catch(() => ({}));
			if (response.ok && data.ok) {
				form.reset();
				setStatus(typeof data.message === 'string' ? data.message : 'Thank you. Your details have been sent.', 'success');
				return;
			}
			if (data.errors && typeof data.errors === 'object') {
				Object.entries(data.errors).forEach(([name, text]) => {
					const field = fieldByName(name);
					if (field) showError(field, String(text));
				});
			}
			setStatus(typeof data.message === 'string' ? data.message : genericError, 'error');
		} catch {
			setStatus(genericError, 'error');
		} finally {
			submit.disabled = false;
		}
	});
}

// Scroll reveal: only elements that start below the fold are hidden, and only when motion is allowed.
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if ('IntersectionObserver' in window && !reduceMotion) {
	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				entry.target.classList.remove('reveal-pending');
				observer.unobserve(entry.target);
			});
		},
		{ rootMargin: '0px 0px -8% 0px' },
	);

	document.querySelectorAll('[data-reveal]').forEach((element) => {
		if (element.getBoundingClientRect().top < window.innerHeight) return;
		element.classList.add('reveal-pending');
		observer.observe(element);
	});
}
