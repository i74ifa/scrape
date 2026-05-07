import AdminLayout from "@/Layouts/AdminLayout";
import { router } from "@inertiajs/react";
import { useCallback, useRef, useState } from "react";
import { Search, Package, ExternalLink, CheckCircle2, XCircle } from "lucide-react";
import { Card, CardContent, Pagination } from "@heroui/react";

const formatMoney = (n, symbol = "") =>
    `${new Intl.NumberFormat("ar-IQ", { maximumFractionDigits: 2 }).format(Number(n) || 0)} ${symbol}`.trim();

function ProductRow({ product }) {
    const symbol = product.platform?.currency_symbol || "";
    const isActive = !!product.is_active;

    return (
        <tr className="border-b border-zinc-100 dark:border-zinc-800 last:border-b-0">
            <td className="px-4 py-3">
                <div className="flex items-center gap-3">
                    {product.image ? (
                        <img
                            src={product.image}
                            alt={product.name}
                            className="w-12 h-12 rounded-2xl object-cover bg-zinc-100"
                            loading="lazy"
                        />
                    ) : (
                        <div className="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                            <Package size={18} className="text-zinc-400" />
                        </div>
                    )}
                    <div className="min-w-0">
                        <div className="font-bold text-sm text-zinc-900 dark:text-white truncate max-w-xs">
                            {product.name}
                        </div>
                        {product.brand && (
                            <div className="text-xs text-zinc-400 truncate">
                                {product.brand}
                                {product.category ? ` • ${product.category}` : ""}
                            </div>
                        )}
                    </div>
                </div>
            </td>
            <td className="px-4 py-3">
                {product.platform ? (
                    <div className="flex items-center gap-2">
                        {product.platform.logo && (
                            <img
                                src={product.platform.logo}
                                alt={product.platform.name}
                                className="w-6 h-6 rounded-lg object-contain"
                            />
                        )}
                        <span className="text-sm font-bold">
                            {product.platform.name}
                        </span>
                    </div>
                ) : (
                    <span className="text-xs text-zinc-400">—</span>
                )}
            </td>
            <td className="px-4 py-3 text-sm font-black">
                {formatMoney(product.price, symbol)}
            </td>
            <td className="px-4 py-3 text-sm font-bold text-zinc-500 text-center">
                {product.weight ? `${product.weight} g` : "—"}
            </td>
            <td className="px-4 py-3">
                {isActive ? (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                        <CheckCircle2 size={12} />
                        نشط
                    </span>
                ) : (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-zinc-200 dark:bg-zinc-800 text-zinc-500">
                        <XCircle size={12} />
                        غير نشط
                    </span>
                )}
            </td>
            <td className="px-4 py-3">
                {product.url && (
                    <a
                        href={product.url}
                        target="_blank"
                        rel="noopener"
                        className="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline"
                    >
                        <ExternalLink size={12} />
                        المصدر
                    </a>
                )}
            </td>
        </tr>
    );
}

