import React from "react";
import { WavyBackground } from "./wavy-background";

interface CTAWavyProps {
  hotelId: number;
}

export const CTAWavy: React.FC<CTAWavyProps> = ({ hotelId }) => {
  return React.createElement(
    WavyBackground,
    {
      containerClassName: "py-12",
      colors: ["#38bdf8", "#818cf8", "#c084fc", "#e879f9", "#22d3ee"],
      waveWidth: 30,
      backgroundFill: "black",
      blur: 10,
      speed: "slow",
      waveOpacity: 0.5,
    },
    React.createElement(
      "div",
      { className: "max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center" },
      React.createElement(
        "h2",
        { className: "text-4xl md:text-5xl font-cormorant font-bold text-white mb-6" },
        "Ready to Plan Your Next Adventure?"
      ),
      React.createElement(
        "p",
        { className: "text-xl text-white/90 mb-8" },
        "Start exploring our luxurious rooms at Belmont Hotel El Nido and find your perfect stay today."
      ),
      React.createElement(
        "a",
        {
          href: `/hotels/${hotelId}`,
          className: "inline-flex items-center px-8 py-4 bg-white text-primary-green font-semibold rounded-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-xl",
        },
        "View All Rooms",
        React.createElement(
          "svg",
          {
            className: "w-5 h-5 ml-2",
            fill: "none",
            stroke: "currentColor",
            viewBox: "0 0 24 24",
          },
          React.createElement("path", {
            strokeLinecap: "round",
            strokeLinejoin: "round",
            strokeWidth: 2,
            d: "M9 5l7 7-7 7",
          })
        )
      )
    )
  );
};

export default CTAWavy;
