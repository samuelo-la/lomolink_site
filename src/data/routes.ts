import { site } from './site';

export const routes = {
	home: '/',
	about: '/about/',
	educationTraining: '/education-training/',
	/** Each course area has a details section on the Education & Training page. */
	courseArea: (slug: string) => `/education-training/#${slug}`,
	refugeeSupport: '/refugee-education-support/',
	recruitment: '/recruitment/',
	recruitmentRegister: '/recruitment/register/',
	recruitmentEmployers: '/recruitment/employers/',
	contact: '/contact/',
	thankYou: '/thank-you/',
	privacy: '/privacy/',
} as const;

export const mainNav = [
	{ href: routes.home, label: 'Home' },
	{ href: routes.about, label: 'About Us' },
	{ href: routes.educationTraining, label: 'Education & Training' },
	{ href: routes.refugeeSupport, label: 'Refugee Education Support' },
	{ href: routes.recruitment, label: 'Recruitment' },
] as const;

export const enquiryTopics = ['consultation', 'partnership', 'referral', 'candidate', 'employer'] as const;
export type EnquiryTopic = (typeof enquiryTopics)[number];

/** Link for enquiry buttons. Topics are only appended when the enquiry page is on this site. */
export const enquiryHref = (topic?: EnquiryTopic): string => {
	if (!topic || !site.enquiryUrl.startsWith('/')) return site.enquiryUrl;
	return `${site.enquiryUrl}?topic=${topic}`;
};
