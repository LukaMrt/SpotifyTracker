// 💾 USE LOCAL STORAGE HOOK - Hook pour gérer le localStorage
import { useState, useCallback } from 'react';

/**
 * 🔧 Hook useLocalStorage - Synchronise un état avec le localStorage
 *
 * Permet de persister des données dans le localStorage avec une API
 * similaire à useState mais avec synchronisation automatique.
 *
 * @param key - La clé dans le localStorage
 * @param initialValue - La valeur initiale si rien n'est trouvé
 * @returns [value, setValue, removeValue] - Tuple avec la valeur, setter et remover
 *
 * @example
 * ```typescript
 * const [theme, setTheme, removeTheme] = useLocalStorage('theme', 'light')
 *
 * // Utilisation comme useState normal
 * setTheme('dark')
 *
 * // Supprimer du localStorage
 * removeTheme()
 * ```
 */
export function useLocalStorage<T>(
  key: string,
  initialValue: T
): [T, (value: T | ((prev: T) => T)) => void, () => void] {
  // 🏪 État pour stocker la valeur
  const [storedValue, setStoredValue] = useState<T>(() => {
    try {
      // 🔍 Essayer de récupérer depuis le localStorage
      if (typeof window !== 'undefined') {
        const item = window.localStorage.getItem(key);
        return item ? JSON.parse(item) : initialValue;
      }
      return initialValue;
    } catch (error) {
      console.warn(
        `Erreur lors de la lecture du localStorage pour la clé "${key}":`,
        error
      );
      return initialValue;
    }
  });

  // 💾 Fonction pour sauvegarder une valeur
  const setValue = useCallback(
    (value: T | ((prev: T) => T)) => {
      try {
        // 📝 Calculer la nouvelle valeur (si c'est une fonction)
        const valueToStore =
          value instanceof Function ? value(storedValue) : value;

        // 🏪 Mettre à jour l'état
        setStoredValue(valueToStore);

        // 💾 Sauvegarder dans le localStorage
        if (typeof window !== 'undefined') {
          window.localStorage.setItem(key, JSON.stringify(valueToStore));
        }
      } catch (error) {
        console.error(
          `Erreur lors de la sauvegarde dans le localStorage pour la clé "${key}":`,
          error
        );
      }
    },
    [key, storedValue]
  );

  // 🗑️ Fonction pour supprimer la valeur
  const removeValue = useCallback(() => {
    try {
      // 🏪 Remettre la valeur initiale dans l'état
      setStoredValue(initialValue);

      // 🗑️ Supprimer du localStorage
      if (typeof window !== 'undefined') {
        window.localStorage.removeItem(key);
      }
    } catch (error) {
      console.error(
        `Erreur lors de la suppression du localStorage pour la clé "${key}":`,
        error
      );
    }
  }, [key, initialValue]);

  return [storedValue, setValue, removeValue];
}

/**
 * 🔧 Hook useLocalStorageState - Version simplifiée qui retourne juste [value, setValue]
 *
 * @param key - La clé dans le localStorage
 * @param initialValue - La valeur initiale
 * @returns [value, setValue] - Tuple comme useState
 */
export function useLocalStorageState<T>(
  key: string,
  initialValue: T
): [T, (value: T | ((prev: T) => T)) => void] {
  const [storedValue, setValue] = useLocalStorage(key, initialValue);
  return [storedValue, setValue];
}

/**
 * 🔧 Hook useSessionStorage - Version pour sessionStorage
 *
 * Même API que useLocalStorage mais utilise sessionStorage
 * (données supprimées à la fermeture de l'onglet)
 */
export function useSessionStorage<T>(
  key: string,
  initialValue: T
): [T, (value: T | ((prev: T) => T)) => void, () => void] {
  const [storedValue, setStoredValue] = useState<T>(() => {
    try {
      if (typeof window !== 'undefined') {
        const item = window.sessionStorage.getItem(key);
        return item ? JSON.parse(item) : initialValue;
      }
      return initialValue;
    } catch (error) {
      console.warn(
        `Erreur lors de la lecture du sessionStorage pour la clé "${key}":`,
        error
      );
      return initialValue;
    }
  });

  const setValue = useCallback(
    (value: T | ((prev: T) => T)) => {
      try {
        const valueToStore =
          value instanceof Function ? value(storedValue) : value;
        setStoredValue(valueToStore);
        if (typeof window !== 'undefined') {
          window.sessionStorage.setItem(key, JSON.stringify(valueToStore));
        }
      } catch (error) {
        console.error(
          `Erreur lors de la sauvegarde dans le sessionStorage pour la clé "${key}":`,
          error
        );
      }
    },
    [key, storedValue]
  );

  const removeValue = useCallback(() => {
    try {
      setStoredValue(initialValue);
      if (typeof window !== 'undefined') {
        window.sessionStorage.removeItem(key);
      }
    } catch (error) {
      console.error(
        `Erreur lors de la suppression du sessionStorage pour la clé "${key}":`,
        error
      );
    }
  }, [key, initialValue]);

  return [storedValue, setValue, removeValue];
}
