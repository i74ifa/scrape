import { useForm, Head, usePage, router } from "@inertiajs/react";
import { UtensilsCrossed, Mail, Lock, Eye, EyeOff, Fingerprint } from "lucide-react";
import { Button, Card, CardContent, Checkbox } from "@heroui/react";
import { useState } from "react";

// Base64url helpers for native WebAuthn API
function base64urlToBuffer(base64url) {
    const base64 = base64url.replace(/-/g, "+").replace(/_/g, "/");
    const binary = atob(base64);
    return Uint8Array.from(binary, (c) => c.charCodeAt(0)).buffer;
}
function bufferToBase64url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = "";
    bytes.forEach((b) => (binary += String.fromCharCode(b)));
    return btoa(binary).replace(/\+/g, "-").replace(/\//g, "_").replace(/=/g, "");
}

export default function Login() {
    const [showPassword, setShowPassword] = useState(false);
    const [passkeyLoading, setPasskeyLoading] = useState(false);
    const props = usePage().props;

    const { data, setData, post, processing, errors } = useForm({
        email: "",
        password: "",
        remember: false,
        _token: props.csrf_token,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post("/admin/login");
    };

    const handlePasskeyLogin = async () => {
        setPasskeyLoading(true);
        try {
            const response = await fetch('/passkeys/authentication-options');
            if (!response.ok) throw new Error('Failed to get options');
            const optionsJson = await response.json();

            // Wrap into { publicKey } and convert Base64url strings to ArrayBuffers
            const credential = await navigator.credentials.get({
                publicKey: {
                    ...optionsJson,
                    challenge: base64urlToBuffer(optionsJson.challenge),
                    allowCredentials: (optionsJson.allowCredentials || []).map((c) => ({
                        ...c,
                        id: base64urlToBuffer(c.id),
                    })),
                },
            });

            // Serialize credential ArrayBuffers back to Base64url strings for the server
            const serialized = JSON.stringify({
                id: credential.id,
                rawId: bufferToBase64url(credential.rawId),
                type: credential.type,
                response: {
                    clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
                    authenticatorData: bufferToBase64url(credential.response.authenticatorData),
                    signature: bufferToBase64url(credential.response.signature),
                    userHandle: credential.response.userHandle
                        ? bufferToBase64url(credential.response.userHandle)
                        : null,
                },
            });

            router.post('/passkeys/authenticate', {
                start_authentication_response: serialized,
                remember: data.remember
            });
        } catch (err) {
            console.error(err);
        } finally {
            setPasskeyLoading(false);
        }
    };

    return (
        <>
            <Head title="تسجيل الدخول" />
            <div className="min-h-screen bg-[#F2F2F7] dark:bg-[#000000] transition-colors duration-500 relative overflow-hidden flex items-center justify-center px-4">
                {/* Animated blur background elements */}
                <div className="pointer-events-none fixed inset-0 z-0 overflow-hidden">
                    <div className="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-blue-400/20 dark:bg-blue-500/10 blur-3xl animate-float1" />
                    <div className="absolute top-1/3 -right-24 w-80 h-80 rounded-full bg-purple-400/20 dark:bg-purple-500/10 blur-3xl animate-float2" />
                    <div className="absolute -bottom-20 left-1/3 w-72 h-72 rounded-full bg-pink-400/15 dark:bg-pink-500/10 blur-3xl animate-float3" />
                    <div className="absolute top-1/2 left-1/4 w-64 h-64 rounded-full bg-cyan-400/15 dark:bg-cyan-500/10 blur-3xl animate-float4" />
                </div>

                <div className="relative z-10 w-full max-w-md">
                    {/* Logo */}
                    <div className="flex flex-col items-center mb-8">
                        <div className="w-16 h-16 bg-black dark:bg-white rounded-3xl flex items-center justify-center shadow-lg mb-4">
                            <UtensilsCrossed className="text-white dark:text-black w-9 h-9" />
                        </div>
                        <h1 className="text-3xl font-black tracking-tight text-black dark:text-white">
                            Talabye
                        </h1>
                        <p className="text-zinc-500 dark:text-zinc-400 font-medium mt-2">
                            تسجيل الدخول إلى لوحة التحكم
                        </p>
                    </div>

                    {/* Login Card */}
                    <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] shadow-none">
                        <CardContent className="p-8">
                            <form onSubmit={handleSubmit} className="space-y-6">
                                {/* Email */}
                                <div>
                                    <label className="block text-sm font-bold text-zinc-700 dark:text-zinc-300 mb-2 uppercase tracking-widest">
                                        البريد الإلكتروني
                                    </label>
                                    <div className="relative">
                                        <Mail
                                            size={18}
                                            className="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400"
                                        />
                                        <input
                                            type="email"
                                            value={data.email}
                                            onChange={(e) =>
                                                setData("email", e.target.value)
                                            }
                                            placeholder="name@example.com"
                                            className="w-full h-14 pl-12 pr-4 rounded-2xl bg-zinc-100/80 dark:bg-zinc-800/50 text-sm border-none outline-none text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 font-medium focus:ring-2 focus:ring-blue-500/50 transition-all"
                                            dir="ltr"
                                        />
                                    </div>
                                    {errors.email && (
                                        <p className="text-rose-500 text-sm font-medium mt-2">
                                            {errors.email}
                                        </p>
                                    )}
                                </div>

                                {/* Password */}
                                <div>
                                    <label className="block text-sm font-bold text-zinc-700 dark:text-zinc-300 mb-2 uppercase tracking-widest">
                                        كلمة المرور
                                    </label>
                                    <div className="relative">
                                        <Lock
                                            size={18}
                                            className="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400"
                                        />
                                        <input
                                            type={
                                                showPassword
                                                    ? "text"
                                                    : "password"
                                            }
                                            value={data.password}
                                            onChange={(e) =>
                                                setData(
                                                    "password",
                                                    e.target.value,
                                                )
                                            }
                                            placeholder="••••••••"
                                            className="w-full h-14 pl-12 pr-12 rounded-2xl bg-zinc-100/80 dark:bg-zinc-800/50 text-sm border-none outline-none text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 font-medium focus:ring-2 focus:ring-blue-500/50 transition-all"
                                            dir="ltr"
                                        />
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setShowPassword(!showPassword)
                                            }
                                            className="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors"
                                        >
                                            {showPassword ? (
                                                <EyeOff size={18} />
                                            ) : (
                                                <Eye size={18} />
                                            )}
                                        </button>
                                    </div>
                                    {errors.password && (
                                        <p className="text-rose-500 text-sm font-medium mt-2">
                                            {errors.password}
                                        </p>
                                    )}
                                </div>

                                {/* Remember me */}
                                <div className="flex items-center justify-between">
                                    <Checkbox
                                        isSelected={data.remember}
                                        onValueChange={(val) =>
                                            setData("remember", val)
                                        }
                                        size="sm"
                                    >
                                        <span className="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                            تذكرني
                                        </span>
                                    </Checkbox>
                                </div>

                                {/* Submit */}
                                <div className="space-y-3">
                                    <Button
                                        type="submit"
                                        isLoading={processing}
                                        className="w-full bg-black dark:bg-white text-white dark:text-black font-black h-14 rounded-2xl text-base"
                                    >
                                        تسجيل الدخول
                                    </Button>

                                    <div className="relative flex items-center py-2">
                                        <div className="flex-grow border-t border-zinc-200 dark:border-zinc-800"></div>
                                        <span className="flex-shrink mx-4 text-zinc-400 text-xs font-bold uppercase tracking-widest">أو</span>
                                        <div className="flex-grow border-t border-zinc-200 dark:border-zinc-800"></div>
                                    </div>

                                    <Button
                                        type="button"
                                        onPress={handlePasskeyLogin}
                                        isLoading={passkeyLoading}
                                        variant="bordered"
                                        className="w-full h-14 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 font-bold text-zinc-700 dark:text-zinc-300 gap-3"
                                    >
                                        <Fingerprint size={20} className="text-blue-500" />
                                        الدخول بواسطة مفتاح المرور
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
