# React + TypeScript + shadcn/ui Integration Setup

This document explains how to set up and use React components in your Laravel application.

## 🚀 Installation Steps

### 1. Install NPM Dependencies

Run the following command to install all required dependencies:

```bash
npm install
```

This will install:
- React & React DOM
- TypeScript
- Framer Motion (for animations)
- Lucide React (icons)
- shadcn/ui utilities (class-variance-authority, clsx, tailwind-merge)
- Vite React plugin
- Type definitions

### 2. Build Assets

For development with hot-reload:
```bash
npm run dev
```

For production build:
```bash
npm run build
```

### 3. Start Laravel Server

In a separate terminal:
```bash
php artisan serve
```

### 4. View the Demo

Navigate to: `http://localhost:8000/react-demo`

You should see:
- A full-screen hero section with "Scroll Down!" message
- A beautiful animated footer component when you scroll down
- Smooth animations on scroll using Framer Motion

---

## 📁 Project Structure

```
hotel-reservation-system/
├── components.json              # shadcn/ui configuration
├── tsconfig.json                # TypeScript configuration
├── vite.config.js               # Vite with React support
├── tailwind.config.js           # Updated with shadcn variables
├── resources/
│   ├── css/
│   │   └── app.css              # Updated with CSS variables
│   ├── js/
│   │   ├── app.tsx              # React entry point
│   │   ├── lib/
│   │   │   └── utils.ts         # Utility functions (cn)
│   │   └── components/
│   │       └── ui/
│   │           └── footer-section.tsx  # Footer component
│   └── views/
│       └── react-demo.blade.php # Demo page
└── routes/
    └── web.php                  # Added /react-demo route
```

---

## 🎨 Using React Components in Blade Templates

### Method 1: Mount React Component in Blade

Add this to any Blade template:

```html
<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
</head>
<body>
    <!-- Your content -->
    
    <!-- Mount React Component -->
    <div data-react-component="Footer" data-react-props="{}"></div>
</body>
</html>
```

### Method 2: Add Props to Components

```html
<div 
    data-react-component="Footer" 
    data-react-props='{"theme": "dark", "year": 2024}'>
</div>
```

---

## 🧩 Creating New React Components

### 1. Create Component File

Create `resources/js/components/ui/your-component.tsx`:

```tsx
import React from 'react';
import { cn } from '@/lib/utils';

interface YourComponentProps {
  className?: string;
  // Add your props
}

export function YourComponent({ className }: YourComponentProps) {
  return (
    <div className={cn("your-classes", className)}>
      {/* Your component */}
    </div>
  );
}
```

### 2. Register Component in app.tsx

Update `resources/js/app.tsx`:

```tsx
import { YourComponent } from '@/components/ui/your-component';

// Inside the switch statement:
case 'YourComponent':
    root.render(<YourComponent {...parsedProps} />);
    break;
```

### 3. Use in Blade Template

```html
<div data-react-component="YourComponent" data-react-props="{}"></div>
```

---

## 🎯 Adding More shadcn/ui Components

To add official shadcn/ui components, you'll need the shadcn CLI:

### Install shadcn CLI
```bash
npx shadcn@latest init
```

### Add Components
```bash
npx shadcn@latest add button
npx shadcn@latest add card
npx shadcn@latest add dialog
# etc...
```

Components will be added to `resources/js/components/ui/`

---

## 🔧 Key Features

### ✅ What's Set Up

1. **React 18** with TypeScript
2. **Tailwind CSS** with shadcn/ui design system
3. **Framer Motion** for smooth animations
4. **Lucide Icons** for beautiful icons
5. **Dark mode support** (CSS variables configured)
6. **Path aliases** (`@/` points to `resources/js/`)
7. **Hot Module Replacement** (HMR) in development

### ✅ CSS Variables Configured

The following shadcn/ui CSS variables are available:
- `--background`, `--foreground`
- `--primary`, `--secondary`, `--accent`
- `--muted`, `--destructive`
- `--border`, `--input`, `--ring`
- `--card`, `--popover`

Use them in your components:
```tsx
<div className="bg-primary text-primary-foreground">
  Button
</div>
```

---

## 🎨 Tailwind Utilities

### Custom Border Radius
```tsx
<div className="rounded-4xl">4rem border radius</div>
<div className="rounded-6xl">3rem border radius</div>
```

### shadcn Colors
```tsx
<div className="bg-accent text-accent-foreground">Accent</div>
<div className="bg-muted text-muted-foreground">Muted</div>
<div className="border-border">Border</div>
```

---

## 🐛 Troubleshooting

### Component Not Rendering?
1. Check browser console for errors
2. Ensure `npm run dev` is running
3. Verify component is registered in `app.tsx`
4. Check `data-react-component` name matches exactly

### TypeScript Errors?
```bash
# Clear TypeScript cache
rm -rf node_modules/.vite
npm run dev
```

### Styling Issues?
1. Ensure `npm run dev` is rebuilding CSS
2. Check Tailwind config includes React files: `./resources/js/**/*.{js,jsx,ts,tsx}`
3. Verify CSS variables are loaded in `app.css`

### Framer Motion Not Animating?
Check that you're using the correct import:
```tsx
import { motion } from 'framer-motion';
```

---

## 📚 Resources

- [React Documentation](https://react.dev/)
- [TypeScript Documentation](https://www.typescriptlang.org/)
- [shadcn/ui Components](https://ui.shadcn.com/)
- [Framer Motion](https://www.framer.com/motion/)
- [Lucide Icons](https://lucide.dev/)
- [Tailwind CSS](https://tailwindcss.com/)

---

## 🔥 Next Steps

1. **Install dependencies**: `npm install`
2. **Start dev server**: `npm run dev`
3. **Start Laravel**: `php artisan serve`
4. **Visit demo**: http://localhost:8000/react-demo
5. **Create your own components** in `resources/js/components/ui/`
6. **Use in any Blade template** with the `data-react-component` attribute

---

## 💡 Tips

- Use the `cn()` utility from `@/lib/utils` to merge Tailwind classes
- All React components support className props for custom styling
- Props are passed as JSON via `data-react-props` attribute
- Dark mode works automatically with Tailwind's `dark:` prefix
- Use Framer Motion's `motion` components for animations
- Import icons from `lucide-react` package

---

**Happy Coding! 🎉**

