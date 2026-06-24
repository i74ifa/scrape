import { useMemo, useState } from "react";
import { router } from "@inertiajs/react";
import axios from "axios";
import AdminLayout from "@/Layouts/AdminLayout";
import ColorPickerInput from "@/Components/ColorPickerInput";
import {
    Card,
    CardContent,
    Button,
    Modal,
    Label,
    toast,
} from "@heroui/react";
import {
    Plus,
    Pencil,
    Trash2,
    ChevronUp,
    ChevronDown,
    Sun,
    Moon,
    ImagePlus,
    X,
    Loader2,
    GalleryHorizontalEnd,
    LayoutGrid,
    Megaphone,
    Rows3,
    Grid2x2,
    Smartphone,
} from "lucide-react";

/* ------------------------------------------------------------------ */
/* Section type catalog                                                */
/* ------------------------------------------------------------------ */

// `badge` + `iconColor` are full static class strings (Tailwind purges
// interpolated `bg-${color}-100`, so the classes must appear verbatim).
const TYPES = {
    BannerSwipe: {
        label: "بانر متحرك",
        desc: "صور بعرض كامل قابلة للتمرير",
        icon: GalleryHorizontalEnd,
        badge: "bg-blue-100 dark:bg-blue-900/30",
        iconColor: "text-blue-600 dark:text-blue-400",
    },
    BannerGrid: {
        label: "شبكة بانرات",
        desc: "صور بنظام 12 عمود (مثل bootstrap)",
        icon: LayoutGrid,
        badge: "bg-violet-100 dark:bg-violet-900/30",
        iconColor: "text-violet-600 dark:text-violet-400",
    },
    CustomBanner: {
        label: "بانر مخصص",
        desc: "بطاقة ترويجية بألوان ونص وزر",
        icon: Megaphone,
        badge: "bg-amber-100 dark:bg-amber-900/30",
        iconColor: "text-amber-600 dark:text-amber-400",
    },
    ProductSwipe: {
        label: "منتجات متحركة",
        desc: "صف أفقي من المنتجات من رابط",
        icon: Rows3,
        badge: "bg-emerald-100 dark:bg-emerald-900/30",
        iconColor: "text-emerald-600 dark:text-emerald-400",
    },
    ProductGrid: {
        label: "شبكة منتجات",
        desc: "شبكة منتجات من رابط",
        icon: Grid2x2,
        badge: "bg-rose-100 dark:bg-rose-900/30",
        iconColor: "text-rose-600 dark:text-rose-400",
    },
};

// Default content payloads — mirror the shape the Flutter renderer expects.
const defaultContent = (type) => {
    switch (type) {
        case "BannerSwipe":
            return {
                data: [{ url: "", image: { light: "", dark: "" } }],
                title: "",
                config: { autoplay: false, page_cols: 1, height: 110 },
            };
        case "BannerGrid":
            return {
                data: [{ url: "", cols: 6, image: { light: "", dark: "" } }],
                title: "",
                config: { autoplay: false, page_cols: 1 },
            };
        case "CustomBanner":
            return {
                title: "",
                description: "",
                button: { title: "", url: "" },
                icon: { light: "", dark: "" },
                colors: {
                    background: "#76D2DB",
                    text: "#ffffff",
                    button: "#ffffff",
                    button_text: "#000000",
                },
            };
        case "ProductSwipe":
        case "ProductGrid":
            return { data: [{ title: "", url: "" }] };
        default:
            return {};
    }
};

/* ------------------------------------------------------------------ */
/* Image upload field (uploads immediately, stores returned url)       */
/* ------------------------------------------------------------------ */

function ImageField({ label, value, onChange }) {
    const [uploading, setUploading] = useState(false);

    const upload = async (file) => {
        if (!file) return;
        setUploading(true);
        try {
            const fd = new FormData();
            fd.append("image", file);
            const { data } = await axios.post(
                route("admin.app-sections.images.upload"),
                fd,
                { headers: { "Content-Type": "multipart/form-data" } },
            );
            onChange(data.url);
        } catch {
            toast.error("تعذّر رفع الصورة");
        } finally {
            setUploading(false);
        }
    };

    return (
        <div>
            <Label className="text-xs font-bold text-zinc-500">{label}</Label>
            <div className="mt-1 flex items-center gap-3">
                {value ? (
                    <div className="relative w-16 h-16 rounded-2xl overflow-hidden border border-zinc-200 dark:border-zinc-600 shrink-0">
                        <img
                            src={value}
                            alt=""
                            className="w-full h-full object-cover"
                        />
                        <button
                            type="button"
                            onClick={() => onChange("")}
                            className="absolute top-1 left-1 w-5 h-5 rounded-full bg-black/60 text-white flex items-center justify-center"
                        >
                            <X className="w-3 h-3" />
                        </button>
                    </div>
                ) : (
                    <label className="w-16 h-16 rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-600 flex items-center justify-center text-zinc-400 cursor-pointer hover:border-blue-400 transition-colors shrink-0">
                        {uploading ? (
                            <Loader2 className="w-4 h-4 animate-spin" />
                        ) : (
                            <ImagePlus className="w-4 h-4" />
                        )}
                        <input
                            type="file"
                            accept="image/*"
                            className="hidden"
                            onChange={(e) => upload(e.target.files?.[0])}
                        />
                    </label>
                )}
                {value && (
                    <label className="text-xs font-bold text-blue-600 cursor-pointer">
                        تغيير
                        <input
                            type="file"
                            accept="image/*"
                            className="hidden"
                            onChange={(e) => upload(e.target.files?.[0])}
                        />
                    </label>
                )}
            </div>
        </div>
    );
}

