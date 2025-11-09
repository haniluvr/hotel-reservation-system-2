import React from "react";
import { useEffect, useState } from "react";
import { Carousel, CarouselApi, CarouselContent, CarouselItem } from "@/components/ui/carousel";
import { Button } from "@/components/ui/button";

interface RoomData {
  id: number;
  slug: string;
  room_type: string;
  description?: string;
  price_per_night: number;
  max_guests: number;
  max_adults?: number;
  max_children?: number;
  size?: string;
  available_quantity: number;
  images?: string[];
  amenities?: string[];
}

interface RoomsCarouselProps {
  rooms: RoomData[];
  hotelId: number;
}

const RoomsCarousel = ({ rooms, hotelId }: RoomsCarouselProps) => {
  const [carouselApi, setCarouselApi] = useState<CarouselApi>();
  const [canScrollPrev, setCanScrollPrev] = useState(false);
  const [canScrollNext, setCanScrollNext] = useState(false);
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isHovered, setIsHovered] = useState(false);

  useEffect(() => {
    if (!carouselApi) {
      return;
    }

    const updateSelection = () => {
      setCanScrollPrev(carouselApi.canScrollPrev());
      setCanScrollNext(carouselApi.canScrollNext());
      setCurrentSlide(carouselApi.selectedScrollSnap());
    };

    updateSelection();
    carouselApi.on("select", updateSelection);

    return () => {
      carouselApi.off("select", updateSelection);
    };
  }, [carouselApi]);

  // Autoplay functionality
  useEffect(() => {
    if (!carouselApi || isHovered) {
      return;
    }

    const autoplayInterval = setInterval(() => {
      if (carouselApi.canScrollNext()) {
        carouselApi.scrollNext();
      } else {
        // Loop back to the start
        carouselApi.scrollTo(0);
      }
    }, 3000); // Change slide every 3 seconds

    return () => {
      clearInterval(autoplayInterval);
    };
  }, [carouselApi, isHovered]);

  if (!rooms || rooms.length === 0) {
    return React.createElement(
      "div",
      { className: "text-center py-12" },
      React.createElement("p", { className: "text-gray-300 text-lg" }, "No featured rooms available at the moment.")
    );
  }

  return React.createElement(
    "section",
    { className: "py-20 bg-black" },
    React.createElement(
      "div",
      { className: "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" },
      // Header
      React.createElement(
        "div",
        { className: "mb-8 flex items-end justify-between md:mb-14 lg:mb-16" },
        React.createElement(
          "div",
          { className: "flex flex-col gap-4" },
          React.createElement(
            "h2",
            { className: "text-4xl md:text-5xl font-cormorant font-bold text-white" },
            "Featured Rooms"
          ),
          React.createElement(
            "p",
            { className: "max-w-lg text-xl text-gray-300" },
            "Discover our luxurious accommodations at Belmont Hotel El Nido, each offering unique experiences and world-class comfort."
          )
        ),
        // Navigation arrows (desktop)
        React.createElement(
          "div",
          { className: "hidden shrink-0 gap-2 md:flex" },
          React.createElement(
            Button,
            {
              size: "icon",
              variant: "ghost",
              onClick: () => carouselApi?.scrollPrev(),
              disabled: !canScrollPrev,
              className: "disabled:pointer-events-auto text-white hover:bg-gray-800 hover:text-primary-green",
            },
            React.createElement(
              "svg",
              { className: "size-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" },
              React.createElement("path", {
                strokeLinecap: "round",
                strokeLinejoin: "round",
                strokeWidth: "2",
                d: "M15 19l-7-7 7-7",
              })
            )
          ),
          React.createElement(
            Button,
            {
              size: "icon",
              variant: "ghost",
              onClick: () => carouselApi?.scrollNext(),
              disabled: !canScrollNext,
              className: "disabled:pointer-events-auto text-white hover:bg-gray-800 hover:text-primary-green",
            },
            React.createElement(
              "svg",
              { className: "size-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" },
              React.createElement("path", {
                strokeLinecap: "round",
                strokeLinejoin: "round",
                strokeWidth: "2",
                d: "M9 5l7 7-7 7",
              })
            )
          )
        )
      )
    ),
    // Full-width carousel container
    React.createElement(
      "div",
      { 
        className: "w-full",
        onMouseEnter: () => setIsHovered(true),
        onMouseLeave: () => setIsHovered(false),
      },
      React.createElement(
        Carousel,
        {
          setApi: setCarouselApi,
          opts: {
            align: "start",
            loop: true,
            breakpoints: {
              "(max-width: 768px)": {
                dragFree: true,
              },
            },
          },
        },
        React.createElement(
          CarouselContent,
          { className: "ml-0 2xl:ml-[max(8rem,calc(50vw-700px))] 2xl:mr-[max(0rem,calc(50vw-700px))]" },
          rooms.map((room) =>
            React.createElement(
              CarouselItem,
              {
                key: room.id,
                className: "max-w-[320px] pl-[20px] md:max-w-[380px] lg:max-w-[420px]",
              },
              React.createElement(
                "div",
                { className: "bg-gray-900 rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 group border border-gray-800 h-full flex flex-col" },
                // Room Image
                React.createElement(
                  "div",
                  { className: "relative h-64 overflow-hidden flex-shrink-0" },
                  React.createElement("img", {
                    src: room.images && room.images.length > 0
                      ? room.images[0]
                      : "https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
                    alt: room.room_type,
                    className: "w-full h-full object-cover group-hover:scale-110 transition-transform duration-500",
                    loading: "lazy",
                  }),
                  React.createElement("div", {
                    className: "absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent",
                  }),
                  // Availability Badge (top-right)
                  React.createElement(
                    "div",
                    { className: "absolute top-4 right-4" },
                    room.available_quantity > 0
                      ? React.createElement(
                          "span",
                          { className: "px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full" },
                          `${room.available_quantity} Available`
                        )
                      : React.createElement(
                      "span",
                          { className: "px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-full" },
                          "Sold Out"
                        )
                  )
                ),
                // Room Info
                React.createElement(
                  "div",
                  { className: "p-6 flex flex-col flex-1" },
                  React.createElement(
                    "h3",
                    { className: "text-2xl font-bold text-white mb-2 group-hover:text-primary-green transition-colors" },
                    room.room_type
                  ),
                  React.createElement(
                    "p",
                    { className: "text-gray-300 mb-4 line-clamp-2" },
                    room.description
                      ? room.description.substring(0, 120) + (room.description.length > 120 ? "..." : "")
                      : "Luxurious accommodation with modern amenities"
                  ),
                  // Room Details (Guests, Adults, Children, Size)
                  React.createElement(
                    "div",
                    { className: "flex flex-wrap gap-3 mb-4 text-sm text-gray-400" },
                    React.createElement(
                      "div",
                      { className: "flex items-center" },
                      React.createElement(
                        "svg",
                        { className: "w-4 h-4 mr-1", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" },
                        React.createElement("path", {
                          strokeLinecap: "round",
                          strokeLinejoin: "round",
                          strokeWidth: "2",
                          d: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
                        })
                      ),
                      React.createElement("span", null, `${room.max_guests} Guests`)
                    ),
                    room.max_adults &&
                      React.createElement(
                        React.Fragment,
                        null,
                        React.createElement("span", null, "•"),
                        React.createElement("span", { className: "ml-1" }, `${room.max_adults} Adults`)
                      ),
                    room.max_children !== undefined && room.max_children > 0 &&
                      React.createElement(
                        React.Fragment,
                        null,
                        React.createElement("span", null, "•"),
                        React.createElement("span", { className: "ml-1" }, `${room.max_children} Children`)
                    ),
                    room.size &&
                      React.createElement(
                        React.Fragment,
                        null,
                        React.createElement("span", null, "•"),
                        React.createElement("span", { className: "ml-1" }, room.size)
                      )
                  ),
                  // Amenities Tags
                  room.amenities && room.amenities.length > 0 &&
                    React.createElement(
                      "div",
                      { className: "mb-4" },
                  React.createElement(
                    "div",
                        { className: "flex flex-wrap gap-2" },
                      room.amenities.slice(0, 3).map((amenity, idx) =>
                        React.createElement(
                          "span",
                          {
                            key: idx,
                              className: "px-2 py-1 bg-gray-800 text-gray-300 text-xs rounded border border-gray-700",
                          },
                          amenity
                        )
                      ),
                      room.amenities.length > 3 &&
                      React.createElement(
                        "span",
                            { className: "px-2 py-1 bg-gray-800 text-gray-300 text-xs rounded border border-gray-700" },
                        `+${room.amenities.length - 3} more`
                          )
                      )
                  ),
                  // Price and CTA
                  React.createElement(
                    "div",
                    { className: "flex justify-between items-center pt-4 border-t border-gray-800 mt-auto" },
                    React.createElement(
                      "div",
                      null,
                      React.createElement(
                        "div",
                        { className: "text-2xl font-bold text-primary-green" },
                        `₱${Number(room.price_per_night || room.price || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                      ),
                      React.createElement("div", { className: "text-xs text-gray-400" }, "per night")
                    ),
                    React.createElement(
                      "a",
                      {
                        href: `/accommodations/${room.slug}`,
                        className: "bg-primary-green text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-green-hover transition-colors duration-200 flex items-center",
                      },
                      "View Details",
                      React.createElement(
                        "svg",
                        { className: "w-4 h-4 ml-2", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" },
                        React.createElement("path", {
                          strokeLinecap: "round",
                          strokeLinejoin: "round",
                          strokeWidth: "2",
                          d: "M9 5l7 7-7 7",
                        })
                      )
                    )
                  )
                )
              )
            )
          )
        ),
        // Dot indicators
        React.createElement(
          "div",
          { className: "mt-8 flex justify-center gap-2" },
          rooms.map((_, index) =>
            React.createElement("button", {
              key: index,
              className: `h-2 w-2 rounded-full transition-colors ${
                currentSlide === index ? "bg-primary-green" : "bg-gray-700"
              }`,
              onClick: () => carouselApi?.scrollTo(index),
              "aria-label": `Go to slide ${index + 1}`,
            })
          )
        )
      )
    ),
    // View All Button
    React.createElement(
      "div",
      { className: "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" },
      React.createElement(
        "div",
        { className: "text-center mt-12" },
        React.createElement(
          "a",
          {
            href: `/hotels/${hotelId}`,
            className: "inline-flex items-center px-8 py-3 bg-primary-green text-white font-semibold rounded-lg hover:bg-primary-green-hover transition-all duration-300 transform hover:scale-105 shadow-lg",
          },
          "View All Rooms",
          React.createElement(
            "svg",
            { className: "w-5 h-5 ml-2", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" },
            React.createElement("path", {
              strokeLinecap: "round",
              strokeLinejoin: "round",
              strokeWidth: "2",
              d: "M9 5l7 7-7 7",
            })
          )
        )
      )
    )
  );
};

export default RoomsCarousel;

