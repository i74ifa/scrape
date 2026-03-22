<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تسجيل الدخول بنجاح</title>
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

<body class="antialiased bg-gray-100 dark:bg-[#111]" dir="rtl">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="max-w-md w-full bg-white dark:bg-[#1e1e1e] rounded-3xl p-8 text-center">
            <div class="mb-6">
                <div
                    class="w-20 h-20 bg-gray-50 dark:bg-[#2d2d2d] rounded-full flex items-center justify-center mx-auto mb-4">
                    <img src="{{ asset('/logo.png') }}" alt="Logo" class="p-3">

                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-200 mb-2">تم تسجيل الدخول بنجاح</h1>
                <p class="text-gray-600 dark:text-gray-400 text-sm">سيتم تحويلك إلى التطبيق خلال ثوانٍ 👋</p>
            </div>
        </div>
    </div>
</body>

</html>
