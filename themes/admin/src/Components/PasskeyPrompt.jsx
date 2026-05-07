import React, { useEffect, useState } from "react";
import { usePage } from "@inertiajs/react";
import { Modal, Button } from "@heroui/react";
import { Fingerprint } from "lucide-react";

// Base64url helpers required by the native WebAuthn API
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

export default function PasskeyPrompt() {
    const { props } = usePage();
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // Only prompt if the browser supports WebAuthn (requires HTTPS / secure context)
    const isSupported =
        typeof window !== "undefined" &&
        window.isSecureContext &&
        !!window.PublicKeyCredential;

    useEffect(() => {
        if (props.askPasskey && isSupported) {
            setIsOpen(true);
        }
    }, [props.askPasskey]);

    const handleRegister = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await fetch(
                route("passkeys.registration_options"),
            );
            if (!response.ok)
                throw new Error("Failed to get registration options");
            const optionsJson = await response.json();

            // Convert challenge and user.id from Base64url strings to ArrayBuffers
            const publicKey = {
                ...optionsJson,
                challenge: base64urlToBuffer(optionsJson.challenge),
                user: {
                    ...optionsJson.user,
                    id: base64urlToBuffer(optionsJson.user.id),
                },
                excludeCredentials: (optionsJson.excludeCredentials || []).map((c) => ({
                    ...c,
                    id: base64urlToBuffer(c.id),
                })),
            };

            const credential = await navigator.credentials.create({ publicKey });

            const registerResponse = await fetch(route("passkeys.register"), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": props.csrf_token,
                },
                body: JSON.stringify({
                    // Serialize the credential back to JSON-safe Base64url format
                    passkey: JSON.stringify({
                        id: credential.id,
                        rawId: bufferToBase64url(credential.rawId),
                        type: credential.type,
                        response: {
                            clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
                            attestationObject: bufferToBase64url(credential.response.attestationObject),
                        },
                    }),
                    name: `Passkey (${new Date().toLocaleDateString()})`,
                }),
            });

            if (!registerResponse.ok) {
                const errorData = await registerResponse.json();
                throw new Error(
                    errorData.message || "Failed to register passkey",
                );
            }

            setIsOpen(false);
        } catch (err) {
            console.error(err);
            setError(err.message || "Something went wrong");
        } finally {
            setLoading(false);
        }
    };

    return (
        <Modal
            isOpen={isOpen}
            onOpenChange={(open) => !open && setIsOpen(false)}
            placement="center"
        >
            <Modal.Container
                className="fixed inset-0 z-500 flex items-center justify-center p-4 w-full"
                placement="center"
            >
                <Modal.Backdrop className="fixed inset-0 bg-black/5 z-499" />
                <Modal.Dialog className="bg-white dark:bg-zinc-900 rounded-[2.5rem] shadow-2xl w-full max-w-md p-0 outline-none">
                    <Modal.Header className="flex flex-col items-center pt-8 px-8 pb-0">
                        <div className="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-3xl flex items-center justify-center mb-4">
                            <Fingerprint className="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        </div>
                        <Modal.Heading className="text-2xl font-black text-zinc-900 dark:text-white">
                            تأمين حسابك
                        </Modal.Heading>
                    </Modal.Header>

                    <Modal.Body className="text-center px-8 py-6">
                        <p className="text-zinc-500 dark:text-zinc-400 font-medium">
                            هل ترغب في تفعيل مفتاح المرور (Passkey) لتسجيل
                            الدخول بشكل أسرع وأكثر أماناً في المرة القادمة؟
                        </p>
                        {error && (
                            <div className="mt-4 p-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-2xl text-sm font-bold border border-red-100 dark:border-red-900/30">
                                {error}
                            </div>
                        )}
                    </Modal.Body>

                    <Modal.Footer className="flex flex-col gap-2 p-8 pt-0">
                        <Button
                            onPress={handleRegister}
                            isLoading={loading}
                            className="w-full h-14 bg-black dark:bg-white text-white dark:text-black font-black rounded-2xl text-base shadow-xl"
                        >
                            تفعيل مفتاح المرور
                        </Button>
                        <Modal.CloseTrigger asChild>
                            <Button
                                variant="light"
                                onPress={() => setIsOpen(false)}
                                className="w-full h-12 text-zinc-500 font-bold rounded-xl"
                            >
                                ليس الآن
                            </Button>
                        </Modal.CloseTrigger>
                    </Modal.Footer>
                </Modal.Dialog>
            </Modal.Container>
        </Modal>
    );
}
