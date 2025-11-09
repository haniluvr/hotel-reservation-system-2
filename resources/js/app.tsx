import './bootstrap';
import Alpine from 'alpinejs';
import React from 'react';
import { createRoot } from 'react-dom/client';
import DestinationCards from '@/components/ui/destination-cards';
import ScrollExpandMedia from '@/components/ui/scroll-expansion-hero';
import RoomsCarousel from '@/components/ui/rooms-carousel';
import NavbarComponent from '@/components/navbar';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Store roots to prevent re-creating them
const componentRoots = new WeakMap();

// Find all elements with data-react-component attribute
function initReactComponents() {
    const reactComponents = document.querySelectorAll('[data-react-component]:not([data-mounted])');
    
    if (reactComponents.length === 0) {
        return;
    }
    
    reactComponents.forEach((element) => {
        // Mark as mounted to prevent double mounting
        element.setAttribute('data-mounted', 'true');
        
        const componentName = element.getAttribute('data-react-component');
        const props = element.getAttribute('data-react-props');
        let parsedProps = {};
        
        try {
            parsedProps = props ? JSON.parse(props) : {};
        } catch (e) {
            // Silently handle parsing errors
        }
        
        try {
            // Check if root already exists
            let root = componentRoots.get(element);
            if (!root) {
                root = createRoot(element);
                componentRoots.set(element, root);
            }
            
            // Map component names to actual components
            switch (componentName) {
                case 'DestinationCards':
                    if (DestinationCards) {
                        root.render(<DestinationCards {...parsedProps} />);
                    }
                    break;
                case 'ScrollExpandMedia':
                    if (ScrollExpandMedia) {
                        // Capture the inner HTML as children
                        const scrollExpandChildren = element.innerHTML;
                        // Clear the element to avoid duplicate content
                        element.innerHTML = '';
                        
                        root.render(
                            <ScrollExpandMedia {...parsedProps}>
                                <div dangerouslySetInnerHTML={{ __html: scrollExpandChildren }} />
                            </ScrollExpandMedia>
                        );
                    }
                    break;
                case 'RoomsCarousel':
                    if (RoomsCarousel) {
                        root.render(<RoomsCarousel {...parsedProps} />);
                    }
                    break;
                case 'Navbar':
                    if (NavbarComponent) {
                        root.render(<NavbarComponent {...parsedProps} />);
                    }
                    break;
            }
        } catch (error) {
            // Silently handle mounting errors
        }
    });
}

// Try to initialize immediately if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReactComponents);
} else {
    // DOM is already loaded
    initReactComponents();
}

// Also try after a short delay to ensure everything is ready
setTimeout(() => {
    initReactComponents();
}, 1000);

