// 🔄 USE TOGGLE HOOK - Hook pour gérer les états booléens
import { useState, useCallback } from 'react';

/**
 * 🔧 Hook useToggle - Simplifie la gestion des états booléens
 *
 * Fournit des méthodes pratiques pour basculer, activer et désactiver
 * un état booléen.
 *
 * @param initialValue - La valeur initiale (défaut: false)
 * @returns Tuple avec [value, { toggle, on, off, set }]
 *
 * @example
 * ```typescript
 * const [isOpen, { toggle, on, off }] = useToggle(false)
 *
 * // Basculer l'état
 * <button onClick={toggle}>Toggle</button>
 *
 * // Forcer à true
 * <button onClick={on}>Ouvrir</button>
 *
 * // Forcer à false
 * <button onClick={off}>Fermer</button>
 * ```
 */
export function useToggle(initialValue = false): [
  boolean,
  {
    toggle: () => void;
    on: () => void;
    off: () => void;
    set: (value: boolean) => void;
  },
] {
  // 🏪 État booléen
  const [value, setValue] = useState<boolean>(initialValue);

  // 🔄 Basculer l'état
  const toggle = useCallback(() => {
    setValue(current => !current);
  }, []);

  // ✅ Mettre à true
  const on = useCallback(() => {
    setValue(true);
  }, []);

  // ❌ Mettre à false
  const off = useCallback(() => {
    setValue(false);
  }, []);

  // 📝 Setter direct
  const set = useCallback((newValue: boolean) => {
    setValue(newValue);
  }, []);

  return [
    value,
    {
      toggle,
      on,
      off,
      set,
    },
  ];
}

/**
 * 🔧 Hook useMultiToggle - Gère plusieurs états booléens
 *
 * Utile pour gérer plusieurs modales, dropdowns, etc.
 *
 * @param keys - Les clés des états à gérer
 * @param initialValues - Les valeurs initiales (optionnel)
 * @returns Objet avec les valeurs et actions pour chaque clé
 *
 * @example
 * ```typescript
 * const toggles = useMultiToggle(['modal', 'dropdown', 'sidebar'])
 *
 * // Accéder aux valeurs
 * console.log(toggles.modal.value) // false
 *
 * // Basculer un état
 * toggles.modal.toggle()
 *
 * // Activer/désactiver
 * toggles.dropdown.on()
 * toggles.sidebar.off()
 * ```
 */
export function useMultiToggle<T extends string>(
  keys: readonly T[],
  initialValues?: Partial<Record<T, boolean>>
): Record<
  T,
  {
    value: boolean;
    toggle: () => void;
    on: () => void;
    off: () => void;
    set: (value: boolean) => void;
  }
> {
  // 🏪 État pour tous les toggles
  const [values, setValues] = useState<Record<T, boolean>>(() => {
    const initial = {} as Record<T, boolean>;
    keys.forEach(key => {
      initial[key] = initialValues?.[key] ?? false;
    });
    return initial;
  });

  // 🔧 Créer les actions pour chaque clé
  const actions = {} as Record<
    T,
    {
      value: boolean;
      toggle: () => void;
      on: () => void;
      off: () => void;
      set: (value: boolean) => void;
    }
  >;

  keys.forEach(key => {
    const toggle = useCallback(() => {
      setValues(current => ({
        ...current,
        [key]: !current[key],
      }));
    }, [key]);

    const on = useCallback(() => {
      setValues(current => ({
        ...current,
        [key]: true,
      }));
    }, [key]);

    const off = useCallback(() => {
      setValues(current => ({
        ...current,
        [key]: false,
      }));
    }, [key]);

    const set = useCallback(
      (value: boolean) => {
        setValues(current => ({
          ...current,
          [key]: value,
        }));
      },
      [key]
    );

    actions[key] = {
      value: values[key],
      toggle,
      on,
      off,
      set,
    };
  });

  return actions;
}

/**
 * 🔧 Hook useToggleWithPersistence - Toggle avec persistance
 *
 * Combine useToggle avec useLocalStorage pour persister l'état
 *
 * @param key - Clé pour le localStorage
 * @param initialValue - Valeur initiale
 * @returns Même API que useToggle mais persisté
 */
export function useToggleWithPersistence(
  key: string,
  initialValue = false
): [
  boolean,
  {
    toggle: () => void;
    on: () => void;
    off: () => void;
    set: (value: boolean) => void;
  },
] {
  // 🏪 État persisté dans localStorage
  const [value, setValue] = useState<boolean>(() => {
    try {
      if (typeof window !== 'undefined') {
        const item = window.localStorage.getItem(key);
        return item ? JSON.parse(item) : initialValue;
      }
      return initialValue;
    } catch {
      return initialValue;
    }
  });

  // 💾 Fonction pour sauvegarder dans localStorage
  const persistValue = useCallback(
    (newValue: boolean) => {
      setValue(newValue);
      try {
        if (typeof window !== 'undefined') {
          window.localStorage.setItem(key, JSON.stringify(newValue));
        }
      } catch (error) {
        console.warn(
          `Impossible de sauvegarder la clé "${key}" dans localStorage:`,
          error
        );
      }
    },
    [key]
  );

  const toggle = useCallback(() => {
    persistValue(!value);
  }, [value, persistValue]);

  const on = useCallback(() => {
    persistValue(true);
  }, [persistValue]);

  const off = useCallback(() => {
    persistValue(false);
  }, [persistValue]);

  const set = useCallback(
    (newValue: boolean) => {
      persistValue(newValue);
    },
    [persistValue]
  );

  return [
    value,
    {
      toggle,
      on,
      off,
      set,
    },
  ];
}
