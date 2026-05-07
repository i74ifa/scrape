import AdminLayout from "../../../Layouts/AdminLayout";
import { useState, useRef, useEffect, useCallback } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import SubscriptionAlert from "@/Components/SubscriptionAlert";
import axios from "axios";
import QrCodeWithLogo from "qrcode-with-logos";
import {
    Save,
    Image as ImageIcon,
    Info,
    Globe,
    Settings,
    Upload,
    Trash2,
    Search,
    CheckCircle,
    XCircle,
    AtSign,
    QrCode,
    Download,
    Printer,
} from "lucide-react";
import {
    Card,
    CardContent,
    Button,
    TextField,
    Input,
    Checkbox,
    Accordion,
    AccordionItem,
    Label,
    Spinner,
    Tooltip,
    Modal,
    AlertDialog,
    useOverlayState,
    InputGroup,
    Switch,
    toast,
    ColorPicker,
    ColorArea,
    ColorSlider,
    ColorSwatchPicker,
    ColorSwatch,
    ColorField,
    parseColor,
} from "@heroui/react";
import { router } from "@inertiajs/react";

const getSettings = async () => {
    const { data } = await axios.get(route("admin.settings.data"));
    return data;
};

const updateSettings = async (formData) => {
    const { data } = await axios.post(
        route("admin.settings.update"),
        formData,
        {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        },
    );
    return data;
};

