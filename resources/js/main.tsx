import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, Routes, Route } from "react-router";
import "./bootstrap";
import "./index.css";
import App from "./App";
import Page from "./pages/page";

createRoot(document.getElementById("root")!).render(
    <StrictMode>
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<App />} />
                <Route path="/pages/:slug" element={<Page />} />
            </Routes>
        </BrowserRouter>
    </StrictMode>,
);
