import type { IconName } from '../components/icons/paths';
import data from './courses.json';

export interface Course {
	slug: string;
	title: string;
	category: string;
	icon: IconName;
	summary: string;
	description: string;
	qualifications: string[];
}

export const courses = data as Course[];
