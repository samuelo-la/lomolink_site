import { courses } from './courses';
import { site } from './site';

export const routes = {
	home: '/',
	about: '/about/',
	educationTraining: '/education-training/',
	/** Each course area has a details section on the Education & Training page. */
	courseArea: (slug: string) => `/education-training/#${slug}`,
	refugeeSupport: '/refugee-education-support/',
	studentRecruitment: '/student-recruitment/',
	recruitment: '/recruitment/',
	recruitmentRegister: '/recruitment/register/',
	recruitmentEmployers: '/recruitment/employers/',
	contact: '/contact/',
	thankYou: '/thank-you/',
	privacy: '/privacy/',
} as const;

export interface NavLink {
	href: string;
	label: string;
}
export interface NavGroup {
	label: string;
	children: NavLink[];
}
export type NavItem = NavLink | NavGroup;

export const isNavGroup = (item: NavItem): item is NavGroup => 'children' in item;

export const mainNav: NavItem[] = [
	{ href: routes.home, label: 'Home' },
	{ href: routes.about, label: 'About Us' },
	{ href: routes.educationTraining, label: 'Education & Training' },
	{ href: routes.refugeeSupport, label: 'Refugee Education Support' },
	{
		label: 'Recruitment',
		children: [
			{ href: routes.studentRecruitment, label: 'Student Recruitment' },
			{ href: routes.recruitment, label: 'Care Recruitment' },
		],
	},
];

/** Every menu link in order, with groups flattened (mobile menu, footer). */
export const navLinks: NavLink[] = mainNav.flatMap((item) => (isNavGroup(item) ? item.children : [item]));

const generalTopics = {
	consultation: 'I would like to request a consultation. ',
	study: 'I would like to discuss my study options. ',
	guidance: 'I would like some personalised guidance about returning to education. ',
	'refugee-education': 'I would like to ask about refugee education support. ',
	partnership: 'I would like to discuss a partnership. ',
	referral: 'I would like to discuss a referral. ',
	candidate: 'I am interested in health and social care roles. ',
	employer: 'I would like to discuss our staffing needs. ',
} as const;
export type EnquiryTopic = keyof typeof generalTopics;

/** Topic value for a course area's "Enquire about this area" button. */
const courseTopic = (slug: string) => `course-${slug}`;

/**
 * Every allowed ?topic= value and the sentence it prefills in the contact form's message box.
 * The contact form receives this list; any other value is ignored.
 */
export const topicSentences: Record<string, string> = {
	...generalTopics,
	...Object.fromEntries(courses.map((course) => [courseTopic(course.slug), `I would like advice about ${course.title}. `])),
};

const withTopic = (topic: string): string => (site.enquiryUrl.startsWith('/') ? `${site.enquiryUrl}?topic=${topic}` : site.enquiryUrl);

/** Link for enquiry buttons. Topics are only appended when the enquiry page is on this site. */
export const enquiryHref = (topic?: EnquiryTopic): string => (topic ? withTopic(topic) : site.enquiryUrl);

/** Enquiry link that prefills the message with the course area's name. */
export const courseEnquiryHref = (slug: string): string => withTopic(courseTopic(slug));
