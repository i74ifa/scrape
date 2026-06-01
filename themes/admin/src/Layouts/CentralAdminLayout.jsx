import { Link, usePage, router } from "@inertiajs/react";
import { useEffect, useState, memo } from "react";
import LanguageSwitcher from "@/Components/LanguageSwitcher";
import {
    LayoutDashboard,
    Building2,
    CreditCard,
    BarChart3,
    Settings,
    Users,
    Search,
    UtensilsCrossed,
    LogOut,
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

/* ── Child link inside the dropdown ───────────────────── */
function ChildLink({ child, isActive }) {
    const active = isActive(child.href);
    return (
        <Link
            href={child.href}
            className={`flex mb-1 items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group/child ${
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
}

/* ── Main layout ─────────────────────────────────────────────────────────── */
export default function CentralAdminLayout({
    children,
    title,
    fullWidth = false,
    maxWidth = "max-w-375",
}) {
    const { auth, locale } = usePage().props;
    const user = auth?.user;
    const centralDomain = usePage().props?.central_domain || "menu.test";
    const domain = "https://" + centralDomain;

    const handleLogout = () => {
        router.post("/logout");
    };

    const navItems = [
        {
            name: "الرئيسية",
            icon: LayoutDashboard,
            href: "/central",
            children: [
                { name: "الرئيسية", icon: LayoutDashboard, href: "/central" },
            ],
        },
        {
            name: "العملاء",
            icon: Building2,
            href: "/central/tenants",
            children: [
                { name: "العملاء", icon: Building2, href: "/central/tenants" },
                { name: "الاشتراكات", icon: CreditCard, href: "/central/subscriptions" },
            ],
        },
        {
            name: "الخطط",
            icon: Settings,
            href: "/central/plans",
            children: [
                { name: "الخطط", icon: Settings, href: "/central/plans" },
                { name: "التقارير", icon: BarChart3, href: "/central/reports" },
            ],
        },
    ];

    const currentPath = window.location.pathname;
    const isActive = (href) => {
        if (href === "/central") return currentPath === "/central";
        return currentPath.startsWith(href);
    };

    return (
        <div className="min-h-screen bg-[#F2F2F7] dark:bg-[#000000] transition-colors duration-500 relative overflow-hidden">
            <LanguageSwitcher locale={locale} />
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
                    {/* Logo */}
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-black dark:bg-white rounded-2xl flex items-center justify-center shadow-lg">
                            <UtensilsCrossed className="text-white dark:text-black w-6 h-6" />
                        </div>
                        <p className="font-bold text-xl tracking-tight leading-none hidden sm:block text-black dark:text-white">
                            <a target="_blank" href={domain}>
                                Talabye <span className="text-xs font-normal opacity-60">Central</span>
                            </a>
                        </p>
                    </div>

                    {/* ── Nav tabs ──────────────────────────────────────────── */}
                    <div className="hidden sm:flex gap-2 flex-1 justify-center items-center">
                        {navItems.map((item) => (
                            <div key={item.name} className="relative group">
                                {/* Tab button */}
                                <Link
                                    href={item.href}
                                    className={`flex items-center gap-2 text-sm font-semibold transition-all h-12 px-6 rounded-2xl cursor-pointer ${
                                        isActive(item.href)
                                            ? "text-blue-500 dark:text-white bg-blue-50 border border-blue-200"
                                            : "text-zinc-900 hover:bg-gray-200 bg-gray-100 hover:text-black dark:text-zinc-400 dark:hover:text-white dark:hover:border-gray-700 border border-transparent"
                                    }`}
                                >
                                    <item.icon className="w-5 h-5" />
                                    {item.name}
                                </Link>

                                {/* Dropdown panel (Pure CSS Hover) */}
                                {item.children?.length > 0 && (
                                    <div className="absolute top-full left-1/2 -translate-x-1/2 pt-3 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-3 group-hover:translate-y-0 z-500 w-55">
                                        <div className="bg-white/65 dark:bg-zinc-900/65 backdrop-blur-xl saturate-200 border border-zinc-200/50 dark:border-zinc-800/50 rounded-[1.2rem] shadow-[0_12px_40px_rgba(0,0,0,0.12)] dark:shadow-none overflow-hidden p-2">
                                            {item.children.map((child) => (
                                                <ChildLink
                                                    key={child.href}
                                                    child={child}
                                                    isActive={isActive}
                                                />
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>

                    {/* Right side: search + avatar */}
                    <div className="flex items-center gap-4">
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

                {/* ── Content ────────────────────────────────────────────── */}
                <div className="flex-1 overflow-y-auto px-6 py-8">
                    <div className={`${fullWidth ? "" : maxWidth} mx-auto`}>
                        {title && (
                            <div className="mb-6 px-2">
                                <h1 className="text-2xl font-extrabold tracking-tight text-black dark:text-white leading-none">
                                    {title}
                                </h1>
                            </div>
                        )}
                        <main className="bg-white/50 dark:bg-zinc-900/50 rounded-[1.8rem] min-h-[calc(100vh-200px)] py-5 px-4">
                            {children}
                        </main>
                    </div>
                </div>
            </div>
        </div>
    );
}
