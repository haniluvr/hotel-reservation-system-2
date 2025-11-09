import React from "react";
import { DestinationCard } from "@/components/ui/card-21";

interface Destination {
	name: string;
	image: string;
	count: string;
	description?: string;
}

interface DestinationCardsProps {
	destinations: Destination[];
}

function DestinationCards({ destinations }: DestinationCardsProps) {
	console.log("DestinationCards component rendering with destinations:", destinations);

	if (!destinations || destinations.length === 0) {
		console.warn("No destinations provided to DestinationCards");
		return React.createElement(
			"div",
			{ className: "text-center py-12 text-gray-600" },
			"No attractions available"
		);
	}

	// Map destinations to theme colors
	const themeColors = [
		"150 50% 25%", // Deep green for first card
		"250 50% 30%", // Purple for second card
		"200 60% 30%", // Teal for third card
	];

	return React.createElement(
		"div",
		{ className: "grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12" },
		destinations.map((destination, index) =>
			React.createElement(
				"div",
				{ key: index, className: "w-full h-[450px]" },
				React.createElement(DestinationCard, {
					imageUrl: destination.image,
					location: destination.name,
					flag: "", // Empty flag for now, can be added to data later
					stats: destination.count,
					href: "#",
					themeColor: themeColors[index % themeColors.length],
				})
			)
		)
	);
}

export default DestinationCards;