export default function Index({ can }) {
    const queryClient = useQueryClient();
    const domainModalState = useOverlayState();

    // Alert Dialog State
    const alertDialogState = useOverlayState();
    const [alertConfig, setAlertConfig] = useState({
        title: "",
        message: "",
        status: "primary",
        isConfirm: false,
        onConfirm: null,
    });

    const showAlert = (title, message, status = "accent") => {
        setAlertConfig({
            title,
            message,
            status,
            isConfirm: false,
            onConfirm: null,
        });
        alertDialogState.open();
    };

    const confirmAction = (title, message, onConfirm, status = "danger") => {
        setAlertConfig({ title, message, status, isConfirm: true, onConfirm });
        alertDialogState.open();
    };

    const [domainQuery, setDomainQuery] = useState("");
    const [domainResult, setDomainResult] = useState(null);
    const [isSearchingDomain, setIsSearchingDomain] = useState(false);
    const [isBookingDomain, setIsBookingDomain] = useState(false);

    const [expandedKeys, setExpandedKeys] = useState(new Set(["one"]));

    const handleSearchDomain = async () => {
        if (!domainQuery) return;
        setIsSearchingDomain(true);
        setDomainResult(null);
        try {
            const { data } = await axios.post(
                route("admin.settings.check-domain"),
                { domain: domainQuery },
            );
            setDomainResult(data);
        } catch (error) {
            showAlert(
                "تنبيه",
                error.response?.data?.message || "حدث خطأ أثناء البحث",
                "danger",
            );
        } finally {
            setIsSearchingDomain(false);
        }
    };

    const handleBookDomain = async (onClose) => {
        setIsBookingDomain(true);
        try {
            const { data } = await axios.post(
                route("admin.settings.book-domain"),
                { domain: domainQuery },
            );
            showAlert(
                "نجاح",
                "سوف نقوم بالتواصل معك وإتمام كافة الخطوات اللازمة. هذا مجرد طلب حجز مبدئي.",
                "success",
            );
            queryClient.invalidateQueries(["settings"]);
            onClose();
            setDomainResult(null);
            setDomainQuery("");
        } catch (error) {
            showAlert(
                "تنبيه",
                error.response?.data?.message || "حدث خطأ أثناء الحجز",
                "danger",
            );
        } finally {
            setIsBookingDomain(false);
        }
    };

    const [isRemovingBooking, setIsRemovingBooking] = useState(false);

    const handleRemoveBooking = async (id) => {
        confirmAction(
            "تأكيد الإلغاء",
            "هل أنت متأكد من إلغاء طلب الحجز؟",
            async () => {
                setIsRemovingBooking(true);
                try {
                    const { data } = await axios.post(
                        route("admin.settings.remove-domain-booking"),
                        { id },
                    );
                    showAlert(
                        "نجاح",
                        data.message || "تم الإلغاء بنجاح",
                        "success",
                    );
                    queryClient.invalidateQueries(["settings"]);
                } catch (error) {
                    showAlert(
                        "تنبيه",
                        error.response?.data?.message ||
                            "حدث خطأ أثناء الإلغاء",
                        "danger",
                    );
                } finally {
                    setIsRemovingBooking(false);
                }
            },
        );
    };

    const {
        data: { tenant, domain_booking: domainBooking } = {},
        isLoading,
        isError,
    } = useQuery({
        queryKey: ["settings"],
        queryFn: getSettings,
    });

    const mutation = useMutation({
        mutationFn: updateSettings,
        onSuccess: (data) => {
            queryClient.invalidateQueries(["settings"]);
            showAlert("نجاح", "تم حفظ التعديلات بنجاح!", "success");
        },
        onError: (error) => {
            showAlert(
                "تنبيه",
                error.response?.data?.message || "حدث خطأ أثناء الحفظ",
                "danger",
            );
        },
    });

    // Form State
    const [formData, setFormData] = useState({
        name: "",
        phone: "",
        location: "",
        currency: "",
        instagram_link: "",
        tiktok_link: "",
        snapchat_link: "",
        whatsapp_link: "",
        facebook_link: "",
        twitter_link: "",
        youtube_link: "",
        telegram_link: "",
        currency_symbol_position: false,
        currency_k_format: false,
        menu_in_tatweer_menu: false,
        enable_orders: false,
        show_menu_item_available: true,
    });

    const [logoFile, setLogoFile] = useState(null);
    const [logoPreview, setLogoPreview] = useState(null);
    const [removeLogo, setRemoveLogo] = useState(false);
    const fileInputRef = useRef(null);

    // QR Code Generator State
    const qrCanvasRef = useRef(null);
    const qrImageRef = useRef(null);
    const qrLogoInputRef = useRef(null);
    const [dotsColor, setDotsColor] = useState(parseColor("#000000"));
    const [cornersColor, setCornersColor] = useState(parseColor("#000000"));
    const [qrConfig, setQrConfig] = useState({
        dotsType: "square",
        logoSrc: "",
    });

    const presetColors = [
        "#000000",
        "#ffffff",
        "#ef4444",
        "#f97316",
        "#eab308",
        "#22c55e",
        "#3b82f6",
        "#8b5cf6",
        "#ec4899",
        "#14b8a6",
        "#6366f1",
        "#78716c",
    ];
    const [qrLogoPreview, setQrLogoPreview] = useState(null);
    const [isSavingQr, setIsSavingQr] = useState(false);

    const dotTypes = [
        { value: "square", label: "مربع" },
        { value: "dot", label: "دائري" },
        { value: "dot-small", label: "دائري صغير" },
        { value: "rounded", label: "مدور" },
        { value: "diamond", label: "معين" },
        { value: "star", label: "نجمة" },
        { value: "fluid", label: "سائل" },
        { value: "fluid-line", label: "سائل خطي" },
        { value: "stripe", label: "شريطي" },
        { value: "tile", label: "بلاط" },
    ];

    const cornerTypes = [
        { value: "square", label: "مربع" },
        { value: "rounded", label: "مدور" },
        { value: "circle", label: "دائري" },
        { value: "rounded-circle", label: "مدور-دائري" },
        { value: "circle-rounded", label: "دائري-مدور" },
        { value: "circle-star", label: "دائري-نجمة" },
        { value: "circle-diamond", label: "دائري-معين" },
    ];

    const [cornersType, setCornersType] = useState("square");

    const generateQrCode = useCallback(() => {
        if (!qrCanvasRef.current || !qrImageRef.current || !tenant) return;

        const menuUrl = `${window.location.origin}/${tenant.slug}`;
        const dotsHex = dotsColor.toString("hex");
        const cornersHex = cornersColor.toString("hex");
        const opts = {
            canvas: qrCanvasRef.current,
            image: qrImageRef.current,
            content: menuUrl,
            width: 380,
            dotsOptions: {
                type: qrConfig.dotsType,
                color: dotsHex,
            },
            cornersOptions: {
                type: cornersType,
                color: cornersHex,
            },
            nodeQrCodeOptions: {
                errorCorrectionLevel: "H",
                margin: 2,
            },
        };

        if (qrConfig.logoSrc) {
            opts.logo = {
                src: qrConfig.logoSrc,
                logoRadius: 8,
                borderRadius: 8,
                borderWidth: 2,
                bgColor: "#ffffff",
            };
        }

        new QrCodeWithLogo(opts);
    }, [tenant, qrConfig, cornersType, dotsColor, cornersColor]);

    useEffect(() => {
        if (tenant) {
            generateQrCode();
        }
    }, [tenant, generateQrCode]);

    const handleQrLogoSelect = (file) => {
        if (!file || !file.type.startsWith("image/")) return;
        const url = URL.createObjectURL(file);
        setQrLogoPreview(url);
        setQrConfig((prev) => ({ ...prev, logoSrc: url }));
    };

    const handleRemoveQrLogo = () => {
        setQrLogoPreview(null);
        setQrConfig((prev) => ({ ...prev, logoSrc: "" }));
        if (qrLogoInputRef.current) qrLogoInputRef.current.value = "";
    };

    const handleDownloadQr = async () => {
        if (!qrImageRef.current?.src) return;
        const link = document.createElement("a");
        link.download = `${tenant?.name || "qrcode"}-qr.png`;
        link.href = qrImageRef.current.src;
        link.click();
    };

    const handlePrintQr = () => {
        if (!qrImageRef.current?.src) return;
        const printWindow = window.open("", "_blank");
        printWindow.document.write(`
            <html><head><title>QR Code</title>
            <style>body{display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;}img{max-width:90vw;max-height:90vh;}</style>
            </head><body><img src="${qrImageRef.current.src}" onload="window.print();window.close();" /></body></html>
        `);
        printWindow.document.close();
    };

    const handleSaveQrToServer = async () => {
        if (!qrCanvasRef.current) return;
        setIsSavingQr(true);
        try {
            const blob = await new Promise((resolve) =>
                qrCanvasRef.current.toBlob(resolve, "image/png"),
            );
            const fd = new FormData();
            fd.append("key", "qr_code");
            fd.append("qr_code", blob, "qr-code.png");
            await axios.post(route("admin.settings.update-by-key"), fd, {
                headers: { "Content-Type": "multipart/form-data" },
            });
            queryClient.invalidateQueries(["settings"]);
            toast.success("تم حفظ رمز QR بنجاح!");
        } catch (error) {
            toast.error(
                error.response?.data?.message || "حدث خطأ أثناء حفظ رمز QR",
            );
        } finally {
            setIsSavingQr(false);
        }
    };

    // Sync query data to form when loaded
    useEffect(() => {
        if (tenant) {
            setFormData({
                name: tenant.name || "",
                phone: tenant.phone || "",
                location: tenant.location || "",
                currency: tenant.currency || "",
                instagram_link: tenant.instagram_link || "",
                tiktok_link: tenant.tiktok_link || "",
                snapchat_link: tenant.snapchat_link || "",
                whatsapp_link: tenant.whatsapp_link || "",
                facebook_link: tenant.facebook_link || "",
                twitter_link: tenant.twitter_link || "",
                youtube_link: tenant.youtube_link || "",
                telegram_link: tenant.telegram_link || "",
                menu_in_tatweer_menu: !!tenant.menu_in_tatweer_menu,
                currency_symbol_position: !!tenant.currency_symbol_position,
                currency_k_format: !!tenant.currency_k_format,
                enable_orders: !!tenant.enable_orders,
                show_menu_item_available: !!tenant.show_menu_item_available,
            });
            setLogoPreview(tenant.logo ? `/storage/${tenant.logo}` : null);
            setLogoFile(null);
            setRemoveLogo(false);
        }
    }, [tenant]);

    const handleImageSelect = (file) => {
        if (!file || !file.type.startsWith("image/")) return;
        setLogoFile(file);
        setRemoveLogo(false);
        setLogoPreview(URL.createObjectURL(file));
    };

    const handleRemoveImage = () => {
        setLogoFile(null);
        setLogoPreview(null);
        setRemoveLogo(true);
        if (fileInputRef.current) fileInputRef.current.value = "";
    };

    const handleSubmit = (e) => {
        const fd = new FormData();
        Object.keys(formData).forEach((key) => {
            if (key === "menu_in_tatweer_menu") {
                fd.append(key, formData[key] ? 1 : 0);
            } else {
                fd.append(key, formData[key]);
            }
        });

        if (logoFile) {
            fd.append("logo", logoFile);
        } else if (removeLogo) {
            fd.append("remove_logo", 1);
        }

        mutation.mutate(fd);
    };

    if (isLoading) {
        return (
            <AdminLayout title="الإعدادات">
                <div className="flex items-center justify-center min-h-[50vh]">
                    <Spinner size="lg" />
                </div>
            </AdminLayout>
        );
    }

    if (isError) {
        return (
            <AdminLayout title="الإعدادات">
                <div className="text-red-500 text-center mt-10">
                    حدث خطأ أثناء تحميل الإعدادات
                </div>
            </AdminLayout>
        );
    }

    function updateSetting(key, value) {
        axios
            .post(route("admin.settings.update-by-key"), {
                key,
                value,
            })
            .then(() => {
                queryClient.invalidateQueries(["settings"]);
                toast.success("تم حفظ التعديلات بنجاح!");
            })
            .catch((error) => {
                toast.error(
                    error.response?.data?.message || "حدث خطأ أثناء الحفظ",
                );
            });
    }

    return (
        <AdminLayout title="الإعدادات">
            <div className="max-w-6xl mx-auto pb-20 px-4">
                <div
                    id="settings-header"
                    className="flex justify-between items-center mb-8 py-4 px-6 bg-white/60 backdrop:blur-lg dark:bg-zinc-900/80 backdrop-blur-lg rounded-3xl sticky top-4 z-40"
                >
                    <h1 className="text-base md:text-2xl font-black text-zinc-800 dark:text-zinc-100 flex items-center gap-3">
                        <Settings className="text-blue-500" /> إعدادات المطعم
                    </h1>
                    <Button
                        onPress={handleSubmit}
                        isDisabled={mutation.isPending}
                        className="rounded-full font-black h-11 bg-blue-600 text-white flex items-center justify-center rtl:flex-row-reverse"
                    >
                        {mutation.isPending ? "جاري الحفظ..." : "حفظ التعديلات"}
                        <div className="rtl:-mr-3 ltr:-ml-3 h-9 w-9 rounded-2xl bg-blue-800 flex items-center justify-center">
                            <Save size={20} />
                        </div>
                    </Button>
                </div>

                <Accordion
                    selectionMode="multiple"
                    className="bg-white dark:bg-zinc-900 rounded-3xl p-4"
                    defaultExpandedKeys={["1", "2"]}
                    variant="splitted"
                >
                    <AccordionItem
                        key="1"
                        aria-label="General Settings"
                        title={
                            <div className="flex items-center gap-3">
                                <Settings size={18} className="text-blue-500" />
                                <span className="font-bold text-lg">
                                    الإعدادات العامة
                                </span>
                            </div>
                        }
                    >
                        <div className="space-y-6 pt-2 pb-6 px-2">
                            {/* Logo */}
                            <div>
                                <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2 block">
                                    شعار المطعم
                                </Label>
                                <div className="flex items-center gap-6">
                                    <div className="relative w-24 h-24 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center border-2 border-dashed border-zinc-300 dark:border-zinc-700 overflow-hidden shrink-0">
                                        {logoPreview ? (
                                            <>
                                                <img
                                                    src={logoPreview}
                                                    alt="Logo preview"
                                                    className="w-full h-full object-cover"
                                                />
                                                <button
                                                    onClick={handleRemoveImage}
                                                    className="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full opacity-0 hover:opacity-100 transition-opacity"
                                                >
                                                    <Trash2 size={12} />
                                                </button>
                                            </>
                                        ) : (
                                            <ImageIcon
                                                size={24}
                                                className="text-zinc-400"
                                            />
                                        )}
                                    </div>
                                    <div className="flex-1">
                                        <input
                                            type="file"
                                            ref={fileInputRef}
                                            onChange={(e) =>
                                                handleImageSelect(
                                                    e.target.files[0],
                                                )
                                            }
                                            accept="image/*"
                                            className="hidden"
                                        />
                                        <Button
                                            variant="flat"
                                            onPress={() =>
                                                fileInputRef.current?.click()
                                            }
                                            className="rounded-full font-bold bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                                        >
                                            <Upload
                                                size={16}
                                                className="ml-2"
                                            />
                                            رفع صورة جديدة
                                        </Button>
                                        <p className="text-xs text-zinc-500 mt-2">
                                            يفضل أن تكون الصورة بصيغة PNG أو JPG
                                            وبحجم لا يتجاوز 2MB
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <TextField
                                    value={formData.name}
                                    onChange={(v) =>
                                        setFormData({ ...formData, name: v })
                                    }
                                >
                                    <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2">
                                        اسم المطعم
                                    </Label>
                                    <Input
                                        placeholder="اسم المطعم"
                                        className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl px-6 h-12 shadow-none"
                                    />
                                </TextField>

                                <TextField
                                    value={formData.phone}
                                    onChange={(v) =>
                                        setFormData({ ...formData, phone: v })
                                    }
                                >
                                    <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2">
                                        رقم الهاتف
                                    </Label>
                                    <Input
                                        placeholder="رقم الهاتف"
                                        className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl px-6 h-12 shadow-none"
                                    />
                                </TextField>

                                <TextField
                                    value={formData.location}
                                    onChange={(v) =>
                                        setFormData({
                                            ...formData,
                                            location: v,
                                        })
                                    }
                                >
                                    <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2">
                                        الموقع
                                    </Label>
                                    <Input
                                        placeholder="رابط الموقع أو العنوان"
                                        className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl px-6 h-12 shadow-none"
                                    />
                                </TextField>
                            </div>

                            <hr className="border-zinc-100 dark:border-zinc-800 border-dashed my-4" />

                            <h4 className="font-bold text-md mb-4 text-zinc-700 dark:text-zinc-300">
                                يوزرات التواصل الاجتماعي
                            </h4>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {[
                                    "instagram",
                                    "tiktok",
                                    "snapchat",
                                    "whatsapp",
                                    "facebook",
                                    "twitter",
                                    "youtube",
                                    "telegram",
                                ].map((platform) => (
                                    <TextField
                                        onChange={(v) => {
                                            setFormData({
                                                ...formData,
                                                [`${platform}_link`]: v,
                                            });
                                        }}
                                    >
                                        <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2 capitalize">
                                            {platform}
                                        </Label>
                                        <InputGroup
                                            dir="ltr"
                                            placeholder={`${platform} username`}
                                            className="bg-zinc-100 dark:bg-zinc-800 rounded-3xl h-12 shadow-none"
                                        >
                                            <InputGroup.Prefix>
                                                <AtSign
                                                    size={16}
                                                    className="text-zinc-400"
                                                />
                                            </InputGroup.Prefix>
                                            <InputGroup.Input
                                                value={
                                                    formData[`${platform}_link`]
                                                }
                                            />
                                        </InputGroup>
                                    </TextField>
                                ))}
                            </div>
                            <small className="text-zinc-500 dark:text-zinc-400">
                                فقط اليوزر بدون @
                            </small>

                            <hr className="border-zinc-100 dark:border-zinc-800 border-dashed my-4 " />

                            {false && (
                                <div className="flex items-center gap-3 bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-2xl">
                                    <Checkbox
                                        isSelected={formData.menu_in_tatweer_menu}
                                        onValueChange={(v) =>
                                        setFormData({
                                            ...formData,
                                            menu_in_tatweer_menu: v,
                                        })
                                    }
                                >
                                    إضافة المطعم في قائمة تطوير
                                </Checkbox>
                                <Tooltip>
                                    <Info size={16} />
                                    <Tooltip.Content>
                                        تفعيل هذا الخيار سيجعل مطعمك يظهر في
                                        الدليل العام لمطاعم منيو مما يزيد من
                                        وصول العملاء إليك.
                                    </Tooltip.Content>
                                </Tooltip>
                            </div>
                            )}
                        </div>
                    </AccordionItem>

                    <AccordionItem
                        key="3"
                        aria-label="QR Code Generator"
                        title={
                            <div className="flex items-center gap-3">
                                <QrCode size={18} className="text-purple-500" />
                                <span className="font-bold text-lg">
                                    مولّد رمز QR
                                </span>
                            </div>
                        }
                    >
                        <div className="space-y-6 pt-2 pb-6 px-2">
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                {/* QR Preview */}
                                <div className="flex flex-col items-center gap-4">
                                    <div className="bg-white p-4 rounded-2xl shadow-sm border border-zinc-100 dark:border-zinc-700">
                                        <canvas
                                            ref={qrCanvasRef}
                                            className="hidden"
                                        />
                                        <img
                                            ref={qrImageRef}
                                            alt="QR Code"
                                            className="w-72 h-72 object-contain"
                                        />
                                    </div>
                                    <div className="flex gap-3 w-full justify-center flex-wrap">
                                        <Button
                                            variant="flat"
                                            onPress={handleDownloadQr}
                                            className="rounded-full font-bold bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                                        >
                                            <Download
                                                size={16}
                                                className="ml-1"
                                            />
                                            تحميل
                                        </Button>
                                        <Button
                                            variant="flat"
                                            onPress={handlePrintQr}
                                            className="rounded-full font-bold bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                                        >
                                            <Printer
                                                size={16}
                                                className="ml-1"
                                            />
                                            طباعة
                                        </Button>
                                        <Button
                                            variant="flat"
                                            onPress={handleSaveQrToServer}
                                            isLoading={isSavingQr}
                                            className="rounded-full font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                                        >
                                            <Save size={16} className="ml-1" />
                                            حفظ
                                        </Button>
                                    </div>
                                </div>

                                {/* QR Options */}
                                <div className="space-y-5">
                                    {/* Logo */}
                                    <div>
                                        <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2 block">
                                            شعار داخل الـ QR
                                        </Label>
                                        <div className="flex items-center gap-4">
                                            <div className="relative w-16 h-16 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center border-2 border-dashed border-zinc-300 dark:border-zinc-700 overflow-hidden shrink-0">
                                                {qrLogoPreview ? (
                                                    <>
                                                        <img
                                                            src={qrLogoPreview}
                                                            alt="QR Logo"
                                                            className="w-full h-full object-cover"
                                                        />
                                                        <button
                                                            onClick={
                                                                handleRemoveQrLogo
                                                            }
                                                            className="absolute top-0.5 right-0.5 p-0.5 bg-red-500 text-white rounded-full opacity-0 hover:opacity-100 transition-opacity"
                                                        >
                                                            <Trash2 size={10} />
                                                        </button>
                                                    </>
                                                ) : (
                                                    <ImageIcon
                                                        size={18}
                                                        className="text-zinc-400"
                                                    />
                                                )}
                                            </div>
                                            <div>
                                                <input
                                                    type="file"
                                                    ref={qrLogoInputRef}
                                                    onChange={(e) =>
                                                        handleQrLogoSelect(
                                                            e.target.files[0],
                                                        )
                                                    }
                                                    accept="image/*"
                                                    className="hidden"
                                                />
                                                <Button
                                                    variant="flat"
                                                    size="sm"
                                                    onPress={() =>
                                                        qrLogoInputRef.current?.click()
                                                    }
                                                    className="rounded-full font-bold bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                                                >
                                                    <Upload
                                                        size={14}
                                                        className="ml-1"
                                                    />
                                                    رفع شعار
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Colors */}
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2 block">
                                                لون النقاط
                                            </Label>
                                            <ColorPicker.Root
                                                value={dotsColor}
                                                onChange={setDotsColor}
                                            >
                                                <ColorPicker.Trigger />
                                                <ColorPicker.Popover placement="bottom start">
                                                    <div className="flex flex-col gap-3 p-1">
                                                        <ColorArea.Root
                                                            colorSpace="hsb"
                                                            xChannel="saturation"
                                                            yChannel="brightness"
                                                            className="w-full h-36 rounded-xl"
                                                        >
                                                            <ColorArea.Thumb />
                                                        </ColorArea.Root>
                                                        <ColorSlider.Root
                                                            channel="hue"
                                                            colorSpace="hsb"
                                                        >
                                                            <ColorSlider.Track className="rounded-lg">
                                                                <ColorSlider.Thumb />
                                                            </ColorSlider.Track>
                                                        </ColorSlider.Root>
                                                        <ColorSwatchPicker.Root
                                                            layout="grid"
                                                            className="gap-1.5"
                                                        >
                                                            {presetColors.map(
                                                                (c) => (
                                                                    <ColorSwatchPicker.Item
                                                                        key={c}
                                                                        color={
                                                                            c
                                                                        }
                                                                    >
                                                                        <ColorSwatch.Root
                                                                            shape="circle"
                                                                            size="sm"
                                                                        />
                                                                    </ColorSwatchPicker.Item>
                                                                ),
                                                            )}
                                                        </ColorSwatchPicker.Root>
                                                        <ColorField.Root
                                                            colorSpace="hsb"
                                                            className="mt-1"
                                                        >
                                                            <ColorField.Input className="text-xs font-mono" />
                                                        </ColorField.Root>
                                                    </div>
                                                </ColorPicker.Popover>
                                            </ColorPicker.Root>
                                        </div>
                                        <div>
                                            <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2 block">
                                                لون الأركان
                                            </Label>
                                            <ColorPicker.Root
                                                value={cornersColor}
                                                onChange={setCornersColor}
                                            >
                                                <ColorPicker.Trigger />
                                                <ColorPicker.Popover placement="bottom start">
                                                    <div className="flex flex-col gap-3 p-1">
                                                        <ColorArea.Root
                                                            colorSpace="hsb"
                                                            xChannel="saturation"
                                                            yChannel="brightness"
                                                            className="w-full h-36 rounded-xl"
                                                        >
                                                            <ColorArea.Thumb />
                                                        </ColorArea.Root>
                                                        <ColorSlider.Root
                                                            channel="hue"
                                                            colorSpace="hsb"
                                                        >
                                                            <ColorSlider.Track className="rounded-lg">
                                                                <ColorSlider.Thumb />
                                                            </ColorSlider.Track>
                                                        </ColorSlider.Root>
                                                        <ColorSwatchPicker.Root
                                                            layout="grid"
                                                            className="gap-1.5"
                                                        >
                                                            {presetColors.map(
                                                                (c) => (
                                                                    <ColorSwatchPicker.Item
                                                                        key={c}
                                                                        color={
                                                                            c
                                                                        }
                                                                    >
                                                                        <ColorSwatch.Root
                                                                            shape="circle"
                                                                            size="sm"
                                                                        />
                                                                    </ColorSwatchPicker.Item>
                                                                ),
                                                            )}
                                                        </ColorSwatchPicker.Root>
                                                        <ColorField.Root
                                                            colorSpace="hsb"
                                                            className="mt-1"
                                                        >
                                                            <ColorField.Input className="text-xs font-mono" />
                                                        </ColorField.Root>
                                                    </div>
                                                </ColorPicker.Popover>
                                            </ColorPicker.Root>
                                        </div>
                                    </div>

                                    {/* Dots Type */}
                                    <div>
                                        <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2 block">
                                            شكل النقاط
                                        </Label>
                                        <div className="flex flex-wrap gap-2">
                                            {dotTypes.map((dt) => (
                                                <Button
                                                    key={dt.value}
                                                    size="sm"
                                                    variant={
                                                        qrConfig.dotsType ===
                                                        dt.value
                                                            ? "solid"
                                                            : "flat"
                                                    }
                                                    color={
                                                        qrConfig.dotsType ===
                                                        dt.value
                                                            ? "primary"
                                                            : "default"
                                                    }
                                                    onPress={() =>
                                                        setQrConfig((prev) => ({
                                                            ...prev,
                                                            dotsType: dt.value,
                                                        }))
                                                    }
                                                    className="rounded-full text-xs font-bold"
                                                >
                                                    {dt.label}
                                                </Button>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Corners Type */}
                                    <div>
                                        <Label className="font-black text-sm uppercase tracking-widest text-zinc-500 mb-2 pl-2 block">
                                            شكل الأركان
                                        </Label>
                                        <div className="flex flex-wrap gap-2">
                                            {cornerTypes.map((ct) => (
                                                <Button
                                                    key={ct.value}
                                                    size="sm"
                                                    variant={
                                                        cornersType === ct.value
                                                            ? "solid"
                                                            : "flat"
                                                    }
                                                    color={
                                                        cornersType === ct.value
                                                            ? "primary"
                                                            : "default"
                                                    }
                                                    onPress={() =>
                                                        setCornersType(ct.value)
                                                    }
                                                    className="rounded-full text-xs font-bold"
                                                >
                                                    {ct.label}
                                                </Button>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </AccordionItem>
                    <div className="p-4">
                        <div className="flex items-center gap-3 mb-3">
                            <Globe size={18} className="text-emerald-500" />
                            <span className="font-bold text-lg">
                                خيارات التطبيق
                            </span>
                        </div>
                        <div className="space-y-4 pt-2 pb-6 px-2">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <label htmlFor="currency_symbol_position">
                                    موضع رمز العملة
                                </label>
                                <Switch
                                    isSelected={formData.currency_symbol_position}
                                    size="lg"
                                    onChange={(e) => {
                                        setFormData((prev) => ({ ...prev, currency_symbol_position: e }));
                                        updateSetting("currency_symbol_position", e);
                                    }}
                                >
                                    {({ isSelected }) => (
                                        <>
                                            <Switch.Control>
                                                <Switch.Thumb />
                                            </Switch.Control>
                                            <Switch.Content>
                                                <Label className="text-xs">
                                                    {isSelected
                                                        ? "بعد السعر (100د.ع)"
                                                        : "قبل السعر (د.ع100)"}
                                                </Label>
                                            </Switch.Content>
                                        </>
                                    )}
                                </Switch>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="flex items-center gap-2">
                                    <label htmlFor="enable_orders">
                                        تفعيل الطلبات
                                    </label>
                                </div>
                                <Switch
                                    isSelected={formData.enable_orders}
                                    size="lg"
                                    onChange={(e) => {
                                        setFormData((prev) => ({ ...prev, enable_orders: e }));
                                        updateSetting("enable_orders", e);
                                    }}
                                >
                                    {({ isSelected }) => (
                                        <>
                                            <Switch.Control>
                                                <Switch.Thumb />
                                            </Switch.Control>
                                            <Switch.Content>
                                                <Label className="text-xs">
                                                    {isSelected
                                                        ? "مفعل"
                                                        : "غير مفعل"}
                                                </Label>
                                            </Switch.Content>
                                        </>
                                    )}
                                </Switch>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="flex items-center gap-2">
                                    <Tooltip delay={0}>
                                        <Tooltip.Trigger>
                                            <Info size={16} />
                                        </Tooltip.Trigger>
                                        <Tooltip.Content>
                                            عند التعطيل سيتم إخفاء الأصناف غير
                                            المتاحة من قائمة وعند التفعيل سوف تنعرض لكن مع علامة تدل على عدم توفرها.
                                        </Tooltip.Content>
                                    </Tooltip>
                                    <label htmlFor="show_menu_item_available">
                                        عرض الأصناف غير المتاحة
                                    </label>
                                </div>
                                <Switch
                                    isSelected={formData.show_menu_item_available}
                                    size="lg"
                                    onChange={(e) => {
                                        setFormData((prev) => ({
                                            ...prev,
                                            show_menu_item_available: e,
                                        }));
                                        updateSetting("show_menu_item_available", e);
                                    }}
                                >
                                    {({ isSelected }) => (
                                        <>
                                            <Switch.Control>
                                                <Switch.Thumb />
                                            </Switch.Control>
                                            <Switch.Content>
                                                <Label className="text-xs">
                                                    {isSelected
                                                        ? "عرض الكل"
                                                        : "عرض المتاح فقط"}
                                                </Label>
                                            </Switch.Content>
                                        </>
                                    )}
                                </Switch>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="flex items-center gap-2">
                                    <Tooltip delay={0}>
                                        <Tooltip.Trigger>
                                            <Info size={16} />
                                        </Tooltip.Trigger>
                                        <Tooltip.Content>
                                            سوف يحذف الاصفار من العملة فقط في
                                            العرض بعض الامثلة:
                                            <br />
                                            1000 = 1
                                            <br />
                                            15500 = 15.5
                                            <br />
                                            155000 = 155
                                            <br />
                                            (لايتاثر الحساب بهذا)
                                        </Tooltip.Content>
                                    </Tooltip>
                                    <label htmlFor="currency_k_format">
                                        حذف الاصفار من السعر
                                    </label>
                                </div>
                                <Switch
                                    isSelected={formData.currency_k_format}
                                    size="lg"
                                    onChange={(e) => {
                                        setFormData((prev) => ({ ...prev, currency_k_format: e }));
                                        updateSetting("currency_k_format", e);
                                    }}
                                >
                                    {({ isSelected }) => (
                                        <>
                                            <Switch.Control>
                                                <Switch.Thumb />
                                            </Switch.Control>
                                            <Switch.Content>
                                                <Label className="text-xs">
                                                    {isSelected
                                                        ? "بدون اصفار (10 د.ع)"
                                                        : "صيغة كاملة (د.ع 10،000)"}
                                                </Label>
                                            </Switch.Content>
                                        </>
                                    )}
                                </Switch>
                            </div>
                        </div>
                    </div>

                    <AccordionItem
                        key="2"
                        aria-label="Domain Settings"
                        title={
                            <div className="flex items-center gap-3">
                                <Globe size={18} className="text-emerald-500" />
                                <span className="font-bold text-lg">
                                    إعدادات النطاق (الدومين)
                                </span>
                            </div>
                        }
                    >
                        <div className={"relative space-y-4 pt-2 pb-6 px-2 "}>
                            <div
                                className={
                                    "absolute inset-0 z-10 " +
                                    (can?.domain ? "hidden" : "")
                                }
                            ></div>

                            {!can?.domain && (
                                <SubscriptionAlert
                                    title="باقتك ماتشمل"
                                    message="الباقة الحالية لا تدعم هذه الميزة"
                                    buttonText="ترقية الباقة"
                                />
                            )}

                            <Card
                                className={
                                    "bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100 dark:border-emerald-900/50 shadow-none " +
                                    (can?.domain ? "" : "opacity-50")
                                }
                            >
                                <CardContent className="p-6">
                                    <h4 className="font-bold text-emerald-800 dark:text-emerald-400 mb-2 text-lg">
                                        طريقة تغيير نطاق المطعم الخاص بك
                                    </h4>
                                    <p className="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed mb-4">
                                        يمكنك الدخول إلى المطعم الخاص بك عن طريق
                                        عنوان فرعي خاص يعطى لك عند التسجيل. إذا
                                        كنت ترغب في استخدام نطاقك الخاص (مثل
                                        www.yourrestaurant.com)، اتبع هذه
                                        الخطوات:
                                    </p>

                                    <div className="space-y-4">
                                        <div className="bg-white dark:bg-zinc-900 p-4 rounded-2xl ">
                                            <h5 className="font-bold text-sm mb-2 text-zinc-800 dark:text-zinc-100">
                                                الخيار الأول: لديك نطاق بالفعل
                                            </h5>
                                            <p className="text-xs text-zinc-500 mb-2">
                                                قم بتوجيه سجل الـ DNS (A Record)
                                                الخاص بنطاقك إلى عنوان الـ IP
                                                الخاص بنا:{" "}
                                                <code>192.168.1.1</code> (يرجى
                                                التواصل مع الدعم الفني لتأكيد
                                                الرقم).
                                            </p>
                                        </div>
                                        {domainBooking ? (
                                            <div className="bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <h5 className="font-bold text-sm mb-1 text-emerald-800 dark:text-emerald-400">
                                                            تم طلب حجز النطاق:{" "}
                                                            {
                                                                domainBooking.domain_name
                                                            }
                                                        </h5>
                                                        <p className="text-xs text-emerald-600 dark:text-emerald-500">
                                                            الحالة:{" "}
                                                            {domainBooking.status ===
                                                            "pending"
                                                                ? "جاري المعالجة"
                                                                : "مفعل"}
                                                        </p>
                                                    </div>
                                                    <Button
                                                        color="danger"
                                                        variant="flat"
                                                        size="sm"
                                                        isLoading={
                                                            isRemovingBooking
                                                        }
                                                        onPress={() =>
                                                            handleRemoveBooking(
                                                                domainBooking.id,
                                                            )
                                                        }
                                                    >
                                                        إلغاء الحجز
                                                    </Button>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="bg-white dark:bg-zinc-900 p-4 rounded-2xl">
                                                <h5 className="font-bold text-sm mb-2 text-zinc-800 dark:text-zinc-200">
                                                    الخيار الثاني: شراء نطاق من
                                                    تطوير
                                                </h5>
                                                <p className="text-xs text-zinc-500 mb-2">
                                                    نوفر لك خدمة شراء وربط
                                                    النطاقات بشكل تلقائي وبدون
                                                    عناء. تواصل مع مبيعات تطوير
                                                    لمعرفة الباقات المتاحة.
                                                </p>
                                                <Button
                                                    onPress={
                                                        domainModalState.open
                                                    }
                                                    className="mt-4 bg-emerald-600 text-white font-bold rounded-xl w-full sm:w-auto"
                                                    startContent={
                                                        <Search size={16} />
                                                    }
                                                >
                                                    البحث عن دومين متاح
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </AccordionItem>
                </Accordion>
            </div>

            <Modal
                isOpen={domainModalState.isOpen}
                onOpenChange={domainModalState.setOpen}
                placement="center"
                backdrop="blur"
            >
                <Modal.Backdrop>
                    <Modal.Container>
                        <Modal.Dialog>
                            <Modal.CloseTrigger className="rtl:right-auto rtl:left-3 ltr:left-auto ltr:right-3" />
                            <Modal.Header className="flex flex-col gap-1 font-black text-xl">
                                البحث عن نطاق (دومين)
                            </Modal.Header>
                            <Modal.Body>
                                <p className="text-sm text-zinc-500 mb-2">
                                    أدخل اسم النطاق الذي ترغب في حجزه للتحقق من
                                    توفره (مثال: shopini.com)
                                </p>
                                <div className="flex gap-2 py-2">
                                    <Input
                                        dir="ltr"
                                        placeholder="altatweer.tech"
                                        value={domainQuery}
                                        onChange={(e) =>
                                            setDomainQuery(e.target.value)
                                        }
                                        className="flex-1 mx-2"
                                        size="lg"
                                        disabled={isSearchingDomain}
                                        onKeyDown={(e) =>
                                            e.key === "Enter" &&
                                            handleSearchDomain()
                                        }
                                    />
                                    <Button
                                        isIconOnly
                                        size="lg"
                                        color="primary"
                                        isLoading={isSearchingDomain}
                                        onPress={handleSearchDomain}
                                        isPending={isSearchingDomain}
                                    >
                                        {!isSearchingDomain && (
                                            <Search size={20} />
                                        )}
                                    </Button>
                                </div>

                                {domainResult && (
                                    <div
                                        className={`mt-4 p-4 rounded-2xl border ${domainResult.available ? "bg-emerald-50 border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800" : "bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800"}`}
                                    >
                                        <div className="flex items-center gap-3">
                                            {domainResult.available ? (
                                                <CheckCircle
                                                    className="text-emerald-500 shrink-0"
                                                    size={24}
                                                />
                                            ) : (
                                                <XCircle
                                                    className="text-red-500 shrink-0"
                                                    size={24}
                                                />
                                            )}
                                            <div>
                                                <h4
                                                    className={`font-bold ${domainResult.available ? "text-emerald-700 dark:text-emerald-400" : "text-red-700 dark:text-red-400"}`}
                                                >
                                                    {domainResult.available
                                                        ? "الدومين متاح للحجز!"
                                                        : "الدومين غير متاح"}
                                                </h4>
                                                {!domainResult.available &&
                                                    domainResult.owner && (
                                                        <p className="text-sm text-red-600/80 dark:text-red-400/80 mt-1 flex flex-col">
                                                            <span>
                                                                محجوز بواسطة:{" "}
                                                                {
                                                                    domainResult
                                                                        .owner
                                                                        ?.phone
                                                                }
                                                            </span>
                                                            {domainResult.expires && (
                                                                <span>
                                                                    تاريخ
                                                                    الانتهاء:{" "}
                                                                    {new Date(
                                                                        domainResult.expires,
                                                                    ).toLocaleDateString()}
                                                                </span>
                                                            )}
                                                        </p>
                                                    )}
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </Modal.Body>
                            <Modal.Footer>
                                <Button
                                    color="danger"
                                    variant="light"
                                    onPress={domainModalState.close}
                                >
                                    إغلاق
                                </Button>
                                {domainResult?.available && (
                                    <Button
                                        color="success"
                                        className="text-white font-bold"
                                        isLoading={isBookingDomain}
                                        onPress={() =>
                                            handleBookDomain(
                                                domainModalState.close,
                                            )
                                        }
                                    >
                                        حجز
                                    </Button>
                                )}
                            </Modal.Footer>
                        </Modal.Dialog>
                    </Modal.Container>
                </Modal.Backdrop>
            </Modal>

            <AlertDialog>
                <AlertDialog.Backdrop
                    isOpen={alertDialogState.isOpen}
                    onOpenChange={alertDialogState.setOpen}
                >
                    <AlertDialog.Container>
                        <AlertDialog.Dialog className="sm:max-w-[400px]">
                            <AlertDialog.CloseTrigger className="rtl:right-auto rtl:left-3 ltr:left-auto ltr:right-3" />
                            <AlertDialog.Header>
                                <AlertDialog.Icon status={alertConfig.status} />
                                <AlertDialog.Heading className="font-bold">
                                    {alertConfig.title}
                                </AlertDialog.Heading>
                            </AlertDialog.Header>
                            <AlertDialog.Body>
                                <p className="text-sm dark:text-zinc-300">
                                    {alertConfig.message}
                                </p>
                            </AlertDialog.Body>
                            <AlertDialog.Footer>
                                <Button
                                    variant="tertiary"
                                    onPress={alertDialogState.close}
                                >
                                    {alertConfig.isConfirm ? "إلغاء" : "إغلاق"}
                                </Button>
                                {alertConfig.isConfirm && (
                                    <Button
                                        color={
                                            alertConfig.status === "danger"
                                                ? "danger"
                                                : "primary"
                                        }
                                        onPress={() => {
                                            if (alertConfig.onConfirm) {
                                                alertConfig.onConfirm();
                                            }
                                            alertDialogState.close();
                                        }}
                                    >
                                        تأكيد
                                    </Button>
                                )}
                            </AlertDialog.Footer>
                        </AlertDialog.Dialog>
                    </AlertDialog.Container>
                </AlertDialog.Backdrop>
            </AlertDialog>
        </AdminLayout>
    );
}
