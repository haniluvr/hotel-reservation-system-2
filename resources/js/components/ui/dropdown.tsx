import React, { useState, useRef, useEffect } from "react";

interface DropdownProps {
  trigger: React.ReactNode;
  children: React.ReactNode;
  align?: "left" | "right";
  className?: string;
}

export function Dropdown({
  trigger,
  children,
  align = "right",
  className = "",
}: DropdownProps) {
  const [isOpen, setIsOpen] = useState(false);
  const dropdownRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        dropdownRef.current &&
        !dropdownRef.current.contains(event.target as Node)
      ) {
        setIsOpen(false);
      }
    };

    if (isOpen) {
      document.addEventListener("mousedown", handleClickOutside);
    }

    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, [isOpen]);

  return React.createElement(
    "div",
    { className: `relative ${className}`, ref: dropdownRef },
    React.createElement("div", { onClick: () => setIsOpen(!isOpen) }, trigger),
    isOpen &&
      React.createElement(
        "div",
        {
          className: `absolute ${
            align === "right" ? "right-0" : "left-0"
          } mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50`,
        },
        React.createElement("div", { className: "py-1" }, children)
      )
  );
}

interface DropdownItemProps {
  href?: string;
  onClick?: (e?: React.MouseEvent) => void;
  children: React.ReactNode;
  className?: string;
}

export function DropdownItem({
  href,
  onClick,
  children,
  className = "",
}: DropdownItemProps) {
  const handleClick = (e: React.MouseEvent) => {
    if (onClick) {
      onClick(e);
    }
  };

  if (href) {
    return React.createElement(
      "a",
      {
        href: href,
        onClick: handleClick,
        className: `block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 ${className}`,
      },
      children
    );
  }

  return React.createElement(
    "button",
    {
      onClick: handleClick,
      className: `block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 ${className}`,
    },
    children
  );
}

