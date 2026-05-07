import "./bootstrap";
import "./app.css";

import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";

import { Ziggy } from './ziggy';
import { Toast } from "@heroui/react";
import "./lib/i18n";

window.route = Ziggy.routes;
const appName =
    window.document.getElementsByTagName("title")[0]?.innerText || "Laravel";

const queryClient = new QueryClient();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob("./Pages/**/*.jsx"),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <QueryClientProvider client={queryClient}>
                <Toast.Provider /> 
                    <App {...props} />
            </QueryClientProvider>,
        );
    },
    progress: {
        color: "#4B5563",
    },
});
