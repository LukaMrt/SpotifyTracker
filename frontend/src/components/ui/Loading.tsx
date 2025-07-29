// ⏳ LOADING COMPONENT - Composants de chargement réutilisables
import { cva, type VariantProps } from 'class-variance-authority';
import type { HTMLAttributes } from 'react';

import { cn } from '@/utils/cn';

// 🎨 Définition des variantes pour le Spinner
const spinnerVariants = cva(
  // Classes de base communes à tous les spinners
  'animate-spin rounded-full border-2 border-transparent',
  {
    variants: {
      // 📏 Variantes de taille
      size: {
        xs: 'h-3 w-3 border-[1px]',
        sm: 'h-4 w-4',
        md: 'h-6 w-6',
        lg: 'h-8 w-8',
        xl: 'h-12 w-12 border-4',
      },
      // 🎨 Variantes de couleur
      color: {
        primary: 'border-t-blue-600 border-r-blue-600',
        secondary: 'border-t-gray-600 border-r-gray-600',
        success: 'border-t-green-600 border-r-green-600',
        warning: 'border-t-yellow-600 border-r-yellow-600',
        danger: 'border-t-red-600 border-r-red-600',
        spotify: 'border-t-spotify-green border-r-spotify-green',
        white: 'border-t-white border-r-white',
      },
    },
    defaultVariants: {
      size: 'md',
      color: 'primary',
    },
  }
);

// 🔗 Interface pour les props du Spinner
interface SpinnerProps
  extends Omit<HTMLAttributes<HTMLDivElement>, 'color'>,
    VariantProps<typeof spinnerVariants> {}

// ⚡ Composant Spinner
export const Spinner = ({ className, size, color, ...props }: SpinnerProps) => {
  return (
    <div
      className={cn(spinnerVariants({ size, color }), className)}
      role="status"
      aria-label="Chargement"
      {...props}
    />
  );
};

// 🎯 Composant LoadingOverlay pour les overlays de chargement
interface LoadingOverlayProps extends HTMLAttributes<HTMLDivElement> {
  visible: boolean;
  text?: string;
  spinnerSize?: VariantProps<typeof spinnerVariants>['size'];
  spinnerColor?: VariantProps<typeof spinnerVariants>['color'];
}

export const LoadingOverlay = ({
  visible,
  text = 'Chargement...',
  spinnerSize = 'lg',
  spinnerColor = 'primary',
  className,
  children,
  ...props
}: LoadingOverlayProps) => {
  if (!visible) return <>{children}</>;

  return (
    <div className={cn('relative', className)} {...props}>
      {/* Contenu original (flouté) */}
      <div className={visible ? 'blur-sm opacity-50' : ''}>{children}</div>

      {/* Overlay de chargement */}
      {visible && (
        <div className="absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-sm">
          <div className="flex flex-col items-center space-y-3">
            <Spinner size={spinnerSize} color={spinnerColor} />
            {text && (
              <p className="text-sm font-medium text-gray-700">{text}</p>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

// 📄 Composant LoadingPage pour les pages de chargement complètes
interface LoadingPageProps {
  text?: string;
  description?: string;
  spinnerSize?: VariantProps<typeof spinnerVariants>['size'];
  spinnerColor?: VariantProps<typeof spinnerVariants>['color'];
}

export const LoadingPage = ({
  text = 'Chargement...',
  description,
  spinnerSize = 'xl',
  spinnerColor = 'primary',
}: LoadingPageProps) => {
  return (
    <div className="flex min-h-screen items-center justify-center">
      <div className="flex flex-col items-center space-y-4 text-center">
        <Spinner size={spinnerSize} color={spinnerColor} />
        <div className="space-y-2">
          <h2 className="text-lg font-semibold text-gray-900">{text}</h2>
          {description && (
            <p className="text-sm text-gray-500">{description}</p>
          )}
        </div>
      </div>
    </div>
  );
};

// 🔄 Composant LoadingButton pour les boutons avec état de chargement
interface LoadingButtonProps extends HTMLAttributes<HTMLButtonElement> {
  loading: boolean;
  loadingText?: string;
  spinnerSize?: VariantProps<typeof spinnerVariants>['size'];
  spinnerColor?: VariantProps<typeof spinnerVariants>['color'];
  disabled?: boolean;
}

export const LoadingButton = ({
  loading,
  loadingText,
  spinnerSize = 'sm',
  spinnerColor = 'white',
  disabled,
  children,
  className,
  ...props
}: LoadingButtonProps) => {
  return (
    <button
      className={cn(
        'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 font-medium transition-colors',
        'bg-blue-600 text-white hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2',
        'disabled:cursor-not-allowed disabled:opacity-50',
        className
      )}
      disabled={disabled || loading}
      {...props}
    >
      {loading && <Spinner size={spinnerSize} color={spinnerColor} />}
      <span>{loading && loadingText ? loadingText : children}</span>
    </button>
  );
};

// 💀 Composant Skeleton pour les placeholders
interface SkeletonProps extends HTMLAttributes<HTMLDivElement> {
  lines?: number;
  height?: string;
}

export const Skeleton = ({
  lines = 1,
  height,
  className,
  ...props
}: SkeletonProps) => {
  if (lines === 1) {
    return (
      <div
        className={cn(
          'animate-pulse rounded bg-gray-200',
          height ? `h-[${height}]` : 'h-4',
          className
        )}
        {...props}
      />
    );
  }

  return (
    <div className={cn('space-y-2', className)} {...props}>
      {Array.from({ length: lines }).map((_, i) => (
        <div
          key={i}
          className={cn(
            'animate-pulse rounded bg-gray-200',
            height ? `h-[${height}]` : 'h-4',
            i === lines - 1 ? 'w-3/4' : 'w-full'
          )}
        />
      ))}
    </div>
  );
};
