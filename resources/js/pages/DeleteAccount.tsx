import { useState } from "react";
import axios from "axios";
import Layout from "../layout";
import { Card } from "../components/Card";
import { Button } from "../components/Button";

type Step = "phone" | "otp" | "done";

export default function DeleteAccount() {
    const [step, setStep] = useState<Step>("phone");
    const [phone, setPhone] = useState("");
    const [countryCode, setCountryCode] = useState("+967");
    const [otp, setOtp] = useState("");
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);

    const sendOtp = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setError(null);
        setLoading(true);
        try {
            await axios.post("/api/account-delete/send-otp", {
                phone,
                country_code: countryCode,
            });
            setStep("otp");
        } catch (err: any) {
            setError(err.response?.data?.message || "حدث خطأ، حاول مرة أخرى");
        } finally {
            setLoading(false);
        }
    };

    const confirmDelete = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setError(null);
        setLoading(true);
        try {
            const res = await axios.post("/api/account-delete/confirm", {
                phone,
                country_code: countryCode,
                otp,
            });
            setSuccess(res.data.message);
            setStep("done");
        } catch (err: any) {
            setError(err.response?.data?.message || "رمز غير صحيح أو منتهي");
        } finally {
            setLoading(false);
        }
    };

    return (
        <Layout>
            <main className="relative z-10">
                <section className="container mx-auto px-6 py-12 md:py-24 animate-fade-in">
                    <Card className="max-w-2xl mx-auto border border-border-subtle bg-surface/80 backdrop-blur-md">
                        <div className="text-right space-y-6">
                            <header className="flex items-center justify-start gap-3 mb-2">
                                <div className="w-12 h-12 bg-red-500/20 text-red-500 rounded-2xl flex items-center justify-center">
                                    <TrashIcon />
                                </div>
                                <h1 className="text-3xl md:text-4xl font-bold">
                                    حذف الحساب
                                </h1>
                            </header>

                            <div className="bg-red-500/10 border border-red-500/30 rounded-2xl p-5 text-text-dimmed leading-relaxed">
                                <p className="font-bold text-red-400 mb-2">
                                    تنبيه قبل الحذف
                                </p>
                                <ul className="list-disc list-inside space-y-1 text-sm">
                                    <li>
                                        سيتم حذف حسابك مع جميع بياناتك (العناوين،
                                        السلة، الطلبات، التفضيلات) خلال 30 يوماً.
                                    </li>
                                    <li>
                                        خلال هذه المدة يمكنك التواصل مع الدعم
                                        لاستعادة حسابك.
                                    </li>
                                    <li>
                                        بعد 30 يوماً يصبح الحذف نهائي ولا يمكن
                                        التراجع عنه.
                                    </li>
                                </ul>
                            </div>

                            {step === "phone" && (
                                <PhoneStep
                                    phone={phone}
                                    countryCode={countryCode}
                                    onPhoneChange={setPhone}
                                    onCountryCodeChange={setCountryCode}
                                    onSubmit={sendOtp}
                                    loading={loading}
                                    error={error}
                                />
                            )}

                            {step === "otp" && (
                                <OtpStep
                                    countryCode={countryCode}
                                    phone={phone}
                                    otp={otp}
                                    onOtpChange={setOtp}
                                    onSubmit={confirmDelete}
                                    onBack={() => {
                                        setStep("phone");
                                        setOtp("");
                                        setError(null);
                                    }}
                                    loading={loading}
                                    error={error}
                                />
                            )}

                            {step === "done" && <DoneStep message={success} />}
                        </div>
                    </Card>
                </section>
            </main>
        </Layout>
    );
}

function PhoneStep(props: {
    phone: string;
    countryCode: string;
    onPhoneChange: (v: string) => void;
    onCountryCodeChange: (v: string) => void;
    onSubmit: (e: React.FormEvent<HTMLFormElement>) => void;
    loading: boolean;
    error: string | null;
}) {
    return (
        <form onSubmit={props.onSubmit} className="space-y-4">
            <label className="block text-sm font-medium text-text-dimmed">
                رقم الهاتف المسجل
            </label>
            <div className="flex gap-3">
                <input
                    type="tel"
                    value={props.phone}
                    onChange={(e) => props.onPhoneChange(e.target.value)}
                    placeholder="7xxxxxxxx"
                    required
                    className="flex-1 bg-background/80 border-2 border-border-subtle rounded-2xl px-4 py-3 text-text-main focus:border-brand-primary outline-none"
                />
                <input
                    type="text"
                    disabled={true}
                    value={props.countryCode}
                    placeholder="+967"
                    dir="ltr"
                    className="w-24 bg-background/80 border-2 border-border-subtle rounded-2xl px-4 py-3 text-center text-text-main focus:border-brand-primary outline-none"
                />
            </div>
            {props.error && (
                <div className="text-red-500 text-sm">{props.error}</div>
            )}
            <Button
                type="submit"
                className="w-full bg-red-600 hover:bg-red-700"
            >
                {props.loading ? "جاري الإرسال..." : "إرسال رمز التحقق"}
            </Button>
        </form>
    );
}

function OtpStep(props: {
    countryCode: string;
    phone: string;
    otp: string;
    onOtpChange: (v: string) => void;
    onSubmit: (e: React.FormEvent<HTMLFormElement>) => void;
    onBack: () => void;
    loading: boolean;
    error: string | null;
}) {
    return (
        <form onSubmit={props.onSubmit} className="space-y-4">
            <p className="text-text-dimmed text-sm">
                تم إرسال رمز التحقق إلى {props.countryCode}
                {props.phone}
            </p>
            <label className="block text-sm font-medium text-text-dimmed">
                رمز التحقق (4 أرقام)
            </label>
            <input
                type="tel"
                inputMode="numeric"
                maxLength={4}
                value={props.otp}
                onChange={(e) =>
                    props.onOtpChange(e.target.value.replace(/\D/g, ""))
                }
                placeholder="0000"
                required
                className="w-full bg-background/80 border-2 border-border-subtle rounded-2xl px-4 py-3 text-center text-2xl tracking-[0.5em] text-text-main focus:border-brand-primary outline-none"
            />
            {props.error && (
                <div className="text-red-500 text-sm">{props.error}</div>
            )}
            <div className="flex gap-3">
                <Button
                    type="button"
                    variant="outline"
                    onClick={props.onBack}
                    className="flex-1"
                >
                    رجوع
                </Button>
                <Button
                    type="submit"
                    className="flex-1 bg-red-600 hover:bg-red-700"
                >
                    {props.loading ? "جاري الحذف..." : "تأكيد حذف الحساب"}
                </Button>
            </div>
        </form>
    );
}

function DoneStep({ message }: { message: string | null }) {
    return (
        <div className="bg-green-500/10 border border-green-500/30 rounded-2xl p-6 text-center">
            <div className="w-16 h-16 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <CheckIcon />
            </div>
            <p className="text-green-400 font-bold text-lg mb-2">
                تم استلام طلب الحذف
            </p>
            <p className="text-text-dimmed text-sm">{message}</p>
        </div>
    );
}

function TrashIcon() {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-7 w-7"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"
            />
        </svg>
    );
}

function CheckIcon() {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-9 w-9"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M5 13l4 4L19 7"
            />
        </svg>
    );
}
