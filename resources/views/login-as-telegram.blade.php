<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول عبر تليجرام</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <style>
        body {
            font-family: "IBM Plex Sans Arabic", sans-serif;
        }
    </style>
    @vite(['resources/js/main.tsx'])

</head>

<body class="antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-3xl p-8 text-center">
            <div class="mb-6">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <img src="{{ asset('/logo.png') }}" alt="Logo" class="p-3">

                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">سجل الدخول عبر تليجرام</h1>
                <p class="text-gray-600">اضغط على الزر أدناه لتسجيل الدخول بأمان باستخدام حساب تليجرام الخاص بك</p>
            </div>

            <div class="mb-6 flex justify-center">
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