// Small text input styled to match the admin design tokens.
function Field({ label, value, onChange, placeholder, type = "text" }) {
    return (
        <div>
            <Label className="text-xs font-bold text-zinc-500">{label}</Label>
            <input
                type={type}
                value={value ?? ""}
                onChange={(e) => onChange(e.target.value)}
                placeholder={placeholder}
                className="mt-1 w-full bg-zinc-100 dark:bg-zinc-800 rounded-2xl h-11 px-4 text-sm border-none outline-none"
            />
        </div>
    );
}

function Toggle({ label, checked, onChange }) {
    return (
        <button
            type="button"
            onClick={() => onChange(!checked)}
            className="flex items-center justify-between w-full"
        >
            <span className="text-xs font-bold text-zinc-500">{label}</span>
            <span
                className={`w-11 h-6 rounded-full p-0.5 transition-colors ${
                    checked ? "bg-blue-600" : "bg-zinc-300 dark:bg-zinc-700"
                }`}
            >
                <span
                    className={`block w-5 h-5 rounded-full bg-white transition-transform ${
                        checked ? "rtl:-translate-x-5 ltr:translate-x-5" : ""
                    }`}
                />
            </span>
        </button>
    );
}

/* ------------------------------------------------------------------ */
/* Section preview renderers (inside the device)                       */
/* ------------------------------------------------------------------ */

function img(item, mode) {
    return item?.image?.[mode] || item?.image?.light || item?.image?.dark || "";
}

// The simulated screen is themed by the preview `mode` toggle, NOT by the admin
// panel's own dark mode (which follows prefers-color-scheme). So all simulated
// surfaces use explicit, mode-driven classes rather than `dark:` variants.
function skinFor(mode) {
    return mode === "dark"
        ? {
              screen: "bg-zinc-900 text-zinc-100",
              ph: "bg-zinc-700",
              border: "border-zinc-800",
              muted: "text-zinc-500",
          }
        : {
              screen: "bg-zinc-50 text-zinc-900",
              ph: "bg-zinc-200",
              border: "border-zinc-100",
              muted: "text-zinc-400",
          };
}

function PlaceholderProduct({ small, ph }) {
    return (
        <div className={small ? "w-24 shrink-0" : ""}>
            <div className={`aspect-square rounded-xl ${ph}`} />
            <div className={`h-2 mt-1.5 rounded ${ph} w-3/4`} />
            <div className={`h-2 mt-1 rounded ${ph} w-1/2`} />
        </div>
    );
}

