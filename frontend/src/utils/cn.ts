// 🎨 UTILITAIRE CN - Fusion intelligente des classes CSS
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * 🔧 Fonction utilitaire pour combiner et fusionner les classes CSS
 *
 * Cette fonction combine clsx (pour la logique conditionnelle)
 * et tailwind-merge (pour éviter les conflits de classes Tailwind)
 *
 * @param inputs - Classes CSS à combiner
 * @returns String des classes CSS fusionnées
 */
export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}
