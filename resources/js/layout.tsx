import { useState } from "react";
import { Button } from "./components/Button";

export default function Layout({ children }: { children: React.ReactNode }) {
    const [view, setView] = useState<"home" | "tracking">("home");
    const [trackingNumber, setTrackingNumber] = useState("");
    const [activeTrackingId, setActiveTrackingId] = useState<string | null>(
        null,
    );

    const goHome = () => {
        setView("home");
        setActiveTrackingId(null);
        setTrackingNumber("");
        try {
            window.history.pushState({}, "", window.location.pathname);
        } catch (e) {
            console.warn("History pushState failed:", e);
        }
    };

    const Logo = () => (
        <div className="flex items-center gap-3 group cursor-pointer">
            <div className="relative w-10 h-10 md:w-12 md:h-12">
                <img
                    src="/3d-logo-compressed.webp"
                    className="w-full h-full"
                    alt="logo"
                />
                <div className="absolute inset-0"></div>
            </div>
        </div>
    );

    return (
        <div className="min-h-screen bg-background text-text-main overflow-x-hidden relative">
            <nav className="container mx-auto px-6 py-8 flex items-center justify-between relative z-50">
                <div onClick={goHome}>
                    <Logo />
                </div>
                <div className="hidden md:flex gap-8 items-center">
                    <a
                        href="#"
                        className="hover:text-brand-primary transition-colors font-medium"
                        onClick={(e) => {
                            e.preventDefault();
                            goHome();
                        }}
                    >
                        الرئيسية
                    </a>
                    <a
                        href="#"
                        className="hover:text-brand-primary transition-colors font-medium"
                    >
                        خدماتنا
                    </a>
                    <a
                        href="#"
                        className="hover:text-brand-primary transition-colors font-medium"
                    >
                        المتاجر المدعومة
                    </a>
                    <Button variant="outline">تسجيل الدخول</Button>
                </div>
                <div className="md:hidden">
                    <button className="text-text-main p-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-8 w-8"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M4 6h16M4 12h16m-7 6h7"
                            />
                        </svg>
                    </button>
                </div>
            </nav>
            {children}

            <footer className="container mx-auto px-6 py-12 border-t border-border-subtle mt-24 relative z-10">
                <div className="grid md:grid-cols-4 gap-12 text-right">
                    <div className="space-y-4">
                        <div onClick={goHome}>
                            <Logo />
                        </div>
                        <p className="text-text-dimmed">
                            بوابتك للتسوق العالمي بلا حدود وبأقصى سرعة ممكنة عبر
                            تطبيقنا الذكي.
                        </p>
                    </div>
                    <div className="space-y-4">
                        <h4 className="font-bold text-lg">الروابط</h4>
                        <ul className="space-y-2 text-text-dimmed">
                            <li>
                                <a
                                    href="#"
                                    className="hover:text-brand-primary"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        goHome();
                                    }}
                                >
                                    الرئيسية
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    className="hover:text-brand-primary"
                                >
                                    تحميل التطبيق
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    className="hover:text-brand-primary"
                                >
                                    الأسئلة الشائعة
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div className="space-y-4">
                        <h4 className="font-bold text-lg">القانونية</h4>
                        <ul className="space-y-2 text-text-dimmed">
                            <li>
                                <a
                                    href="/pages/privacy-policy"
                                    className="hover:text-brand-primary"
                                >
                                    سياسة الخصوصية
                                </a>
                            </li>
                            <li>
                                <a
                                    href="/pages/terms-and-conditions"
                                    className="hover:text-brand-primary"
                                >
                                    الشروط والأحكام
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div className="space-y-4">
                        <h4 className="font-bold text-lg">تابعنا</h4>
                        <div className="flex justify-end gap-4">
                            <div className="w-10 h-10 bg-brand-secondary rounded-full flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors cursor-pointer">
                                T
                            </div>
                            <div className="w-10 h-10 bg-brand-secondary rounded-full flex items-center justify-center hover:bg-blue-400 hover:text-white transition-colors cursor-pointer">
                                X
                            </div>
                            <div className="w-10 h-10 bg-brand-secondary rounded-full flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors cursor-pointer">
                                I
                            </div>
                        </div>
                    </div>
                </div>
                <div className="mt-12 pt-8 border-t border-border-subtle text-center text-text-dimmed text-sm">
                    جميع الحقوق محفوظة &copy; {new Date().getFullYear()} لمنصة
                    طلبي
                </div>
            </footer>
        </div>
    );
}
