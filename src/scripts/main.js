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

// Contact form: topic prefill, inline validation and fetch submission. Without JavaScript it posts normally.
const contactForm = document.querySelector('[data-contact-form]');
if (contactForm) upgradeContactForm(contactForm);

function upgradeContactForm(form) {
	const topicSentences = {
		consultation: 'I would like to book a consultation. ',
		partnership: 'I would like to discuss a partnership. ',
		referral: 'I would like to discuss a referral. ',
		candidate: 'I am interested in healthcare roles. ',
		employer: 'I would like to discuss our staffing needs. ',
	};
	const genericError = 'Sorry, your message could not be sent. Please try again, or contact us by email or telephone.';
	const rules = {
		name: (value) => {
			if (!value) return 'Please enter your name.';
			if (value.length > 100) return 'Please keep your name to 100 characters or fewer.';
			return '';
		},
		email: (value) => {
			if (!value) return 'Please enter your email address.';
			if (value.length > 150 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Please enter a valid email address, for example name@example.com.';
			return '';
		},
		message: (value) => {
			if (value.length < 10) return 'Please write a message of at least 10 characters.';
			if (value.length > 2000) return 'Please keep your message to 2,000 characters or fewer.';
			return '';
		},
	};

	const status = form.querySelector('[data-form-status]');
	const submit = form.querySelector('[data-submit]');
	const field = (name) => form.elements.namedItem(name);

	const topic = new URLSearchParams(window.location.search).get('topic');
	const message = field('message');
	if (topic && Object.hasOwn(topicSentences, topic) && message && !message.value) message.value = topicSentences[topic];

	const setStatus = (text, state) => {
		status.textContent = text;
		if (state) status.dataset.state = state;
		else delete status.dataset.state;
	};

	const showError = (name, text) => {
		const input = field(name);
		const error = form.querySelector(`[data-error-for="${name}"]`);
		if (!input || !error) return;
		const hint = name === 'message' ? 'contact-message-hint' : '';
		error.textContent = text;
		error.classList.toggle('hidden', !text);
		input.setAttribute('aria-invalid', text ? 'true' : 'false');
		input.setAttribute('aria-describedby', [hint, text ? error.id : ''].filter(Boolean).join(' '));
		if (!input.getAttribute('aria-describedby')) input.removeAttribute('aria-describedby');
	};

	const validate = () => {
		let firstInvalid = null;
		for (const [name, rule] of Object.entries(rules)) {
			const text = rule(field(name).value.trim());
			showError(name, text);
			if (text && !firstInvalid) firstInvalid = field(name);
		}
		return firstInvalid;
	};

	form.noValidate = true;

	Object.keys(rules).forEach((name) => {
		field(name).addEventListener('input', () => {
			if (field(name).getAttribute('aria-invalid') === 'true') showError(name, rules[name](field(name).value.trim()));
		});
	});

	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		const firstInvalid = validate();
		if (firstInvalid) {
			setStatus('Please check the highlighted fields.', 'error');
			firstInvalid.focus();
			return;
		}

		submit.disabled = true;
		setStatus('Sending your message…');
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
				setStatus(data.message || 'Thank you. Your message has been sent and we will reply by email.', 'success');
				return;
			}
			if (data.errors) Object.entries(data.errors).forEach(([name, text]) => showError(name, String(text)));
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
