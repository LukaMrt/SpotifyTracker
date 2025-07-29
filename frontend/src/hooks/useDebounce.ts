// ⏱️ USE DEBOUNCE HOOK - Hook pour débouncer les valeurs
import { useState, useEffect } from 'react';

/**
 * 🔧Hook useDebounce - Retarde la mise à jour d'une valeur
 *
 * Utile pour éviter les appels API trop fréquents lors de la saisie
 * dans un champ de recherche par exemple.
 *
 * @param value - La valeur à débouncer
 * @param delay - Le délai en millisecondes (défaut: 500ms)
 * @returns La valeur debouncée
 *
 * @example
 * ```typescript
 * const [searchTerm, setSearchTerm] = useState('')
 * const debouncedSearchTerm = useDebounce(searchTerm, 300)
 *
 * useEffect(() => {
 *   if (debouncedSearchTerm) {
 *     // Faire l'appel API seulement après 300ms d'inactivité
 *     searchApi(debouncedSearchTerm)
 *   }
 * }, [debouncedSearchTerm])
 * ```
 */
export function useDebounce<T>(value: T, delay = 500): T {
  // 🏪 État pour stocker la valeur debouncée
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    // ⏰ Définir un timer pour mettre à jour la valeur après le délai
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    // 🧹 Nettoyer le timer si la valeur change avant la fin du délai
    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
}

/**
 * 🔧 Hook useDebounceCallback - Débounce un callback
 *
 * Utile pour débouncer des fonctions plutôt que des valeurs
 *
 * @param callback - La fonction à débouncer
 * @param delay - Le délai en millisecondes
 * @param deps - Les dépendances du callback
 * @returns La fonction debouncée
 *
 * @example
 * ```typescript
 * const handleSearch = useDebounceCallback((term: string) => {
 *   searchApi(term)
 * }, 300, [])
 * ```
 */
export function useDebounceCallback<T extends (...args: any[]) => any>(
  callback: T,
  delay: number
): T {
  const [debounceTimer, setDebounceTimer] = useState<number | null>(null);

  const debouncedCallback = ((...args: Parameters<T>) => {
    // 🧹 Nettoyer le timer précédent
    if (debounceTimer) {
      clearTimeout(debounceTimer);
    }

    // ⏰ Créer un nouveau timer
    const newTimer = setTimeout(() => {
      callback(...args);
    }, delay);

    setDebounceTimer(newTimer);
  }) as T;

  // 🧹 Nettoyer le timer au démontage du composant
  useEffect(() => {
    return () => {
      if (debounceTimer) {
        clearTimeout(debounceTimer);
      }
    };
  }, [debounceTimer]);

  return debouncedCallback;
}
