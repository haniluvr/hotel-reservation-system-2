import './bootstrap';
import Alpine from 'alpinejs';
import React from 'react';
import { createRoot } from 'react-dom/client';
import DestinationCards from '@/components/ui/destination-cards';
import ScrollExpandMedia from '@/components/ui/scroll-expansion-hero';
import RoomsCarousel from '@/components/ui/rooms-carousel';
import NavbarComponent from '@/components/navbar';

console.log('✅ React and components imported successfully');
console.log('DestinationCards component:', DestinationCards);
console.log('ScrollExpandMedia component:', ScrollExpandMedia);
console.log('RoomsCarousel component:', RoomsCarousel);
console.log('NavbarComponent:', NavbarComponent);

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Store roots to prevent re-creating them
const componentRoots = new WeakMap();

// Find all elements with data-react-component attribute
function initReactComponents() {
    const reactComponents = document.querySelectorAll('[data-react-component]:not([data-mounted])');
    
    console.log(`Found ${reactComponents.length} React component(s) to mount`);
    
    if (reactComponents.length === 0) {
        console.log('No React components found to mount');
        return;
    }
    
    reactComponents.forEach((element) => {
        // Mark as mounted to prevent double mounting
        element.setAttribute('data-mounted', 'true');
        
        const componentName = element.getAttribute('data-react-component');
        const props = element.getAttribute('data-react-props');
        let parsedProps = {};
        
        console.log(`Attempting to mount component: ${componentName}`, element);
        
        try {
            parsedProps = props ? JSON.parse(props) : {};
            console.log('Parsed props:', parsedProps);
        } catch (e) {
            console.error('Error parsing React props:', e, 'Raw props:', props);
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
                // case 'Footer':
                //     root.render(<Footer {...parsedProps} />);
                //     console.log('✅ Footer component mounted successfully');
                //     break;
                case 'DestinationCards':
                    console.log('Rendering DestinationCards with props:', parsedProps);
                    console.log('DestinationCards component type:', typeof DestinationCards);
                    if (!DestinationCards) {
                        console.error('❌ DestinationCards component is undefined!');
                        return;
                    }
                    try {
                        root.render(<DestinationCards {...parsedProps} />);
                        console.log('✅ DestinationCards component mounted successfully');
                    } catch (renderError) {
                        console.error('❌ Error rendering DestinationCards:', renderError);
                        throw renderError;
                    }
                    break;
                case 'ScrollExpandMedia':
                    console.log('Rendering ScrollExpandMedia with props:', parsedProps);
                    if (!ScrollExpandMedia) {
                        console.error('❌ ScrollExpandMedia component is undefined!');
                        return;
                    }
                    try {
                        // Capture the inner HTML as children
                        const scrollExpandChildren = element.innerHTML;
                        // Clear the element to avoid duplicate content
                        element.innerHTML = '';
                        
                        root.render(
                            <ScrollExpandMedia {...parsedProps}>
                                <div dangerouslySetInnerHTML={{ __html: scrollExpandChildren }} />
                            </ScrollExpandMedia>
                        );
                        console.log('✅ ScrollExpandMedia component mounted successfully');
                    } catch (renderError) {
                        console.error('❌ Error rendering ScrollExpandMedia:', renderError);
                        throw renderError;
                    }
                    break;
                case 'RoomsCarousel':
                    console.log('Rendering RoomsCarousel with props:', parsedProps);
                    if (!RoomsCarousel) {
                        console.error('❌ RoomsCarousel component is undefined!');
                        return;
                    }
                    try {
                        root.render(<RoomsCarousel {...parsedProps} />);
                        console.log('✅ RoomsCarousel component mounted successfully');
                    } catch (renderError) {
                        console.error('❌ Error rendering RoomsCarousel:', renderError);
                        throw renderError;
                    }
                    break;
                case 'Navbar':
                    console.log('Rendering Navbar with props:', parsedProps);
                    if (!NavbarComponent) {
                        console.error('❌ NavbarComponent is undefined!');
                        return;
                    }
                    try {
                        root.render(<NavbarComponent {...parsedProps} />);
                        console.log('✅ Navbar component mounted successfully');
                    } catch (renderError) {
                        console.error('❌ Error rendering Navbar:', renderError);
                        throw renderError;
                    }
                    break;
                default:
                    console.warn(`⚠️ Unknown component: ${componentName}`);
            }
        } catch (error) {
            console.error('❌ Error mounting React component:', error);
            console.error('Component name:', componentName);
            console.error('Error details:', error.message, error.stack);
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
    console.log('Retry: Checking for React components again...');
    initReactComponents();
}, 1000);

