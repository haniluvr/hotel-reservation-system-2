import React from "react";
import { cn } from "@/lib/utils";
import { ArrowRight } from "lucide-react";

export interface DestinationCardProps {
	className?: string;
	imageUrl: string;
	location: string;
	flag: string;
	stats: string;
	href: string;
	themeColor: string;
}

export function DestinationCard({
	className,
	imageUrl,
	location,
	flag,
	stats,
	href,
	themeColor,
}: DestinationCardProps) {
	return React.createElement(
		"div",
		{
			style: {
				"--theme-color": themeColor,
			} as React.CSSProperties,
			className: cn("group w-full h-full", className),
		},
		React.createElement(
			"a",
			{
				href: href,
				className:
					"relative block w-full h-full rounded-2xl overflow-hidden shadow-lg transition-all duration-500 ease-in-out group-hover:scale-105 group-hover:shadow-[0_0_60px_-15px_hsl(var(--theme-color)/0.6)]",
				"aria-label": `Explore details for ${location}`,
				style: {
					boxShadow: `0 0 40px -15px hsl(var(--theme-color) / 0.5)`,
				},
			},
			React.createElement("div", {
				className:
					"absolute inset-0 bg-cover bg-center transition-transform duration-500 ease-in-out group-hover:scale-110",
				style: { backgroundImage: `url(${imageUrl})` },
			}),
			React.createElement("div", {
				className: "absolute inset-0",
				style: {
					background: `linear-gradient(to top, hsl(var(--theme-color) / 0.9), hsl(var(--theme-color) / 0.6) 30%, transparent 60%)`,
				},
			}),
			React.createElement(
				"div",
				{
					className: "relative flex flex-col justify-end h-full p-6 text-white",
				},
				React.createElement(
					"h3",
					{ className: "text-3xl font-bold tracking-tight" },
					location,
					React.createElement("span", { className: "text-2xl ml-1" }, flag)
				),
				React.createElement(
					"p",
					{ className: "text-sm text-white/80 mt-1 font-medium" },
					stats
				),
				React.createElement(
					"div",
					{
						className:
							"mt-8 flex items-center justify-between bg-[hsl(var(--theme-color)/0.2)] backdrop-blur-md border border-[hsl(var(--theme-color)/0.3)] rounded-lg px-4 py-3 transition-all duration-300 group-hover:bg-[hsl(var(--theme-color)/0.4)] group-hover:border-[hsl(var(--theme-color)/0.5)]",
					},
					React.createElement(
						"span",
						{ className: "text-sm font-semibold tracking-wide" },
						"Explore Now"
					),
					React.createElement(ArrowRight, {
						className:
							"h-4 w-4 transform transition-transform duration-300 group-hover:translate-x-1",
					})
				)
			)
		)
	);
}
