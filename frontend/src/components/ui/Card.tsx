// 🃏 CARD COMPONENT - Composant de carte réutilisable
import { cva, type VariantProps } from 'class-variance-authority';
import type { HTMLAttributes, ReactNode } from 'react';

import { cn } from '@/utils/cn';

// 🎨 Définition des variantes avec CVA
const cardVariants = cva(
  // Classes de base communes à toutes les cartes
  'rounded-lg border bg-white text-gray-900 shadow-sm',
  {
    variants: {
      // 🎨 Variantes de style
      variant: {
        default: 'border-gray-200',
        outlined: 'border-gray-300 shadow-none',
        elevated: 'border-gray-100 shadow-lg',
        ghost: 'border-transparent shadow-none bg-transparent',
      },
      // 📏 Variantes de padding
      padding: {
        none: 'p-0',
        sm: 'p-3',
        md: 'p-4',
        lg: 'p-6',
        xl: 'p-8',
      },
    },
    defaultVariants: {
      variant: 'default',
      padding: 'md',
    },
  }
);

// 🔗 Interface pour les props de la Card
interface CardProps
  extends HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof cardVariants> {
  children: ReactNode;
  hover?: boolean;
}

// 🃏 Composant Card principal
export const Card = ({
  className,
  variant,
  padding,
  children,
  hover = false,
  ...props
}: CardProps) => {
  return (
    <div
      className={cn(
        cardVariants({ variant, padding }),
        {
          'transition-shadow hover:shadow-md': hover && variant !== 'ghost',
        },
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
};

// 📋 Composant CardHeader
interface CardHeaderProps extends HTMLAttributes<HTMLDivElement> {
  children: ReactNode;
}

export const CardHeader = ({
  className,
  children,
  ...props
}: CardHeaderProps) => {
  return (
    <div className={cn('flex flex-col space-y-1.5 pb-3', className)} {...props}>
      {children}
    </div>
  );
};

// 🏷️ Composant CardTitle
interface CardTitleProps extends HTMLAttributes<HTMLHeadingElement> {
  children: ReactNode;
  as?: 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6';
}

export const CardTitle = ({
  className,
  children,
  as: Component = 'h3',
  ...props
}: CardTitleProps) => {
  return (
    <Component
      className={cn(
        'text-lg font-semibold leading-none tracking-tight',
        className
      )}
      {...props}
    >
      {children}
    </Component>
  );
};

// 📝 Composant CardDescription
interface CardDescriptionProps extends HTMLAttributes<HTMLParagraphElement> {
  children: ReactNode;
}

export const CardDescription = ({
  className,
  children,
  ...props
}: CardDescriptionProps) => {
  return (
    <p className={cn('text-sm text-gray-500', className)} {...props}>
      {children}
    </p>
  );
};

// 📄 Composant CardContent
interface CardContentProps extends HTMLAttributes<HTMLDivElement> {
  children: ReactNode;
}

export const CardContent = ({
  className,
  children,
  ...props
}: CardContentProps) => {
  return (
    <div className={cn('pb-3', className)} {...props}>
      {children}
    </div>
  );
};

// 🦶 Composant CardFooter
interface CardFooterProps extends HTMLAttributes<HTMLDivElement> {
  children: ReactNode;
}

export const CardFooter = ({
  className,
  children,
  ...props
}: CardFooterProps) => {
  return (
    <div className={cn('flex items-center pt-3', className)} {...props}>
      {children}
    </div>
  );
};
