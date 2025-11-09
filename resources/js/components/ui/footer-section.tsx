import React from 'react';
import type { ComponentProps, ReactNode } from 'react';
import { motion, useReducedMotion } from 'framer-motion';
import { FacebookIcon, InstagramIcon, TwitterIcon, YoutubeIcon, LinkedinIcon, HotelIcon } from 'lucide-react';

interface FooterLink {
	title: string;
	href: string;
	icon?: React.ComponentType<{ className?: string }>;
}

interface FooterSection {
	label: string;
	links: FooterLink[];
}

const footerLinks: FooterSection[] = [
	{
		label: 'Quick Links',
		links: [
			{ title: 'Home', href: '/' },
			{ title: 'Rooms', href: '/hotels/1' },
			{ title: 'About Us', href: '#' },
			{ title: 'Contact', href: '#' },
		],
	},
	{
		label: 'Support',
		links: [
			{ title: 'Help Center', href: '#' },
			{ title: 'Booking Guide', href: '#' },
			{ title: 'Cancellation Policy', href: '#' },
			{ title: 'FAQ', href: '#' },
		],
	},
	{
		label: 'Legal',
		links: [
			{ title: 'Privacy Policy', href: '#' },
			{ title: 'Terms of Service', href: '#' },
			{ title: 'Cookie Policy', href: '#' },
			{ title: 'Sitemap', href: '#' },
		],
	},
	{
		label: 'Social Links',
		links: [
			{ title: 'Facebook', href: '#', icon: FacebookIcon },
			{ title: 'Instagram', href: '#', icon: InstagramIcon },
			{ title: 'Youtube', href: '#', icon: YoutubeIcon },
			{ title: 'Twitter', href: '#', icon: TwitterIcon },
		],
	},
];

type AnimatedContainerProps = {
	delay?: number;
	className?: string;
	children: ReactNode;
};

function AnimatedContainer({ className, delay = 0.1, children }: AnimatedContainerProps) {
	const shouldReduceMotion = useReducedMotion();

	if (shouldReduceMotion) {
		return <div className={className}>{children}</div>;
	}

	return (
		<motion.div
			initial={{ filter: 'blur(4px)', translateY: -8, opacity: 0 }}
			whileInView={{ filter: 'blur(0px)', translateY: 0, opacity: 1 }}
			viewport={{ once: true }}
			transition={{ delay, duration: 0.8 }}
			className={className}
		>
			{children}
		</motion.div>
	);
}

export function Footer() {
	return (
		<footer className="md:rounded-t-6xl relative w-full max-w-7xl mx-auto flex flex-col items-center justify-center rounded-t-4xl border-t border-gray-800 bg-black px-6 py-12 lg:py-16 mt-20" style={{ background: 'radial-gradient(35% 128px at 50% 0%, rgba(255, 255, 255, 0.08), transparent)' }}>
			<div className="absolute top-0 right-1/2 left-1/2 h-2 w-2/3 -translate-x-1/2 -translate-y-1/2 rounded-full blur-2xl" style={{ background: 'radial-gradient(circle, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0.3) 40%, rgba(255, 255, 255, 0) 100%)' }} />

			<div className="grid w-full gap-8 xl:grid-cols-3 xl:gap-8">
				<AnimatedContainer className="space-y-4">
					<div className="flex items-center space-x-2">
						<HotelIcon className="w-8 h-8 text-primary-green" />
						<span className="text-2xl font-bold text-primary-green">Belmont Hotel</span>
					</div>
					<p className="text-gray-300 text-sm mt-4">
						Experience luxury redefined in the heart of El Nido, Palawan. Your perfect getaway starts here.
					</p>
					<p className="text-gray-500 mt-8 text-sm md:mt-0">
						© {new Date().getFullYear()} Belmont Hotel. All rights reserved.
					</p>
				</AnimatedContainer>

				<div className="mt-10 grid grid-cols-2 gap-8 md:grid-cols-4 xl:col-span-2 xl:mt-0">
					{footerLinks.map((section, index) => (
						<AnimatedContainer key={section.label} delay={0.1 + index * 0.1}>
							<div className="mb-10 md:mb-0">
								<h3 className="text-sm font-semibold text-white mb-4">{section.label}</h3>
								<ul className="text-gray-300 space-y-2 text-sm">
									{section.links.map((link) => {
										const IconComponent = link.icon;
										return (
											<li key={link.title}>
												<a
													href={link.href}
													className="hover:text-primary-green inline-flex items-center transition-all duration-300"
												>
													{IconComponent && <IconComponent className="mr-1 w-4 h-4" />}
													{link.title}
												</a>
											</li>
										);
									})}
								</ul>
							</div>
						</AnimatedContainer>
					))}
				</div>
			</div>
		</footer>
	);
}
