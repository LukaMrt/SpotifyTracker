// 📝 TYPOGRAPHY COMPONENT - Système de typographie réutilisable
import { cva, type VariantProps } from 'class-variance-authority';
import type { ReactNode, HTMLAttributes } from 'react';

import { cn } from '@/utils/cn';

// 🎨 Définition des variantes avec CVA
const typographyVariants = cva(
  // Classes de base communes
  'text-gray-900',
  {
    variants: {
      // 📏 Variantes de taille et style
      variant: {
        h1: 'scroll-m-20 text-4xl font-extrabold tracking-tight lg:text-5xl',
        h2: 'scroll-m-20 border-b pb-2 text-3xl font-semibold tracking-tight first:mt-0',
        h3: 'scroll-m-20 text-2xl font-semibold tracking-tight',
        h4: 'scroll-m-20 text-xl font-semibold tracking-tight',
        h5: 'scroll-m-20 text-lg font-semibold tracking-tight',
        h6: 'scroll-m-20 text-base font-semibold tracking-tight',
        p: 'leading-7 [&:not(:first-child)]:mt-6',
        lead: 'text-xl text-gray-600 [&:not(:first-child)]:mt-6',
        large: 'text-lg font-semibold',
        small: 'text-sm font-medium leading-none',
        muted: 'text-sm text-gray-500',
        code: 'relative rounded bg-gray-100 px-[0.3rem] py-[0.2rem] font-mono text-sm font-semibold',
        blockquote: 'mt-6 border-l-2 border-gray-300 pl-6 italic',
      },
      // 🎨 Variantes de couleur
      color: {
        default: 'text-gray-900',
        muted: 'text-gray-500',
        primary: 'text-blue-600',
        secondary: 'text-gray-600',
        success: 'text-green-600',
        warning: 'text-yellow-600',
        danger: 'text-red-600',
        spotify: 'text-spotify-green',
      },
      // 📐 Alignement du texte
      align: {
        left: 'text-left',
        center: 'text-center',
        right: 'text-right',
        justify: 'text-justify',
      },
      // ✂️ Troncature du texte
      truncate: {
        true: 'truncate',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'p',
      color: 'default',
      align: 'left',
      truncate: false,
    },
  }
);

// 🏷️ Map des variantes vers les éléments HTML
const variantElementMap = {
  h1: 'h1',
  h2: 'h2',
  h3: 'h3',
  h4: 'h4',
  h5: 'h5',
  h6: 'h6',
  p: 'p',
  lead: 'p',
  large: 'div',
  small: 'small',
  muted: 'p',
  code: 'code',
  blockquote: 'blockquote',
} as const;

// 🔗 Interface pour les props du Typography
interface TypographyProps
  extends Omit<HTMLAttributes<HTMLElement>, 'color'>,
    VariantProps<typeof typographyVariants> {
  children: ReactNode;
  as?: keyof React.JSX.IntrinsicElements;
}

// 📝 Composant Typography
export const Typography = ({
  className,
  variant = 'p',
  color,
  align,
  truncate,
  as,
  children,
  ...props
}: TypographyProps) => {
  // 🏷️ Déterminer l'élément HTML à utiliser
  const Element = as || variantElementMap[variant!] || 'div';

  return (
    <Element
      className={cn(
        typographyVariants({ variant, color, align, truncate }),
        className
      )}
      {...(props as any)}
    >
      {children}
    </Element>
  );
};

// 🚀 Composants spécialisés pour une utilisation plus simple
export const Heading1 = ({
  children,
  className,
  ...props
}: Omit<TypographyProps, 'variant'>) => (
  <Typography variant="h1" className={className} {...props}>
    {children}
  </Typography>
);

export const Heading2 = ({
  children,
  className,
  ...props
}: Omit<TypographyProps, 'variant'>) => (
  <Typography variant="h2" className={className} {...props}>
    {children}
  </Typography>
);

export const Heading3 = ({
  children,
  className,
  ...props
}: Omit<TypographyProps, 'variant'>) => (
  <Typography variant="h3" className={className} {...props}>
    {children}
  </Typography>
);

export const Paragraph = ({
  children,
  className,
  ...props
}: Omit<TypographyProps, 'variant'>) => (
  <Typography variant="p" className={className} {...props}>
    {children}
  </Typography>
);

export const Lead = ({
  children,
  className,
  ...props
}: Omit<TypographyProps, 'variant'>) => (
  <Typography variant="lead" className={className} {...props}>
    {children}
  </Typography>
);

export const Muted = ({
  children,
  className,
  ...props
}: Omit<TypographyProps, 'variant'>) => (
  <Typography variant="muted" className={className} {...props}>
    {children}
  </Typography>
);

export const Code = ({
  children,
  className,
  ...props
}: Omit<TypographyProps, 'variant'>) => (
  <Typography variant="code" className={className} {...props}>
    {children}
  </Typography>
);
