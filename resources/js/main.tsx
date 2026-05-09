import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, Routes, Route } from "react-router";
import "./bootstrap";
import "./index.css";
import App from "./App";
import Page from "./pages/page";
import DeleteAccount from "./pages/DeleteAccount";

createRoot(document.getElementById("root")!).render(
    <StrictMode>
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<App />} />
                <Route path="/pages/:slug" element={<Page />} />
                <Route path="/delete-account" element={<DeleteAccount />} />
            </Routes>
        </BrowserRouter>
    </StrictMode>,
);
