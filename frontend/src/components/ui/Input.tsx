// 📝 INPUT COMPONENT - Composant de saisie réutilisable
import { cva, type VariantProps } from 'class-variance-authority';
import { forwardRef, type InputHTMLAttributes, type ReactNode } from 'react';

import { cn } from '@/utils/cn';

// 🎨 Définition des variantes avec CVA
const inputVariants = cva(
  // Classes de base communes à tous les inputs
  'flex w-full rounded-lg border bg-white px-3 py-2 text-base transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
  {
    variants: {
      // 🎨 Variantes de style
      variant: {
        default: 'border-gray-300 focus-visible:ring-blue-500',
        error: 'border-red-500 focus-visible:ring-red-500',
        success: 'border-green-500 focus-visible:ring-green-500',
      },
      // 📏 Variantes de taille
      size: {
        sm: 'h-8 px-2 text-sm',
        md: 'h-10 px-3 text-base',
        lg: 'h-12 px-4 text-lg',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  }
);

// 🔗 Interface pour les props de l'input
interface InputProps
  extends Omit<InputHTMLAttributes<HTMLInputElement>, 'size'>,
    VariantProps<typeof inputVariants> {
  label?: string;
  error?: string;
  helper?: string;
  leftIcon?: ReactNode;
  rightIcon?: ReactNode;
  leftAddon?: ReactNode;
  rightAddon?: ReactNode;
}

// 📝 Composant Input avec forwardRef pour les refs
export const Input = forwardRef<HTMLInputElement, InputProps>(
  (
    {
      className,
      variant,
      size,
      type = 'text',
      label,
      error,
      helper,
      leftIcon,
      rightIcon,
      leftAddon,
      rightAddon,
      id,
      ...props
    },
    ref
  ) => {
    // 🔍 Déterminer la variante basée sur l'état d'erreur
    const inputVariant = error ? 'error' : variant;

    // 🆔 Générer un ID unique si non fourni
    const inputId = id || `input-${Math.random().toString(36).substr(2, 9)}`;

    return (
      <div className="w-full">
        {/* 🏷️ Label */}
        {label && (
          <label
            htmlFor={inputId}
            className="mb-2 block text-sm font-medium text-gray-700"
          >
            {label}
          </label>
        )}

        {/* 📦 Container principal */}
        <div className="relative">
          {/* 👈 Addon gauche */}
          {leftAddon && (
            <div className="absolute left-0 top-0 flex h-full items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3">
              <span className="text-gray-500">{leftAddon}</span>
            </div>
          )}

          {/* 👈 Icône gauche */}
          {leftIcon && !leftAddon && (
            <div className="absolute left-3 top-1/2 -translate-y-1/2">
              <span className="text-gray-400">{leftIcon}</span>
            </div>
          )}

          {/* 📝 Input principal */}
          <input
            ref={ref}
            id={inputId}
            type={type}
            className={cn(
              inputVariants({ variant: inputVariant, size }),
              {
                'pl-10': leftIcon && !leftAddon,
                'pr-10': rightIcon && !rightAddon,
                'pl-16': leftAddon,
                'pr-16': rightAddon,
              },
              className
            )}
            {...props}
          />

          {/* 👉 Icône droite */}
          {rightIcon && !rightAddon && (
            <div className="absolute right-3 top-1/2 -translate-y-1/2">
              <span className="text-gray-400">{rightIcon}</span>
            </div>
          )}

          {/* 👉 Addon droite */}
          {rightAddon && (
            <div className="absolute right-0 top-0 flex h-full items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-3">
              <span className="text-gray-500">{rightAddon}</span>
            </div>
          )}
        </div>

        {/* 💬 Messages d'aide et d'erreur */}
        {(error || helper) && (
          <div className="mt-1 text-sm">
            {error ? (
              <p className="text-red-600">{error}</p>
            ) : (
              <p className="text-gray-500">{helper}</p>
            )}
          </div>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';
