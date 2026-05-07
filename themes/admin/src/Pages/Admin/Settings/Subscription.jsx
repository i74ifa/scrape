import AdminLayout from "../../../Layouts/AdminLayout";
import { useState, useEffect, useRef } from "react";
import { useQuery } from "@tanstack/react-query";
import CurrentSubscriptionCard from "./Parts/CurrentSubscriptionCard";
import PlanCard from "./Parts/PlanCard";
import PaymentGatewayCard from "./Parts/PaymentGatewayCard";
import axios from "axios";
import { CreditCard, Zap, Shield, ChevronRight, Calendar } from "lucide-react";
import {
    Button,
    Spinner,
    AlertDialog,
    useOverlayState,
    Modal,
} from "@heroui/react";

const getSubscriptionData = async () => {
    const { data } = await axios.get(route("admin.subscription.data"));
    return data;
};



export default function Subscription({ plans, payments }) {
    const alertDialogState = useOverlayState();
    const confirmModalState = useOverlayState();

    const [alertConfig, setAlertConfig] = useState({
        title: "",
        message: "",
        status: "primary",
    });

    const [selectedPlan, setSelectedPlan] = useState(null);
    const [selectedGateway, setSelectedGateway] = useState(null);
    const [isProcessing, setIsProcessing] = useState(false);
    const [billingCycle, setBillingCycle] = useState("monthly");
    const [isSticky, setIsSticky] = useState(false);
    const sentinelRef = useRef(null);

    useEffect(() => {
        const observer = new IntersectionObserver(
            ([entry]) => {
                setIsSticky(!entry.isIntersecting);
            },
            { threshold: 0 },
        );
        if (sentinelRef.current) {
            observer.observe(sentinelRef.current);
        }
        return () => observer.disconnect();
    }, []);

    const showAlert = (title, message, status = "accent") => {
        setAlertConfig({ title, message, status });
        alertDialogState.open();
    };

    const {
        data: { tenant, subscription } = {},
        isLoading,
        isError,
    } = useQuery({
        queryKey: ["settings"],
        queryFn: getSubscriptionData,
    });

    const currentPlanSlug = subscription?.plan?.slug;

    const handleConfirmSubscription = () => {
        if (!selectedPlan) {
            showAlert("تنبيه", "يرجى اختيار خطة أولاً", "danger");
            return;
        }
        if (!selectedGateway) {
            showAlert("تنبيه", "يرجى اختيار طريقة الدفع", "danger");
            return;
        }
        confirmModalState.open();
    };

    const handleProcessPayment = async () => {
        setIsProcessing(true);
        try {
            const { data } = await axios.post(route("admin.subscription.pay"), {
                plan_price_id: selectedPrice?.id,
                payment_gateway_id: selectedGateway,
            });

            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                confirmModalState.close();
                showAlert(
                    "تم بنجاح",
                    "تم إنشاء عملية الدفع بنجاح",
                    "success",
                );
            }
        } catch (e) {
            confirmModalState.close();
            showAlert(
                "خطأ",
                e.response?.data?.message || "حدث خطأ أثناء معالجة الدفع",
                "danger",
            );
        } finally {
            setIsProcessing(false);
        }
    };

    const selectedPrice = selectedPlan?.selectedPrice;
    const selectedCurrency = selectedPrice?.currency ?? "IQD";

    if (isLoading) {
        return (
            <AdminLayout title="الاشتراك">
                <div className="flex items-center justify-center min-h-[50vh]">
                    <Spinner size="lg" />
                </div>
            </AdminLayout>
        );
    }

    if (isError) {
        return (
            <AdminLayout title="الاشتراك">
                <div className="text-red-500 text-center mt-10">
                    حدث خطأ أثناء تحميل بيانات الاشتراك
                </div>
            </AdminLayout>
        );
    }

    return (
        <AdminLayout title="الاشتراك">
            <div className="max-w-6xl mx-auto pb-20 px-4">
                {/* Sentinel to detect stickiness */}
                <div ref={sentinelRef} className="h-px" />

                {/* Header */}
                <div
                    id="subscription-header"
                    className={`border-2 border-transparent flex justify-between items-center mb-8 py-4 px-6 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-lg rounded-3xl sticky top-4 z-40 transition-all duration-300 ${
                        isSticky
                            ? "shadow-2xs border-gray-400/70 dark:border-gray-600/70 -translate-y-10"
                            : ""
                    }`}
                >
                    <h1 className="text-base md:text-2xl font-black text-zinc-800 dark:text-zinc-100 flex items-center gap-3">
                        <CreditCard className="text-blue-500" />
                        الاشتراك والدفع
                    </h1>
                    <Button
                        onPress={handleConfirmSubscription}
                        isDisabled={!selectedPlan || !selectedGateway}
                        className="rounded-full font-black h-11 bg-blue-600 text-white flex items-center justify-center rtl:flex-row-reverse disabled:opacity-50"
                    >
                        متابعة الدفع
                        <div className="rtl:-mr-3 ltr:-ml-3 h-9 w-9 rounded-2xl bg-blue-800 flex items-center justify-center">
                            <ChevronRight size={20} />
                        </div>
                    </Button>
                </div>

                <div className="space-y-6">
                    {/* Current Subscription */}
                    <div>
                        <h2 className="font-bold text-lg text-zinc-700 dark:text-zinc-300 mb-3 px-1 flex items-center gap-2">
                            <Shield size={18} className="text-emerald-500" />
                            اشتراكك الحالي
                        </h2>
                        <CurrentSubscriptionCard
                            subscription={subscription}
                            tenant={tenant}
                        />
                    </div>

                    {/* Plans */}
                    <div>
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 px-1">
                            <h2 className="font-bold text-lg text-zinc-700 dark:text-zinc-300 flex items-center gap-2">
                                <Zap size={18} className="text-blue-500" />
                                {subscription
                                    ? "تجديد أو تغيير الخطة"
                                    : "اختر الخطة المناسبة"}
                            </h2>

                            {/* Billing cycle toggle */}
                            <div className="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800 p-1 rounded-2xl self-start sm:self-auto">
                                <button
                                    onClick={() => {
                                        setBillingCycle("monthly");
                                        setSelectedPlan(null);
                                    }}
                                    className={`flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold transition-all ${
                                        billingCycle === "monthly"
                                            ? "bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm"
                                            : "text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300"
                                    }`}
                                >
                                    <Calendar size={13} />
                                    شهري
                                </button>
                                <button
                                    onClick={() => {
                                        setBillingCycle("yearly");
                                        setSelectedPlan(null);
                                    }}
                                    className={`flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold transition-all ${
                                        billingCycle === "yearly"
                                            ? "bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm"
                                            : "text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300"
                                    }`}
                                >
                                    <Calendar size={13} />
                                    سنوي
                                    <span className="text-[10px] font-black bg-emerald-500 text-white px-1.5 py-0.5 rounded-full">
                                        وفّر
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {plans.map((plan) => (
                                <PlanCard
                                    key={plan.id}
                                    plan={plan}
                                    currentPlanSlug={currentPlanSlug}
                                    isSelected={selectedPlan?.id === plan.id}
                                    onSelect={setSelectedPlan}
                                    billingCycle={billingCycle}
                                />
                            ))}
                        </div>
                    </div>

                    {/* Payment Gateway */}
                    {selectedPlan && (
                        <div>
                            <h2 className="font-bold text-lg text-zinc-700 dark:text-zinc-300 mb-3 px-1 flex items-center gap-2">
                                <CreditCard
                                    size={18}
                                    className="text-purple-500"
                                />
                                طريقة الدفع
                            </h2>
                            <div className="bg-white dark:bg-zinc-900 rounded-3xl p-6 border border-zinc-100 dark:border-zinc-800">
                                <div className="space-y-3">
                                    {payments.map((gw) => (
                                        <PaymentGatewayCard
                                            key={gw.id}
                                            gateway={gw}
                                            isSelected={
                                                selectedGateway === gw.id
                                            }
                                            onSelect={setSelectedGateway}
                                        />
                                    ))}
                                </div>

                                {/* Coming soon */}
                                <div className="mt-4 p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-dashed border-zinc-200 dark:border-zinc-700">
                                    <p className="text-xs text-zinc-400 dark:text-zinc-500 text-center">
                                        راح يتم تحويلك لصفحة الدفع الخاصة
                                        بالبوابة .
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Summary Box */}
                    {selectedPlan && selectedGateway && (
                        <div className="bg-linear-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-3xl p-6 border border-blue-100 dark:border-blue-900/50">
                            <h3 className="font-black text-lg text-zinc-800 dark:text-zinc-100 mb-4">
                                ملخص الطلب
                            </h3>
                            <div className="flex justify-between items-center text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                                <span>الخطة</span>
                                <span className="font-bold text-zinc-800 dark:text-zinc-200">
                                    {selectedPlan.name}
                                </span>
                            </div>
                            <div className="flex justify-between items-center text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                                <span>طريقة الدفع</span>
                                <span className="font-bold text-zinc-800 dark:text-zinc-200">
                                    {
                                        payments.find(
                                            (g) => g.id === selectedGateway,
                                        )?.name
                                    }
                                </span>
                            </div>
                            <div className="flex justify-between items-center text-sm text-zinc-600 dark:text-zinc-400 mb-4">
                                <span>فترة الاشتراك</span>
                                <span className="font-bold text-zinc-800 dark:text-zinc-200">
                                    {billingCycle === "yearly"
                                        ? "سنوي"
                                        : "شهري"}
                                </span>
                            </div>
                            <hr className="border-blue-100 dark:border-blue-900/50 mb-4" />
                            <div className="flex justify-between items-center">
                                <span className="font-black text-base text-zinc-700 dark:text-zinc-300">
                                    الإجمالي
                                </span>
                                <span className="font-black text-2xl text-blue-600 dark:text-blue-400">
                                    {selectedPrice?.amount?.toLocaleString()}{" "}
                                    <span className="text-sm font-bold">
                                        {selectedCurrency}
                                    </span>
                                </span>
                            </div>

                            <Button
                                onPress={handleConfirmSubscription}
                                className="mt-4 w-full rounded-2xl font-black h-12 bg-blue-600 text-white"
                            >
                                متابعة ودفع{" "}
                                {selectedPrice?.amount?.toLocaleString()}{" "}
                                {selectedCurrency}
                            </Button>
                        </div>
                    )}
                </div>
            </div>

            {/* Confirm Modal */}
            <Modal
                isOpen={confirmModalState.isOpen}
                onOpenChange={confirmModalState.setOpen}
                placement="center"
                backdrop="blur"
            >
                <Modal.Backdrop>
                    <Modal.Container>
                        <Modal.Dialog>
                            <Modal.CloseTrigger className="rtl:right-auto rtl:left-3 ltr:left-auto ltr:right-3" />
                            <Modal.Header className="font-black text-xl">
                                تأكيد الاشتراك
                            </Modal.Header>
                            <Modal.Body>
                                <div className="space-y-4">
                                    <div className="bg-zinc-50 dark:bg-zinc-800 rounded-2xl p-4 space-y-3">
                                        <div className="flex justify-between text-sm">
                                            <span className="text-zinc-500">
                                                الخطة
                                            </span>
                                            <span className="font-bold">
                                                {selectedPlan?.name}
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-zinc-500">
                                                فترة الاشتراك
                                            </span>
                                            <span className="font-bold">
                                                {billingCycle === "yearly"
                                                    ? "سنوي"
                                                    : "شهري"}
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-zinc-500">
                                                طريقة الدفع
                                            </span>
                                            <span className="font-bold">
                                                {
                                                    payments.find(
                                                        (g) =>
                                                            g.id ===
                                                            selectedGateway,
                                                    )?.name
                                                }
                                            </span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-zinc-500">
                                                المبلغ
                                            </span>
                                            <span className="font-black text-blue-600">
                                                {selectedPrice?.amount?.toLocaleString()}{" "}
                                                {selectedCurrency}
                                            </span>
                                        </div>
                                    </div>
                                    <p className="text-xs text-zinc-500 dark:text-zinc-400">
                                        عند التاكيد ستنتقل إلى صفحة دفع{" "}
                                        {
                                            payments.find(
                                                (g) => g.id === selectedGateway,
                                            )?.name
                                        }{" "}
                                        لإتمام عملية الدفع بشكل آمن.
                                    </p>
                                </div>
                            </Modal.Body>
                            <Modal.Footer>
                                <Button
                                    variant="tertiary"
                                    onPress={confirmModalState.close}
                                    isDisabled={isProcessing}
                                >
                                    إلغاء
                                </Button>
                                <Button
                                    className="bg-blue-600 text-white font-bold"
                                    isLoading={isProcessing}
                                    onPress={handleProcessPayment}
                                >
                                    تأكيد وادفع
                                </Button>
                            </Modal.Footer>
                        </Modal.Dialog>
                    </Modal.Container>
                </Modal.Backdrop>
            </Modal>

            {/* Alert Dialog */}
            <AlertDialog>
                <AlertDialog.Backdrop
                    isOpen={alertDialogState.isOpen}
                    onOpenChange={alertDialogState.setOpen}
                >
                    <AlertDialog.Container>
                        <AlertDialog.Dialog className="sm:max-w-100">
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
                                    إغلاق
                                </Button>
                            </AlertDialog.Footer>
                        </AlertDialog.Dialog>
                    </AlertDialog.Container>
                </AlertDialog.Backdrop>
            </AlertDialog>
        </AdminLayout>
    );
}
