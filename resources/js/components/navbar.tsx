import React from "react";
import { useState } from "react";
import {
  Navbar,
  NavBody,
  NavItems,
  MobileNav,
  NavbarLogo,
  NavbarButton,
  MobileNavHeader,
  MobileNavToggle,
  MobileNavMenu,
} from "@/components/ui/resizable-navbar";

interface NavbarComponentProps {
  logoText?: string;
  logoSrc?: string;
  user?: {
    id: number;
    name: string;
    email: string;
  } | null;
}

const NavbarComponent = ({ logoText = "Belmont Hotel", logoSrc, user }: NavbarComponentProps) => {
  const navItems = [
    {
      name: "Home",
      link: "/",
    },
    {
      name: "Accommodations",
      link: "/accommodations",
    },
    {
      name: "About",
      link: "#about",
    },
    {
      name: "Contact",
      link: "#contact",
    },
  ];

  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  return React.createElement(
    "div",
    { className: "relative w-full" },
    React.createElement(
      Navbar,
      null,
      // Desktop Navigation
      React.createElement(
        NavBody,
        null,
        React.createElement(NavbarLogo, { href: "/", logoText: logoText, logoSrc: undefined }),
        React.createElement(NavItems, { items: navItems }),
        React.createElement(
          "div",
          { className: "flex items-center gap-4" },
          React.createElement(
            NavbarButton,
            { 
              variant: "secondary",
              as: "button",
              type: "button",
              onClick: (e: any) => {
                e.preventDefault();
                e.stopPropagation();
                if (typeof window !== 'undefined') {
                  // Small delay to ensure Alpine is ready
                  setTimeout(() => {
                    // Try the exposed function first
                    if ((window as any).searchModalOpen) {
                      (window as any).searchModalOpen();
                      return;
                    }
                    // Try global function
                    if ((window as any).openSearchModal) {
                      (window as any).openSearchModal();
                    } else {
                      // Last resort: dispatch event
                      const event = new CustomEvent('openSearchModal');
                      window.dispatchEvent(event);
                    }
                  }, 50);
                }
              }
            },
            React.createElement(
              "svg",
              { className: "w-5 h-5 inline-block mr-1", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" },
              React.createElement("path", {
                strokeLinecap: "round",
                strokeLinejoin: "round",
                strokeWidth: "2",
                d: "M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z",
              })
            )
          ),
          user
            ? React.createElement(
                NavbarButton,
                { variant: "primary", href: "/account", className: "rounded-full" },
                React.createElement("span", { className: "hidden sm:inline" }, "Account")
              )
            : React.createElement(
                NavbarButton,
                { 
                  variant: "primary",
                  as: "button",
                  type: "button",
                  className: "rounded-full",
                  onClick: (e: any) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof window !== 'undefined') {
                      // Try the global function
                      if ((window as any).openAuthModal) {
                        (window as any).openAuthModal('login');
                      } else {
                        // Fallback: dispatch event directly
                        const event = new CustomEvent('openAuthModal', { detail: { type: 'login' } });
                        window.dispatchEvent(event);
                      }
                    }
                  }
                },
                "Sign In"
              )
        )
      ),
      // Mobile Navigation
      React.createElement(
        MobileNav,
        null,
        React.createElement(
          MobileNavHeader,
          null,
          React.createElement(NavbarLogo, { href: "/", logoText: logoText, logoSrc: undefined }),
          React.createElement(MobileNavToggle, {
            isOpen: isMobileMenuOpen,
            onClick: () => setIsMobileMenuOpen(!isMobileMenuOpen),
          })
        ),
        React.createElement(
          MobileNavMenu,
          {
            isOpen: isMobileMenuOpen,
            onClose: () => setIsMobileMenuOpen(false),
          },
          navItems.map((item, idx) =>
            React.createElement(
              "a",
              {
                key: `mobile-link-${idx}`,
                href: item.link,
                onClick: () => setIsMobileMenuOpen(false),
                className: "relative text-gray-200 dark:text-neutral-300 hover:text-white",
              },
              React.createElement("span", { className: "block" }, item.name)
            )
          ),
          React.createElement(
            "button",
            {
              onClick: (e: React.MouseEvent) => {
                e.preventDefault();
                e.stopPropagation();
                setIsMobileMenuOpen(false);
                if (typeof window !== 'undefined') {
                  setTimeout(() => {
                    if ((window as any).searchModalOpen) {
                      (window as any).searchModalOpen();
                    } else if ((window as any).openSearchModal) {
                      (window as any).openSearchModal();
                    } else {
                      const event = new CustomEvent('openSearchModal');
                      window.dispatchEvent(event);
                    }
                  }, 50);
                }
              },
              className: "w-full px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold rounded-lg transition-colors duration-200 border border-gray-700 flex items-center justify-center gap-2"
            },
            React.createElement(
              "svg",
              { className: "w-5 h-5", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" },
              React.createElement("path", {
                strokeLinecap: "round",
                strokeLinejoin: "round",
                strokeWidth: "2",
                d: "M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z",
              })
            ),
            "Search"
          ),
          React.createElement(
            "div",
            { className: "flex w-full flex-col gap-4" },
            user
              ? React.createElement(
                  React.Fragment,
                  null,
                  React.createElement(
                    "div",
                    { className: "px-4 py-2 text-sm text-gray-300 border-b border-gray-700" },
                    React.createElement("div", { className: "font-semibold text-white" }, user.name),
                    React.createElement("div", { className: "text-xs text-gray-400" }, user.email)
                  ),
                  React.createElement(
                    NavbarButton,
                    {
                      onClick: () => setIsMobileMenuOpen(false),
                      variant: "primary",
                      className: "w-full",
                      as: "a",
                      href: "/account",
                    },
                    "Account"
                  ),
                  React.createElement(
                    NavbarButton,
                    {
                      onClick: () => setIsMobileMenuOpen(false),
                      variant: "secondary",
                      className: "w-full",
                      as: "a",
                      href: "/profile",
                    },
                    "Profile"
                  )
                )
              : React.createElement(
                  React.Fragment,
                  null,
                  React.createElement(
                    "button",
                    {
                      onClick: (e: React.MouseEvent) => {
                        e.preventDefault();
                        e.stopPropagation();
                        setIsMobileMenuOpen(false);
                        if (typeof window !== 'undefined') {
                          if ((window as any).openAuthModal) {
                            (window as any).openAuthModal('login');
                          } else {
                            const event = new CustomEvent('openAuthModal', { detail: { type: 'login' } });
                            window.dispatchEvent(event);
                          }
                        }
                      },
                      className: "w-full px-4 py-2 bg-primary-green hover:bg-primary-green-hover text-white font-semibold rounded-lg transition-colors duration-200"
                    },
                    "Sign In"
                  ),
                  React.createElement(
                    "button",
                    {
                      onClick: (e: React.MouseEvent) => {
                        e.preventDefault();
                        e.stopPropagation();
                        setIsMobileMenuOpen(false);
                        if (typeof window !== 'undefined') {
                          if ((window as any).openAuthModal) {
                            (window as any).openAuthModal('register');
                          } else {
                            const event = new CustomEvent('openAuthModal', { detail: { type: 'register' } });
                            window.dispatchEvent(event);
                          }
                        }
                      },
                      className: "w-full px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold rounded-lg transition-colors duration-200 border border-gray-700"
                    },
                    "Create Account"
                  )
                )
          )
        )
      )
    )
  );
};

export default NavbarComponent;

