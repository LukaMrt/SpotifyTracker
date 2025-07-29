// 🧭 NAVIGATION COMPONENT - Composants de navigation réutilisables
import { cva, type VariantProps } from 'class-variance-authority';
import type { ReactNode, HTMLAttributes } from 'react';
import { NavLink, type NavLinkProps } from 'react-router-dom';

import { cn } from '@/utils/cn';

// 🎨 Définition des variantes pour la NavigationBar
const navBarVariants = cva(
  // Classes de base communes
  'sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60',
  {
    variants: {
      variant: {
        default: 'border-gray-200',
        transparent: 'border-transparent bg-transparent backdrop-blur-none',
        dark: 'border-gray-800 bg-gray-900/95 text-white',
      },
      size: {
        sm: 'h-12',
        md: 'h-16',
        lg: 'h-20',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  }
);

// 🔗 Interface pour les props de la NavigationBar
interface NavigationBarProps
  extends HTMLAttributes<HTMLElement>,
    VariantProps<typeof navBarVariants> {
  children: ReactNode;
}

// 🧭 Composant NavigationBar principal
export const NavigationBar = ({
  className,
  variant,
  size,
  children,
  ...props
}: NavigationBarProps) => {
  return (
    <nav
      className={cn(navBarVariants({ variant, size }), className)}
      {...props}
    >
      <div className="container mx-auto flex h-full items-center justify-between px-4">
        {children}
      </div>
    </nav>
  );
};

// 🏠 Composant NavBrand pour le logo/titre
interface NavBrandProps extends HTMLAttributes<HTMLDivElement> {
  children: ReactNode;
  href?: string;
}

export const NavBrand = ({
  className,
  children,
  href = '/',
  ...props
}: NavBrandProps) => {
  const content = (
    <div
      className={cn('flex items-center space-x-2 text-xl font-bold', className)}
      {...props}
    >
      {children}
    </div>
  );

  if (href) {
    return (
      <NavLink to={href} className="no-underline">
        {content}
      </NavLink>
    );
  }

  return content;
};

// 📝 Composant NavMenu pour la liste des liens
interface NavMenuProps extends HTMLAttributes<HTMLUListElement> {
  children: ReactNode;
}

export const NavMenu = ({ className, children, ...props }: NavMenuProps) => {
  return (
    <ul className={cn('flex items-center space-x-1', className)} {...props}>
      {children}
    </ul>
  );
};

// 🎨 Définition des variantes pour les NavItem
const navItemVariants = cva(
  // Classes de base communes
  'inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      variant: {
        default:
          'text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus-visible:ring-gray-400',
        ghost: 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
        active: 'bg-gray-100 text-gray-900',
        spotify:
          'text-gray-700 hover:bg-spotify-green/10 hover:text-spotify-green',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
);

// 🔗 Interface pour les props du NavItem
interface NavItemProps
  extends Omit<NavLinkProps, 'className'>,
    VariantProps<typeof navItemVariants> {
  children: ReactNode;
  className?: string;
  exactMatch?: boolean;
}

// 📋 Composant NavItem
export const NavItem = ({
  className,
  variant,
  children,
  exactMatch = false,
  ...props
}: NavItemProps) => {
  return (
    <li>
      <NavLink
        className={({ isActive }) =>
          cn(
            navItemVariants({
              variant: isActive ? 'active' : variant,
            }),
            className
          )
        }
        end={exactMatch}
        {...props}
      >
        {children}
      </NavLink>
    </li>
  );
};

// 🍔 Composant MobileMenuButton pour le menu hamburger
interface MobileMenuButtonProps extends HTMLAttributes<HTMLButtonElement> {
  isOpen: boolean;
  onToggle: () => void;
}

export const MobileMenuButton = ({
  isOpen,
  onToggle,
  className,
  ...props
}: MobileMenuButtonProps) => {
  return (
    <button
      className={cn(
        'inline-flex items-center justify-center rounded-md p-2 text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500 md:hidden',
        className
      )}
      onClick={onToggle}
      aria-expanded={isOpen}
      aria-label="Menu principal"
      {...props}
    >
      {/* Icône hamburger */}
      <svg
        className={cn('h-6 w-6 transition-transform', {
          'rotate-45': isOpen,
        })}
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        {isOpen ? (
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M6 18L18 6M6 6l12 12"
          />
        ) : (
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M4 6h16M4 12h16M4 18h16"
          />
        )}
      </svg>
    </button>
  );
};

// 📱 Composant MobileMenu pour le menu mobile
interface MobileMenuProps extends HTMLAttributes<HTMLDivElement> {
  children: ReactNode;
  isOpen: boolean;
}

export const MobileMenu = ({
  children,
  isOpen,
  className,
  ...props
}: MobileMenuProps) => {
  if (!isOpen) return null;

  return (
    <div
      className={cn(
        'absolute left-0 right-0 top-full border-t border-gray-200 bg-white shadow-lg md:hidden',
        className
      )}
      {...props}
    >
      <div className="space-y-1 px-4 py-3">{children}</div>
    </div>
  );
};

// 🥾 Composant Breadcrumb pour le fil d'Ariane
interface BreadcrumbProps extends HTMLAttributes<HTMLElement> {
  children: ReactNode;
}

export const Breadcrumb = ({
  className,
  children,
  ...props
}: BreadcrumbProps) => {
  return (
    <nav
      className={cn(
        'flex items-center space-x-1 text-sm text-gray-500',
        className
      )}
      aria-label="Fil d'Ariane"
      {...props}
    >
      {children}
    </nav>
  );
};

// 🔗 Composant BreadcrumbItem
interface BreadcrumbItemProps {
  children: ReactNode;
  href?: string;
  isLast?: boolean;
  className?: string;
}

export const BreadcrumbItem = ({
  children,
  href,
  isLast = false,
  className,
}: BreadcrumbItemProps) => {
  const content = (
    <span
      className={cn(
        isLast ? 'font-medium text-gray-900' : 'hover:text-gray-700',
        className
      )}
    >
      {children}
    </span>
  );

  return (
    <div className="flex items-center space-x-1">
      {href && !isLast ? (
        <NavLink to={href} className="hover:underline">
          {content}
        </NavLink>
      ) : (
        content
      )}
      {!isLast && (
        <svg
          className="h-4 w-4 text-gray-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M9 5l7 7-7 7"
          />
        </svg>
      )}
    </div>
  );
};
