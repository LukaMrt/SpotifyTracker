// 🔘 BUTTON COMPONENT - Composant de base réutilisable
import { cva, type VariantProps } from 'class-variance-authority';
import type { ButtonHTMLAttributes, ReactNode } from 'react';

import { cn } from '@/utils/cn';

// 🎨 Définition des variantes avec CVA (Class Variance Authority)
const buttonVariants = cva(
  // Classes de base communes à tous les boutons
  'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      // 🎨 Variantes de style
      variant: {
        primary:
          'bg-blue-600 text-white hover:bg-blue-700 focus-visible:ring-blue-500',
        secondary:
          'bg-gray-100 text-gray-900 hover:bg-gray-200 focus-visible:ring-gray-500',
        danger:
          'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500',
        ghost: 'text-gray-700 hover:bg-gray-100 focus-visible:ring-gray-500',
        spotify:
          'bg-spotify-green text-white hover:bg-green-600 focus-visible:ring-green-500',
      },
      // 📏 Variantes de taille
      size: {
        sm: 'h-8 px-3 text-sm',
        md: 'h-10 px-4 text-base',
        lg: 'h-12 px-6 text-lg',
      },
      // 📱 Bouton full-width
      fullWidth: {
        true: 'w-full',
        false: 'w-auto',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'md',
      fullWidth: false,
    },
  }
);

// 🔗 Interface pour les props du bouton
interface ButtonProps
  extends ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  children: ReactNode;
  loading?: boolean;
  leftIcon?: ReactNode;
  rightIcon?: ReactNode;
  asChild?: boolean;
}

// 🔘 Composant Button
export const Button = ({
  className,
  variant,
  size,
  fullWidth,
  children,
  loading = false,
  leftIcon,
  rightIcon,
  disabled,
  asChild,
  ...props
}: ButtonProps) => {
  const buttonClass = cn(
    buttonVariants({ variant, size, fullWidth, className })
  );

  // Si asChild est vrai, on retourne juste les enfants avec les classes appliquées
  if (asChild) {
    return children;
  }

  return (
    <button className={buttonClass} disabled={disabled || loading} {...props}>
      {/* 🔄 Indicateur de chargement */}
      {loading && (
        <div className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
      )}

      {/* 👈 Icône gauche */}
      {!loading && leftIcon && (
        <span className="flex-shrink-0">{leftIcon}</span>
      )}

      {/* 📝 Contenu du bouton */}
      <span>{children}</span>

      {/* 👉 Icône droite */}
      {!loading && rightIcon && (
        <span className="flex-shrink-0">{rightIcon}</span>
      )}
    </button>
  );
};