function SectionPreview({ section, mode, skin }) {
    const c = section.content || {};
    const title = c.title;

    switch (section.name) {
        case "BannerSwipe":
            return (
                <div className="px-3 py-2">
                    {title ? (
                        <p className="text-xs font-bold mb-1.5">{title}</p>
                    ) : null}
                    <div className="flex gap-2 overflow-x-auto snap-x">
                        {(c.data || []).map((it, i) => {
                            const src = img(it, mode);
                            return (
                                <div
                                    key={i}
                                    className={`snap-start shrink-0 w-full rounded-2xl overflow-hidden ${skin.ph}`}
                                >
                                    {src ? (
                                        <img
                                            src={src}
                                            alt=""
                                            className="w-full h-auto block"
                                        />
                                    ) : (
                                        <div
                                            className={`w-full h-24 flex items-center justify-center text-[10px] ${skin.muted}`}
                                        >
                                            بدون صورة
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>
            );

        case "BannerGrid":
            return (
                <div className="px-3 py-2">
                    {title ? (
                        <p className="text-xs font-bold mb-1.5">{title}</p>
                    ) : null}
                    {/* 12-col grid, items-start so a row is as tall as its tallest
                        image (shorter images keep their intrinsic height). */}
                    <div className="grid grid-cols-12 gap-2 items-start">
                        {(c.data || []).map((it, i) => {
                            const src = img(it, mode);
                            const cols = Math.min(
                                12,
                                Math.max(1, Number(it.cols) || 12),
                            );
                            return (
                                <div
                                    key={i}
                                    style={{ gridColumn: `span ${cols}` }}
                                    className={`rounded-2xl overflow-hidden ${skin.ph}`}
                                >
                                    {src ? (
                                        <img
                                            src={src}
                                            alt=""
                                            className="w-full h-auto block"
                                        />
                                    ) : (
                                        <div
                                            className={`w-full h-20 flex items-center justify-center text-[10px] ${skin.muted}`}
                                        >
                                            {cols}/12
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>
            );

        case "CustomBanner": {
            const colors = c.colors || {};
            const icon = c.icon?.[mode] || c.icon?.light || c.icon?.dark;
            return (
                <div className="px-3 py-2">
                    <div
                        className="rounded-2xl p-3 flex items-center gap-3"
                        style={{ background: colors.background || "#76D2DB" }}
                    >
                        {icon ? (
                            <img
                                src={icon}
                                alt=""
                                className="w-12 h-12 rounded-xl object-cover shrink-0"
                            />
                        ) : null}
                        <div className="min-w-0 flex-1">
                            <p
                                className="text-sm font-black truncate"
                                style={{ color: colors.text || "#fff" }}
                            >
                                {c.title || "عنوان البانر"}
                            </p>
                            <p
                                className="text-[11px] truncate opacity-90"
                                style={{ color: colors.text || "#fff" }}
                            >
                                {c.description || "الوصف"}
                            </p>
                        </div>
                        <span
                            className="text-[11px] font-bold px-3 py-1.5 rounded-full shrink-0"
                            style={{
                                background: colors.button || "#fff",
                                color: colors.button_text || "#000",
                            }}
                        >
                            {c.button?.title || "زر"}
                        </span>
                    </div>
                </div>
            );
        }

        case "ProductSwipe":
            return (
                <div className="px-3 py-2">
                    {(c.data || []).map((feed, i) => (
                        <div key={i} className="mb-3">
                            <p className="text-xs font-bold mb-1.5">
                                {feed.title || "منتجات"}
                            </p>
                            <div className="flex gap-2 overflow-x-auto">
                                {Array.from({ length: 4 }).map((_, j) => (
                                    <PlaceholderProduct
                                        key={j}
                                        small
                                        ph={skin.ph}
                                    />
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            );

        case "ProductGrid":
            return (
                <div className="px-3 py-2">
                    {(c.data || []).map((feed, i) => (
                        <div key={i} className="mb-3">
                            <p className="text-xs font-bold mb-1.5">
                                {feed.title || "منتجات"}
                            </p>
                            <div className="grid grid-cols-2 gap-2">
                                {Array.from({ length: 4 }).map((_, j) => (
                                    <PlaceholderProduct key={j} ph={skin.ph} />
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            );

        default:
            return (
                <div className="px-3 py-4 text-center text-xs text-zinc-400">
                    {section.name}
                </div>
            );
    }
}

/* ------------------------------------------------------------------ */
/* Editor modal                                                        */
/* ------------------------------------------------------------------ */

function EditorModal({ open, type, initial, page, editingId, onClose }) {
    const [content, setContent] = useState(() =>
        initial ? structuredClone(initial) : defaultContent(type),
    );
    const [saving, setSaving] = useState(false);
    const meta = TYPES[type] || {};

    // generic deep setters for nested fields
    const patch = (updater) =>
        setContent((prev) => {
            const next = structuredClone(prev);
            updater(next);
            return next;
        });

    const items = content.data || [];
    const addItem = () =>
        patch((n) => {
            n.data = n.data || [];
            if (type === "BannerSwipe")
                n.data.push({ url: "", image: { light: "", dark: "" } });
            else if (type === "BannerGrid")
                n.data.push({ url: "", cols: 6, image: { light: "", dark: "" } });
            else n.data.push({ title: "", url: "" });
        });
    const removeItem = (i) =>
        patch((n) => {
            n.data.splice(i, 1);
        });

    const submit = () => {
        setSaving(true);
        const payload = { page, name: type, content };
        const opts = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("تم الحفظ بنجاح");
                onClose(true);
            },
            onError: () => toast.error("تعذّر الحفظ، تحقق من البيانات"),
            onFinish: () => setSaving(false),
        };
        if (editingId) {
            router.put(route("admin.app-sections.update", editingId), payload, opts);
        } else {
            router.post(route("admin.app-sections.store"), payload, opts);
        }
    };

    return (
        <Modal
            isOpen={open}
            onOpenChange={(o) => !o && onClose(false)}
            placement="center"
            backdrop="blur"
        >
            <Modal.Backdrop>
                <Modal.Container>
                    <Modal.Dialog className="max-w-lg w-full">
                        <Modal.CloseTrigger className="rtl:right-auto rtl:left-3 ltr:left-auto ltr:right-3" />
                        <Modal.Header className="font-black text-xl flex items-center gap-3">
                            <div
                                className={`w-9 h-9 rounded-2xl ${meta.badge} flex items-center justify-center`}
                            >
                                {meta.icon ? (
                                    <meta.icon
                                        className={`w-4 h-4 ${meta.iconColor}`}
                                    />
                                ) : null}
                            </div>
                            {editingId ? "تعديل" : "إضافة"} {meta.label}
                        </Modal.Header>
                        <Modal.Body>
                            <div className="space-y-4 p-1 max-h-[60vh] overflow-y-auto">
                                {/* Section title (banners) */}
                                {(type === "BannerSwipe" ||
                                    type === "BannerGrid") && (
                                    <Field
                                        label="عنوان القسم (اختياري)"
                                        value={content.title}
                                        onChange={(v) =>
                                            patch((n) => (n.title = v))
                                        }
                                        placeholder="مثال: عروض اليوم"
                                    />
                                )}

                                {/* Repeatable banner / product items */}
                                {type !== "CustomBanner" && (
                                    <div className="space-y-3">
                                        {items.map((it, i) => (
                                            <div
                                                key={i}
                                                className="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-3 space-y-3 relative"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <span className="text-xs font-bold text-zinc-400">
                                                        عنصر {i + 1}
                                                    </span>
                                                    {items.length > 1 && (
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                removeItem(i)
                                                            }
                                                            className="text-rose-500"
                                                        >
                                                            <Trash2 className="w-4 h-4" />
                                                        </button>
                                                    )}
                                                </div>

                                                {(type === "ProductSwipe" ||
                                                    type === "ProductGrid") && (
                                                    <>
                                                        <Field
                                                            label="العنوان"
                                                            value={it.title}
                                                            onChange={(v) =>
                                                                patch(
                                                                    (n) =>
                                                                        (n.data[
                                                                            i
                                                                        ].title =
                                                                            v),
                                                                )
                                                            }
                                                            placeholder="افضل المنتجات"
                                                        />
                                                        <Field
                                                            label="رابط المنتجات (API)"
                                                            value={it.url}
                                                            onChange={(v) =>
                                                                patch(
                                                                    (n) =>
                                                                        (n.data[
                                                                            i
                                                                        ].url =
                                                                            v),
                                                                )
                                                            }
                                                            placeholder="/api/catalog/products?type=best"
                                                        />
                                                    </>
                                                )}

                                                {(type === "BannerSwipe" ||
                                                    type === "BannerGrid") && (
                                                    <>
                                                        <Field
                                                            label="الرابط عند الضغط"
                                                            value={it.url}
                                                            onChange={(v) =>
                                                                patch(
                                                                    (n) =>
                                                                        (n.data[
                                                                            i
                                                                        ].url =
                                                                            v),
                                                                )
                                                            }
                                                            placeholder="/platforms/5"
                                                        />
                                                        {type ===
                                                            "BannerGrid" && (
                                                            <Field
                                                                label="عدد الأعمدة (1-12)"
                                                                type="number"
                                                                value={it.cols}
                                                                onChange={(v) =>
                                                                    patch(
                                                                        (n) =>
                                                                            (n.data[
                                                                                i
                                                                            ].cols =
                                                                                Math.min(
                                                                                    12,
                                                                                    Math.max(
                                                                                        1,
                                                                                        Number(
                                                                                            v,
                                                                                        ) ||
                                                                                            1,
                                                                                    ),
                                                                                )),
                                                                    )
                                                                }
                                                                placeholder="6"
                                                            />
                                                        )}
                                                        <div className="flex gap-3">
                                                            <div className="flex-1">
                                                                <ImageField
                                                                    label="صورة (فاتح)"
                                                                    value={
                                                                        it.image
                                                                            ?.light
                                                                    }
                                                                    onChange={(
                                                                        v,
                                                                    ) =>
                                                                        patch(
                                                                            (
                                                                                n,
                                                                            ) =>
                                                                                (n.data[
                                                                                    i
                                                                                ].image.light =
                                                                                    v),
                                                                        )
                                                                    }
                                                                />
                                                            </div>
                                                            <div className="flex-1">
                                                                <ImageField
                                                                    label="صورة (داكن)"
                                                                    value={
                                                                        it.image
                                                                            ?.dark
                                                                    }
                                                                    onChange={(
                                                                        v,
                                                                    ) =>
                                                                        patch(
                                                                            (
                                                                                n,
                                                                            ) =>
                                                                                (n.data[
                                                                                    i
                                                                                ].image.dark =
                                                                                    v),
                                                                        )
                                                                    }
                                                                />
                                                            </div>
                                                        </div>
                                                    </>
                                                )}
                                            </div>
                                        ))}
                                        <Button
                                            type="button"
                                            variant="flat"
                                            color="primary"
                                            className="rounded-full font-bold w-full"
                                            startContent={
                                                <Plus className="w-4 h-4" />
                                            }
                                            onPress={addItem}
                                        >
                                            إضافة عنصر
                                        </Button>
                                    </div>
                                )}

                                {/* Banner config */}
                                {(type === "BannerSwipe" ||
                                    type === "BannerGrid") && (
                                    <div className="rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 p-3 space-y-3">
                                        <Toggle
                                            label="تشغيل تلقائي"
                                            checked={!!content.config?.autoplay}
                                            onChange={(v) =>
                                                patch(
                                                    (n) =>
                                                        (n.config.autoplay = v),
                                                )
                                            }
                                        />
                                        <Field
                                            label="عدد الأعمدة في الصفحة"
                                            type="number"
                                            value={content.config?.page_cols}
                                            onChange={(v) =>
                                                patch(
                                                    (n) =>
                                                        (n.config.page_cols =
                                                            Number(v) || 1),
                                                )
                                            }
                                        />
                                        {type === "BannerSwipe" && (
                                            <Field
                                                label="الارتفاع"
                                                type="number"
                                                value={content.config?.height}
                                                onChange={(v) =>
                                                    patch(
                                                        (n) =>
                                                            (n.config.height =
                                                                Number(v) || 0),
                                                    )
                                                }
                                            />
                                        )}
                                    </div>
                                )}

                                {/* CustomBanner fields */}
                                {type === "CustomBanner" && (
                                    <>
                                        <Field
                                            label="العنوان"
                                            value={content.title}
                                            onChange={(v) =>
                                                patch((n) => (n.title = v))
                                            }
                                            placeholder="تخفيضات شي إن"
                                        />
                                        <Field
                                            label="الوصف"
                                            value={content.description}
                                            onChange={(v) =>
                                                patch(
                                                    (n) =>
                                                        (n.description = v),
                                                )
                                            }
                                            placeholder="خصومات تصل إلى 70%"
                                        />
                                        <div className="flex gap-3">
                                            <div className="flex-1">
                                                <Field
                                                    label="نص الزر"
                                                    value={content.button?.title}
                                                    onChange={(v) =>
                                                        patch(
                                                            (n) =>
                                                                (n.button.title =
                                                                    v),
                                                        )
                                                    }
                                                    placeholder="تسوق الآن"
                                                />
                                            </div>
                                            <div className="flex-1">
                                                <Field
                                                    label="رابط الزر"
                                                    value={content.button?.url}
                                                    onChange={(v) =>
                                                        patch(
                                                            (n) =>
                                                                (n.button.url =
                                                                    v),
                                                        )
                                                    }
                                                    placeholder="/platforms/5"
                                                />
                                            </div>
                                        </div>
                                        <div className="flex gap-3">
                                            <div className="flex-1">
                                                <ImageField
                                                    label="أيقونة (فاتح)"
                                                    value={content.icon?.light}
                                                    onChange={(v) =>
                                                        patch(
                                                            (n) =>
                                                                (n.icon.light =
                                                                    v),
                                                        )
                                                    }
                                                />
                                            </div>
                                            <div className="flex-1">
                                                <ImageField
                                                    label="أيقونة (داكن)"
                                                    value={content.icon?.dark}
                                                    onChange={(v) =>
                                                        patch(
                                                            (n) =>
                                                                (n.icon.dark =
                                                                    v),
                                                        )
                                                    }
                                                />
                                            </div>
                                        </div>
                                        <div className="grid grid-cols-2 gap-3">
                                            {[
                                                ["background", "لون الخلفية"],
                                                ["text", "لون النص"],
                                                ["button", "لون الزر"],
                                                ["button_text", "لون نص الزر"],
                                            ].map(([key, lbl]) => (
                                                <div
                                                    key={key}
                                                    className="flex items-center gap-2"
                                                >
                                                    <ColorPickerInput
                                                        value={
                                                            content.colors?.[key]
                                                        }
                                                        onChange={(v) =>
                                                            patch(
                                                                (n) =>
                                                                    (n.colors[
                                                                        key
                                                                    ] = v),
                                                            )
                                                        }
                                                    />
                                                    <span className="text-xs font-bold text-zinc-500">
                                                        {lbl}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </>
                                )}
                            </div>
                        </Modal.Body>
                        <Modal.Footer>
                            <Button
                                type="button"
                                variant="flat"
                                onPress={() => onClose(false)}
                            >
                                إلغاء
                            </Button>
                            <Button
                                color="primary"
                                isLoading={saving}
                                onPress={submit}
                            >
                                {editingId ? "حفظ التعديلات" : "حفظ"}
                            </Button>
                        </Modal.Footer>
                    </Modal.Dialog>
                </Modal.Container>
            </Modal.Backdrop>
        </Modal>
    );
}

/* ------------------------------------------------------------------ */
/* Page                                                                */
/* ------------------------------------------------------------------ */

export default function Index({ sections = [], pages = [], filters = {} }) {
    const currentPage = filters.page || "home";
    const [mode, setMode] = useState("light");
    const skin = skinFor(mode);

    // Type picker → editor flow
    const [picking, setPicking] = useState(false);
    const [editor, setEditor] = useState(null); // {type, initial, editingId}
    const [deleteTarget, setDeleteTarget] = useState(null);

    // New-page slug input
    const [newPageOpen, setNewPageOpen] = useState(false);
    const [newSlug, setNewSlug] = useState("");

    // local ordered copy for optimistic reorder
    const ordered = useMemo(
        () => [...sections].sort((a, b) => a.sort_order - b.sort_order),
        [sections],
    );

    const switchPage = (slug) => {
        if (!slug) return;
        router.get(
            route("admin.app-sections.index"),
            { page: slug },
            {
                preserveState: false,
                preserveScroll: true,
                only: ["sections", "pages", "filters"],
            },
        );
    };

    const createPage = () => {
        const slug = newSlug.trim().toLowerCase();
        if (!/^[a-z0-9-]+$/.test(slug)) {
            toast.error("معرّف غير صالح (أحرف إنجليزية وأرقام وشرطات فقط)");
            return;
        }
        setNewPageOpen(false);
        setNewSlug("");
        switchPage(slug);
    };

    const move = (index, dir) => {
        const target = index + dir;
        if (target < 0 || target >= ordered.length) return;
        const next = [...ordered];
        [next[index], next[target]] = [next[target], next[index]];
        router.post(
            route("admin.app-sections.reorder"),
            { page: currentPage, ids: next.map((s) => s.id) },
            { preserveScroll: true, only: ["sections"] },
        );
    };

    const confirmDelete = () => {
        if (!deleteTarget) return;
        router.delete(
            route("admin.app-sections.destroy", deleteTarget.id),
            {
                preserveScroll: true,
                onSuccess: () => toast.success("تم حذف القسم"),
                onFinish: () => setDeleteTarget(null),
            },
        );
    };

    return (
        <AdminLayout title="أقسام التطبيق">
            <div className="flex flex-col gap-8 pb-10">
                {/* Toolbar */}
                <div className="px-2 flex flex-wrap items-center justify-between gap-4">
                    <div className="flex items-center gap-3 flex-wrap">
                        <h2 className="text-base font-bold text-zinc-500 dark:text-zinc-400">
                            الصفحة
                        </h2>
                        {/* page selector */}
                        <div className="flex gap-1 bg-zinc-100 dark:bg-zinc-800 rounded-2xl p-1 flex-wrap">
                            {pages.map((p) => (
                                <button
                                    key={p}
                                    type="button"
                                    onClick={() => switchPage(p)}
                                    className={`text-xs font-bold px-3 py-2 rounded-xl transition-colors ${
                                        p === currentPage
                                            ? "bg-blue-600 text-white"
                                            : "text-zinc-500"
                                    }`}
                                >
                                    {p}
                                </button>
                            ))}
                            <button
                                type="button"
                                onClick={() => setNewPageOpen(true)}
                                className="text-xs font-bold px-3 py-2 rounded-xl text-emerald-600 flex items-center gap-1"
                            >
                                <Plus className="w-3.5 h-3.5" />
                                صفحة جديدة
                            </button>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        {/* light/dark preview toggle */}
                        <div className="flex gap-1 bg-zinc-100 dark:bg-zinc-800 rounded-2xl p-1">
                            <button
                                type="button"
                                onClick={() => setMode("light")}
                                className={`text-xs font-bold px-3 py-2 rounded-xl flex items-center gap-1.5 ${
                                    mode === "light"
                                        ? "bg-blue-600 text-white"
                                        : "text-zinc-500"
                                }`}
                            >
                                <Sun className="w-3.5 h-3.5" />
                                فاتح
                            </button>
                            <button
                                type="button"
                                onClick={() => setMode("dark")}
                                className={`text-xs font-bold px-3 py-2 rounded-xl flex items-center gap-1.5 ${
                                    mode === "dark"
                                        ? "bg-blue-600 text-white"
                                        : "text-zinc-500"
                                }`}
                            >
                                <Moon className="w-3.5 h-3.5" />
                                داكن
                            </button>
                        </div>
                        <Button
                            color="primary"
                            className="rounded-full font-black h-11"
                            startContent={<Plus className="w-4 h-4" />}
                            onPress={() => setPicking(true)}
                        >
                            إضافة قسم
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-[auto_1fr] gap-8 items-start">
                    {/* iPhone simulation — themed by the `mode` toggle, not the
                        admin panel's own (OS-driven) dark mode. */}
                    <div className="flex justify-center lg:sticky lg:top-4">
                        <div
                            className={`w-[375px] max-w-full rounded-[3rem] border-[10px] shadow-2xl overflow-hidden ${
                                mode === "dark"
                                    ? "border-zinc-700 bg-zinc-950"
                                    : "border-zinc-900 bg-white"
                            }`}
                        >
                            {/* notch */}
                            <div
                                className={`relative h-7 flex items-center justify-center ${
                                    mode === "dark" ? "bg-zinc-950" : "bg-white"
                                }`}
                            >
                                <div
                                    className={`w-28 h-5 rounded-b-2xl ${
                                        mode === "dark"
                                            ? "bg-zinc-700"
                                            : "bg-zinc-900"
                                    }`}
                                />
                            </div>
                            <div
                                className={`h-[640px] overflow-y-auto ${skin.screen}`}
                            >
                                {ordered.length === 0 ? (
                                    <div
                                        className={`flex flex-col items-center justify-center h-full gap-2 px-6 text-center ${skin.muted}`}
                                    >
                                        <Smartphone className="w-10 h-10" />
                                        <p className="text-sm font-bold">
                                            لا توجد أقسام في هذه الصفحة
                                        </p>
                                        <p className="text-xs">
                                            اضغط "إضافة قسم" للبدء
                                        </p>
                                    </div>
                                ) : (
                                    ordered.map((s) => (
                                        <div
                                            key={s.id}
                                            className={`relative border-b ${skin.border} ${
                                                s.is_active ? "" : "opacity-40"
                                            }`}
                                        >
                                            <SectionPreview
                                                section={s}
                                                mode={mode}
                                                skin={skin}
                                            />
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Section list / controls */}
                    <Card className="bg-white/80 dark:bg-zinc-900/80 rounded-[2rem] overflow-hidden shadow-none">
                        <CardContent className="p-3">
                            {ordered.length === 0 ? (
                                <div className="bg-zinc-50 dark:bg-zinc-900/50 rounded-4xl p-16 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                                    <LayoutTemplateLike />
                                    <p className="font-black text-lg text-zinc-400 mt-3">
                                        لا توجد أقسام
                                    </p>
                                </div>
                            ) : (
                                <div className="flex flex-col gap-2">
                                    {ordered.map((s, i) => {
                                        const meta = TYPES[s.name] || {};
                                        const Icon = meta.icon;
                                        return (
                                            <div
                                                key={s.id}
                                                className="flex items-center gap-3 p-3 rounded-2xl border border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors"
                                            >
                                                <div
                                                    className={`w-9 h-9 rounded-2xl ${meta.badge} flex items-center justify-center shrink-0`}
                                                >
                                                    {Icon ? (
                                                        <Icon
                                                            className={`w-4 h-4 ${meta.iconColor}`}
                                                        />
                                                    ) : null}
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <p className="font-bold text-sm truncate">
                                                        {meta.label || s.name}
                                                    </p>
                                                    <p className="text-[11px] text-zinc-400">
                                                        {s.name}
                                                    </p>
                                                </div>

                                                {/* reorder */}
                                                <div className="flex flex-col">
                                                    <button
                                                        type="button"
                                                        disabled={i === 0}
                                                        onClick={() =>
                                                            move(i, -1)
                                                        }
                                                        className="text-zinc-400 hover:text-blue-600 disabled:opacity-30"
                                                    >
                                                        <ChevronUp className="w-4 h-4" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        disabled={
                                                            i ===
                                                            ordered.length - 1
                                                        }
                                                        onClick={() =>
                                                            move(i, 1)
                                                        }
                                                        className="text-zinc-400 hover:text-blue-600 disabled:opacity-30"
                                                    >
                                                        <ChevronDown className="w-4 h-4" />
                                                    </button>
                                                </div>

                                                <Button
                                                    size="sm"
                                                    variant="flat"
                                                    color="primary"
                                                    startContent={
                                                        <Pencil className="w-3.5 h-3.5" />
                                                    }
                                                    onPress={() =>
                                                        setEditor({
                                                            type: s.name,
                                                            initial: s.content,
                                                            editingId: s.id,
                                                        })
                                                    }
                                                >
                                                    تعديل
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    className="text-rose-500"
                                                    startContent={
                                                        <Trash2 className="w-3.5 h-3.5" />
                                                    }
                                                    onPress={() =>
                                                        setDeleteTarget(s)
                                                    }
                                                >
                                                    حذف
                                                </Button>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Type picker modal */}
            <Modal
                isOpen={picking}
                onOpenChange={(o) => !o && setPicking(false)}
                placement="center"
                backdrop="blur"
            >
                <Modal.Backdrop>
                    <Modal.Container>
                        <Modal.Dialog className="max-w-lg w-full">
                            <Modal.CloseTrigger className="rtl:right-auto rtl:left-3 ltr:left-auto ltr:right-3" />
                            <Modal.Header className="font-black text-xl">
                                اختر نوع القسم
                            </Modal.Header>
                            <Modal.Body>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 p-1">
                                    {Object.entries(TYPES).map(
                                        ([type, meta]) => {
                                            const Icon = meta.icon;
                                            return (
                                                <button
                                                    key={type}
                                                    type="button"
                                                    onClick={() => {
                                                        setPicking(false);
                                                        setEditor({
                                                            type,
                                                            initial: null,
                                                            editingId: null,
                                                        });
                                                    }}
                                                    className="text-right p-4 rounded-2xl border border-zinc-200 dark:border-zinc-700 hover:border-blue-400 transition-colors flex items-start gap-3"
                                                >
                                                    <div
                                                        className={`w-10 h-10 rounded-2xl ${meta.badge} flex items-center justify-center shrink-0`}
                                                    >
                                                        <Icon
                                                            className={`w-5 h-5 ${meta.iconColor}`}
                                                        />
                                                    </div>
                                                    <div>
                                                        <p className="font-bold text-sm">
                                                            {meta.label}
                                                        </p>
                                                        <p className="text-[11px] text-zinc-400">
                                                            {meta.desc}
                                                        </p>
                                                    </div>
                                                </button>
                                            );
                                        },
                                    )}
                                </div>
                            </Modal.Body>
                        </Modal.Dialog>
                    </Modal.Container>
                </Modal.Backdrop>
            </Modal>

            {/* Editor modal */}
            {editor && (
                <EditorModal
                    open={!!editor}
                    type={editor.type}
                    initial={editor.initial}
                    editingId={editor.editingId}
                    page={currentPage}
                    onClose={() => setEditor(null)}
                />
            )}

            {/* New page modal */}
            <Modal
                isOpen={newPageOpen}
                onOpenChange={(o) => !o && setNewPageOpen(false)}
                placement="center"
                backdrop="blur"
            >
                <Modal.Backdrop>
                    <Modal.Container>
                        <Modal.Dialog className="max-w-md w-full">
                            <Modal.CloseTrigger className="rtl:right-auto rtl:left-3 ltr:left-auto ltr:right-3" />
                            <Modal.Header className="font-black text-xl">
                                صفحة جديدة
                            </Modal.Header>
                            <Modal.Body>
                                <div className="p-1 space-y-2">
                                    <Field
                                        label="معرّف الصفحة (slug)"
                                        value={newSlug}
                                        onChange={setNewSlug}
                                        placeholder="مثال: offers"
                                    />
                                    <p className="text-[11px] text-zinc-400 px-1">
                                        أحرف إنجليزية صغيرة وأرقام وشرطات فقط.
                                        ستظهر الصفحة في القائمة بعد إضافة أول
                                        قسم.
                                    </p>
                                </div>
                            </Modal.Body>
                            <Modal.Footer>
                                <Button
                                    variant="flat"
                                    onPress={() => setNewPageOpen(false)}
                                >
                                    إلغاء
                                </Button>
                                <Button color="primary" onPress={createPage}>
                                    متابعة
                                </Button>
                            </Modal.Footer>
                        </Modal.Dialog>
                    </Modal.Container>
                </Modal.Backdrop>
            </Modal>

            {/* Delete confirmation */}
            <Modal
                isOpen={!!deleteTarget}
                onOpenChange={(o) => !o && setDeleteTarget(null)}
                placement="center"
                backdrop="blur"
            >
                <Modal.Backdrop>
                    <Modal.Container>
                        <Modal.Dialog>
                            <Modal.Header className="font-black text-xl flex items-center gap-3">
                                <div className="w-9 h-9 rounded-2xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                                    <Trash2 className="w-4 h-4 text-rose-600 dark:text-rose-400" />
                                </div>
                                حذف القسم
                            </Modal.Header>
                            <Modal.Body>
                                <p className="text-sm text-zinc-500 p-2">
                                    هل أنت متأكد من حذف هذا القسم؟ لا يمكن التراجع
                                    عن العملية.
                                </p>
                            </Modal.Body>
                            <Modal.Footer>
                                <Button
                                    variant="flat"
                                    onPress={() => setDeleteTarget(null)}
                                >
                                    إلغاء
                                </Button>
                                <Button
                                    className="rounded-full font-bold px-6 bg-red-600 text-white"
                                    onPress={confirmDelete}
                                >
                                    تأكيد الحذف
                                </Button>
                            </Modal.Footer>
                        </Modal.Dialog>
                    </Modal.Container>
                </Modal.Backdrop>
            </Modal>
        </AdminLayout>
    );
}

// tiny helper so the empty-state icon stays consistent with the nav icon
function LayoutTemplateLike() {
    return (
        <GalleryHorizontalEnd className="mx-auto w-14 h-14 text-zinc-300" />
    );
}
