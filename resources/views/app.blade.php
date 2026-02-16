<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link
            href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />

        <!-- Styles / Scripts -->
    @vite(['resources/js/main.tsx'])
    <style>
      body {
        font-family: "IBM Plex Sans Arabic", sans-serif;
        background-color: #1a1a1a;
        color: #ffffff;
        overflow-x: hidden;
      }
      ::-webkit-scrollbar {
        width: 8px;
      }
      ::-webkit-scrollbar-track {
        background: #1a1a1a;
      }
      ::-webkit-scrollbar-thumb {
        background: #333;
        border-radius: 10px;
      }
      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(10px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      .animate-fade-in {
        animation: fadeIn 0.8s ease-out forwards;
      }

      @keyframes float-slow {
        0% {
          transform: translate(0, 0) scale(1);
        }
        33% {
          transform: translate(30px, -50px) scale(1.1);
        }
        66% {
          transform: translate(-20px, 20px) scale(0.9);
        }
        100% {
          transform: translate(0, 0) scale(1);
        }
      }

      @keyframes float-reverse {
        0% {
          transform: translate(0, 0) scale(1);
        }
        50% {
          transform: translate(-40px, 40px) scale(1.15);
        }
        100% {
          transform: translate(0, 0) scale(1);
        }
      }

      .bg-blur-shape {
        position: fixed;
        border-radius: 50%;
        filter: blur(120px);
        z-index: -1;
        pointer-events: none;
        opacity: 0.12;
        will-change: transform;
      }

      .shape-1 {
        width: 500px;
        height: 500px;
        background: #2563eb;
        top: -100px;
        left: -100px;
        animation: float-slow 25s infinite ease-in-out;
      }

      .shape-2 {
        width: 400px;
        height: 400px;
        background: #7c3aed;
        bottom: 10%;
        right: -50px;
        animation: float-reverse 30s infinite ease-in-out;
      }

      .shape-3 {
        width: 350px;
        height: 350px;
        background: #2563eb;
        top: 40%;
        left: 20%;
        animation: float-slow 35s infinite ease-in-out reverse;
        opacity: 0.08;
      }
    </style>
    </head>
    <body>
      <div id="root"></div>
    </body>

</html>
