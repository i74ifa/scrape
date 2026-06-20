import { Link, usePage, router } from "@inertiajs/react";
import { useEffect, useState, memo } from "react";
import { useTranslation } from "react-i18next";
import { initAdminFcm } from "@/lib/fcm";
import LanguageSwitcher from "@/Components/LanguageSwitcher";
import {
    LayoutDashboard,
    Users,
    Search,
    Package,
    ShoppingCart,
    Receipt,
    Store,
    LogOut,
    Settings,
    BarChart3,
    Menu as MenuIcon,
    X,
    Boxes,
    FolderTree,
    Award,
    Tags,
} from "lucide-react";
import {
    Dropdown,
    DropdownTrigger,
    DropdownPopover,
    DropdownMenu,
    DropdownItem,
    Avatar,
    AvatarFallback,
} from "@heroui/react";
import PasskeyPrompt from "@/Components/PasskeyPrompt";

/* ── Child link inside the dropdown ───────────────────── */
const ChildLink = memo(function ChildLink({ child, active, onSelect }) {
    return (
        <Link
            href={child.href}
            onClick={onSelect}
            className={`flex mb-1 items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150 group/child ${
                active
                    ? "bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"
                    : "text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-black dark:hover:text-white"
            }`}
        >
            <span
                className={`w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-colors duration-150 ${
                    active
                        ? "bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400"
                        : "bg-zinc-100 dark:bg-zinc-800 text-zinc-500 group-hover/child:bg-zinc-200 dark:group-hover/child:bg-zinc-700 group-hover/child:text-zinc-700 dark:group-hover/child:text-zinc-200"
                }`}
            >
                <child.icon className="w-4 h-4" />
            </span>
            {child.name}
        </Link>
    );
});

