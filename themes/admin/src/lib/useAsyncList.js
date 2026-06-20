import { useEffect, useRef, useState } from "react";

/**
 * Minimal drop-in replacement for @react-stately/data's `useAsyncList`, covering
 * exactly the surface our HeroUI Autocomplete pickers use: `items`, `filterText`,
 * `setFilterText`, `isLoading`, and an async `load({ filterText, signal })` that
 * returns `{ items }`.
 *
 * Why local instead of the library: @react-stately/data is a deep transitive dep
 * (react-aria-components → HeroUI) and under this project's bundler it resolved
 * against a second React instance, so its hooks read a null dispatcher ("Cannot
 * read properties of null (reading 'useReducer')"). This implementation imports
 * only the app's single React + the caller's axios, so there is no duplicate.
 *
 * Behaviour matches the bits we rely on: loads once on mount (empty filter) and
 * reloads whenever `filterText` changes, aborting the in-flight request so a
 * slow earlier response can't overwrite a newer one.
 */
export function useAsyncList({ load }) {
    const [items, setItems] = useState([]);
    const [filterText, setFilterText] = useState("");
    const [isLoading, setIsLoading] = useState(false);

    // Keep the latest load fn without making it a reload trigger.
    const loadRef = useRef(load);
    loadRef.current = load;

    useEffect(() => {
        const controller = new AbortController();
        let active = true;
        setIsLoading(true);

        Promise.resolve(
            loadRef.current({ filterText, signal: controller.signal }),
        )
            .then((res) => {
                if (active) setItems(res?.items ?? []);
            })
            .catch(() => {
                // Aborted or failed — leave the previous items in place.
            })
            .finally(() => {
                if (active) setIsLoading(false);
            });

        return () => {
            active = false;
            controller.abort();
        };
    }, [filterText]);

    return { items, filterText, setFilterText, isLoading };
}