export default function Index({ products, platforms = [], filters = {} }) {
    const [searchQuery, setSearchQuery] = useState(filters.search || "");
    const [statusFilter, setStatusFilter] = useState(filters.status || "");
    const [platformFilter, setPlatformFilter] = useState(
        filters.platform_id ? String(filters.platform_id) : "",
    );
    const debounceTimer = useRef(null);

    const productsList = products?.data || [];

    const buildData = (overrides) => ({
        search: searchQuery,
        status: statusFilter,
        platform_id: platformFilter,
        page: 1,
        ...overrides,
    });

    const handleSearch = useCallback(
        (value) => {
            setSearchQuery(value);
            clearTimeout(debounceTimer.current);
            debounceTimer.current = setTimeout(() => {
                router.reload({
                    data: buildData({ search: value }),
                    only: ["products"],
                    preserveScroll: true,
                });
            }, 300);
        },
        [statusFilter, platformFilter],
    );

    const handleStatusFilter = (value) => {
        setStatusFilter(value);
        router.reload({
            data: buildData({ status: value }),
            only: ["products"],
            preserveScroll: true,
        });
    };

    const handlePlatformFilter = (value) => {
        setPlatformFilter(value);
        router.reload({
            data: buildData({ platform_id: value }),
            only: ["products"],
            preserveScroll: true,
        });
    };

    const goToPage = (page) => {
        router.reload({
            data: buildData({ page }),
            only: ["products"],
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout title="المنتجات">
            <div className="flex flex-col gap-6 pb-10">
                <div className="flex justify-between items-center px-2">
                    <h4 className="text-base font-bold text-zinc-500">
                        إجمالي {products?.total ?? productsList.length} منتج
                    </h4>
                </div>

                <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                    <CardContent className="p-5">
                        <div className="flex flex-wrap gap-3 items-center mb-5">
                            <div className="relative flex-1 max-w-sm">
                                <Search
                                    size={16}
                                    className="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400 pointer-events-none"
                                />
                                <input
                                    type="text"
                                    placeholder="بحث بالاسم أو العلامة..."
                                    value={searchQuery}
                                    onChange={(e) => handleSearch(e.target.value)}
                                    className="w-full bg-zinc-100 dark:bg-zinc-800 rounded-3xl h-12 pr-10 pl-4 text-sm border-none outline-none shadow-none"
                                />
                            </div>

                            <div className="flex flex-wrap gap-1">
                                <button
                                    onClick={() => handleStatusFilter("")}
                                    className={`text-xs font-bold px-3 py-2 rounded-xl transition-colors ${
                                        !statusFilter
                                            ? "bg-blue-600 text-white"
                                            : "bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400"
                                    }`}
                                >
                                    الكل
                                </button>
                                <button
                                    onClick={() => handleStatusFilter("active")}
                                    className={`text-xs font-bold px-3 py-2 rounded-xl transition-colors ${
                                        statusFilter === "active"
                                            ? "bg-blue-600 text-white"
                                            : "bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400"
                                    }`}
                                >
                                    نشط
                                </button>
                                <button
                                    onClick={() => handleStatusFilter("inactive")}
                                    className={`text-xs font-bold px-3 py-2 rounded-xl transition-colors ${
                                        statusFilter === "inactive"
                                            ? "bg-blue-600 text-white"
                                            : "bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400"
                                    }`}
                                >
                                    غير نشط
                                </button>
                            </div>

                            {platforms.length > 0 && (
                                <select
                                    value={platformFilter}
                                    onChange={(e) =>
                                        handlePlatformFilter(e.target.value)
                                    }
                                    className="bg-zinc-100 dark:bg-zinc-800 rounded-xl h-10 px-3 text-xs font-bold border-none outline-none"
                                >
                                    <option value="">كل المنصات</option>
                                    {platforms.map((p) => (
                                        <option key={p.id} value={p.id}>
                                            {p.name}
                                        </option>
                                    ))}
                                </select>
                            )}
                        </div>

                        {productsList.length === 0 ? (
                            <div className="bg-zinc-50 dark:bg-zinc-900/50 rounded-4xl p-20 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                                <Package className="mx-auto w-16 h-16 text-zinc-300 dark:text-zinc-600 mb-4" />
                                <p className="font-black text-xl text-zinc-400">
                                    لا توجد منتجات
                                </p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-right">
                                    <thead>
                                        <tr className="border-b border-zinc-200 dark:border-zinc-800">
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                المنتج
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                المنصة
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                السعر
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide text-center">
                                                الوزن
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                الحالة
                                            </th>
                                            <th className="px-4 py-3 text-xs font-bold text-zinc-500 uppercase tracking-wide">
                                                المصدر
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {productsList.map((product) => (
                                            <ProductRow
                                                key={product.id}
                                                product={product}
                                            />
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}

                        {products?.last_page > 1 && (
                            <div className="mt-6 flex justify-center">
                                <Pagination>
                                    <Pagination.Content>
                                        <Pagination.Item>
                                            <Pagination.Previous
                                                onPress={() =>
                                                    goToPage(
                                                        Math.max(
                                                            1,
                                                            products.current_page - 1,
                                                        ),
                                                    )
                                                }
                                                isDisabled={products.current_page === 1}
                                                className="rounded-full font-bold"
                                            >
                                                <Pagination.PreviousIcon />
                                                <span>السابق</span>
                                            </Pagination.Previous>
                                        </Pagination.Item>
                                        {Array.from(
                                            { length: products.last_page },
                                            (_, i) => i + 1,
                                        ).map((p) => (
                                            <Pagination.Item key={p}>
                                                <Pagination.Link
                                                    onPress={() => goToPage(p)}
                                                    isCurrent={
                                                        p === products.current_page
                                                    }
                                                    className="rounded-full font-bold"
                                                >
                                                    {p}
                                                </Pagination.Link>
                                            </Pagination.Item>
                                        ))}
                                        <Pagination.Item>
                                            <Pagination.Next
                                                onPress={() =>
                                                    goToPage(
                                                        Math.min(
                                                            products.last_page,
                                                            products.current_page + 1,
                                                        ),
                                                    )
                                                }
                                                isDisabled={
                                                    products.current_page ===
                                                    products.last_page
                                                }
                                                className="rounded-full font-bold"
                                            >
                                                <span>التالي</span>
                                                <Pagination.NextIcon />
                                            </Pagination.Next>
                                        </Pagination.Item>
                                    </Pagination.Content>
                                </Pagination>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