/* ── Main layout ─────────────────────────────────────────────────────────── */
export default function AdminLayout({
    children,
    title,
    fullWidth = false,
    maxWidth = "max-w-375",
}) {
    const { t } = useTranslation();
    const { auth, fcm, locale } = usePage().props;
    const user = auth?.user;

    const [mobileOpen, setMobileOpen] = useState(false);

    useEffect(() => {
        if (!user || !fcm?.vapid_key) return;
        initAdminFcm({ vapidKey: fcm.vapid_key });
    }, [user?.id, fcm?.vapid_key]);

    useEffect(() => {
        document.body.style.overflow = mobileOpen ? "hidden" : "";
        return () => { document.body.style.overflow = ""; };
    }, [mobileOpen]);

    const handleLogout = () => {
        router.post("/logout");
    };

    const closeMobile = () => setMobileOpen(false);

    const navItems = [
        {
            name: "لوحة التحكم",
            icon: LayoutDashboard,
            href: "/admin",
            children: [
                { name: "نظرة عامة", icon: LayoutDashboard, href: "/admin" },
                { name: "التقارير", icon: BarChart3, href: "/admin/reports" },
            ],
        },
        {
            name: "المتجر",
            icon: Store,
            href: "/admin/products",
            children: [
                { name: "المنتجات", icon: Package, href: "/admin/products" },
                { name: "المنصات", icon: Store, href: "/admin/platforms" },
            ],
        },
        {
            name: "الطلبات",
            icon: ShoppingCart,
            href: "/admin/orders",
            children: [
                { name: "الطلبات", icon: ShoppingCart, href: "/admin/orders" },
                { name: "طلبات الدفع", icon: Receipt, href: "/admin/checkout-orders" },
            ],
        },
        {
            name: "الكتالوج",
            icon: Boxes,
            href: "/admin/catalog/categories",
            children: [
                { name: "منتجات الكتالوج", icon: Package, href: "/admin/catalog/products" },
                { name: "التصنيفات", icon: FolderTree, href: "/admin/catalog/categories" },
                { name: "العلامات التجارية", icon: Award, href: "/admin/catalog/brands" },
                { name: "الخصائص", icon: Tags, href: "/admin/catalog/attributes" },
            ],
        },
        {
            name: "الإعدادات",
            icon: Settings,
            href: "/admin/settings",
            children: [
                { name: "المستخدمون", icon: Users, href: "/admin/users" },
                { name: "الإعدادات", icon: Settings, href: "/admin/settings" },
            ],
        },
    ];

    const currentPath = typeof window !== "undefined" ? window.location.pathname : "";
    const isActive = (href) => {
        if (href === "/admin") return currentPath === "/admin";
        return currentPath.startsWith(href);
    };

    return (
        <div className="min-h-screen bg-[#F2F2F7] dark:bg-[#000000] transition-colors duration-500 relative overflow-hidden">
            <LanguageSwitcher locale={locale} />
            <PasskeyPrompt />

            {/* Glowing background orbs */}
            <div className="pointer-events-none fixed inset-0 z-0 overflow-hidden">
                <div className="absolute -top-32 -left-32 w-120 h-120 rounded-full bg-sky-400/20 dark:bg-sky-500/10 blur-3xl animate-float1" />
                <div className="absolute top-1/3 -right-24 w-80 h-80 rounded-full bg-sky-400/20 dark:bg-sky-500/10 blur-3xl animate-float2" />
                <div className="absolute -bottom-20 left-1/3 w-72 h-72 rounded-full bg-sky-400/20 dark:bg-sky-500/10 blur-3xl animate-float3" />
                <div className="absolute top-1/2 left-1/4 w-64 h-64 rounded-full bg-sky-400/20 dark:bg-sky-500/10 blur-3xl animate-float4" />
            </div>

            <div className="flex flex-col h-screen overflow-hidden relative z-10">
                {/* ── Navbar ───────────────────────────────────────────────── */}
                <nav
                    className="bg-white/80 dark:bg-zinc-900/50 backdrop-blur-xl saturate-150 h-20 flex items-center px-6 shrink-0 relative"
                    style={{ zIndex: 100 }}
                >
                    {/* Mobile menu button */}
                    <button
                        type="button"
                        onClick={() => setMobileOpen(true)}
                        className="sm:hidden flex items-center justify-center w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 mr-3"
                        aria-label="Open menu"
                    >
                        <MenuIcon className="w-5 h-5" />
                    </button>

                    {/* Logo */}
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-black dark:bg-white rounded-2xl flex items-center justify-center shadow-lg">
                            <Store className="text-white dark:text-black w-6 h-6" />
                        </div>
                        <p className="font-bold text-xl tracking-tight leading-none hidden sm:block text-black dark:text-white">
                            <Link href="/admin">Talabye</Link>
                        </p>
                    </div>

                    {/* ── Desktop nav tabs with hover dropdown ─────────────────────────── */}
                    <div className="hidden lg:flex flex-1 justify-center items-center px-4">
                        <div className="flex items-center gap-1 h-12 bg-zinc-100 dark:bg-zinc-800/50 rounded-2xl p-1">
                            {navItems.map((item) => {
                                const isItemActive = item.children.some(c => isActive(c.href));
                                return (
                                    <div key={item.name} className="relative group">
                                        <Link
                                            href={item.href}
                                            className={`flex items-center gap-2 h-10 px-4 rounded-xl text-sm font-semibold transition-all ${
                                                isItemActive
                                                    ? "bg-white dark:bg-zinc-700 text-blue-600 dark:text-blue-400 shadow-sm"
                                                    : "text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                                            }`}
                                        >
                                            <item.icon className="w-4 h-4" />
                                            <span>{item.name}</span>
                                        </Link>
                                        {/* Hover dropdown panel */}
                                        <div className="absolute top-full right-0 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-500 w-56">
                                            <div className="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-700 p-2 space-y-1">
                                                {item.children.map((child) => (
                                                    <Link
                                                        key={child.href}
                                                        href={child.href}
                                                        className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
                                                            isActive(child.href)
                                                                ? "bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"
                                                                : "text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                        }`}
                                                    >
                                                        <child.icon className="w-4 h-4" />
                                                        {child.name}
                                                    </Link>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* ── Medium screen: visible button list ─────────────────────────── */}
                    <div className="hidden md:flex lg:hidden flex-1 justify-center items-center px-2">
                        <div className="flex items-center gap-1 h-12 bg-zinc-100 dark:bg-zinc-800/50 rounded-2xl p-1 overflow-x-auto">
                            {navItems.map((item) => {
                                const isItemActive = item.children.some(c => isActive(c.href));
                                return (
                                    <Link
                                        key={item.name}
                                        href={item.href}
                                        className={`flex items-center gap-2 h-10 px-3 rounded-xl text-sm font-semibold transition-all whitespace-nowrap ${
                                            isItemActive
                                                ? "bg-white dark:bg-zinc-700 text-blue-600 dark:text-blue-400 shadow-sm"
                                                : "text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                                        }`}
                                    >
                                        <item.icon className="w-4 h-4" />
                                        <span>{item.name}</span>
                                    </Link>
                                );
                            })}
                        </div>
                    </div>

                    {/* Right side: search + avatar */}
                    <div className="flex items-center gap-4 ml-auto">
                        <div className="relative hidden sm:block">
                            <Search
                                size={16}
                                className="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"
                            />
                            <input
                                type="search"
                                placeholder="بحث..."
                                className="h-12 w-64 pl-9 pr-4 rounded-xl bg-zinc-200/50 dark:bg-zinc-800/50 text-sm border-none outline-none text-zinc-700 dark:text-zinc-300 placeholder:text-zinc-400"
                            />
                        </div>

                        <Dropdown>
                            <DropdownTrigger>
                                <button className="rounded-full focus:outline-none">
                                    <Avatar className="w-9 h-9 cursor-pointer">
                                        <AvatarFallback>
                                            {user?.name?.charAt(0)?.toUpperCase() || "U"}
                                        </AvatarFallback>
                                    </Avatar>
                                </button>
                            </DropdownTrigger>
                            <DropdownPopover>
                                <DropdownMenu>
                                    <DropdownItem id="profile" textValue="Profile">
                                        <p className="font-semibold">{user?.name}</p>
                                        <p className="font-semibold text-zinc-500 text-sm">
                                            {user?.email}
                                        </p>
                                    </DropdownItem>
                                    <DropdownItem id="settings" textValue="Settings">
                                        الإعدادات
                                    </DropdownItem>
                                    <DropdownItem
                                        id="logout"
                                        textValue="Log Out"
                                        onPress={handleLogout}
                                    >
                                        <span className="flex items-center gap-2 text-red-500">
                                            <LogOut size={14} /> تسجيل الخروج
                                        </span>
                                    </DropdownItem>
                                </DropdownMenu>
                            </DropdownPopover>
                        </Dropdown>
                    </div>
                </nav>

                {/* ── Mobile sidebar ────────────────────────────────────── */}
                {mobileOpen && (
                    <div className="sm:hidden fixed inset-0 z-[200]">
                        <div
                            className="absolute inset-0 bg-black/40 backdrop-blur-sm"
                            onClick={closeMobile}
                        />
                        <aside
                            dir="rtl"
                            className="absolute top-0 right-0 h-full w-[85vw] max-w-sm bg-white dark:bg-zinc-900 shadow-2xl flex flex-col"
                            style={{ animation: "slideInRightSidebar 0.25s ease-out" }}
                        >
                            <style>{`@keyframes slideInRightSidebar{from{transform:translateX(100%)}to{transform:translateX(0)}}`}</style>
                            <div className="flex items-center justify-between px-5 h-20 border-b border-zinc-200/60 dark:border-zinc-800/60 shrink-0">
                                <div className="flex items-center gap-3">
                                    <div className="w-9 h-9 bg-black dark:bg-white rounded-xl flex items-center justify-center">
                                        <Store className="text-white dark:text-black w-5 h-5" />
                                    </div>
                                    <span className="font-bold text-lg text-black dark:text-white">Talabye</span>
                                </div>
                                <button
                                    type="button"
                                    onClick={closeMobile}
                                    className="w-9 h-9 flex items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300"
                                    aria-label="Close menu"
                                >
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                            <nav className="flex-1 overflow-y-auto p-4 space-y-5">
                                {navItems.map((item) => (
                                    <div key={item.name}>
                                        <div className="flex items-center gap-2 px-2 mb-2 text-xs font-bold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                            <item.icon className="w-4 h-4" />
                                            {item.name}
                                        </div>
                                        {item.children.map((child) => (
                                            <ChildLink
                                                key={child.href}
                                                child={child}
                                                active={isActive(child.href)}
                                                onSelect={closeMobile}
                                            />
                                        ))}
                                    </div>
                                ))}
                            </nav>
                        </aside>
                    </div>
                )}

                {/* ── Content ────────────────────────────────────────────── */}
                <div className="flex-1 overflow-y-auto px-2 md:px-6 py-8 ">
                    <div className={`${fullWidth ? "" : maxWidth} mx-auto`}>
                        {title && (
                            <div className="mb-6 px-2">
                                <h1 className="text-2xl font-extrabold tracking-tight text-black dark:text-white leading-none">
                                    {title}
                                </h1>
                            </div>
                        )}
                        <main className="bg-white/50 dark:bg-zinc-900/50 rounded-[1.8rem] min-h-[calc(100vh-200px)] py-5 px-2 md:px-4">
                            {children}
                        </main>
                    </div>
                </div>
            </div>
        </div>
    );
}
