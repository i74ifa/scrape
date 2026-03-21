<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login with Telegram</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5.28 7.72l-5.74 5.74c-.39.39-1.02.39-1.41 0l-2.87-2.87c-.39-.39-.39-1.02 0-1.41s1.02-.39 1.41 0l2.16 2.16 5.02-5.02c.39-.39 1.02-.39 1.41 0s.39 1.02 0 1.41z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">سجل الدخول عبر تليجرام</h1>
                <p class="text-gray-600">اضغط على الزر أدناه لتسجيل الدخول بأمان باستخدام حساب تليجرام الخاص بك</p>
            </div>

            <div class="mb-6">
                {!! \Laravel\Socialite\Socialite::driver('telegram')->stateless()->getButton() !!}
            </div>

            <div class="text-sm text-gray-500">
                <p>هل تحتاج إلى مساعدة؟ تواصل مع الدعم على <a href="mailto:support@talabye.com"
                        class="text-blue-600 hover:underline">support@talabye.com</a></p>
            </div>
        </div>
    </div>
</body>

</html>
