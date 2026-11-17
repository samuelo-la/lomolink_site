import { site } from './site';

export const routes = {
	home: '/',
	about: '/about/',
	educationTraining: '/education-training/',
	studyOption: (slug: string) => `/study-options/${slug}/`,
	refugeeSupport: '/refugee-support/',
	recruitment: '/recruitment/',
	contact: '/contact/',
	thankYou: '/thank-you/',
	privacy: '/privacy/',
} as const;

export const mainNav = [
	{ href: routes.home, label: 'Home' },
	{ href: routes.about, label: 'About Us' },
	{ href: routes.educationTraining, label: 'Education & Training' },
	{ href: routes.refugeeSupport, label: 'Refugee Support' },
	{ href: routes.recruitment, label: 'Recruitment' },
] as const;

export const enquiryTopics = ['consultation', 'partnership', 'referral', 'candidate', 'employer'] as const;
export type EnquiryTopic = (typeof enquiryTopics)[number];

/** Link for enquiry buttons. Topics are only appended when the enquiry page is on this site. */
export const enquiryHref = (topic?: EnquiryTopic): string => {
	if (!topic || !site.enquiryUrl.startsWith('/')) return site.enquiryUrl;
	return `${site.enquiryUrl}?topic=${topic}`;
};
