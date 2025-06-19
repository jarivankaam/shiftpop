<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shiftpop | Rooster</title>
    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Tailwind + Livewire --}}
    @livewireStyles
</head>
<body class="bg-white text-black font-sans">

{{-- Header --}}
<header class="bg-black text-white flex items-center justify-between px-8 py-4 shadow-md">
    <div class="text-3xl font-extrabold">
        <span class="text-orange-400">Shift</span>pop
    </div>
    <div class="space-x-4">
        <button class="bg-white text-black font-bold px-4 py-2 rounded">Ruilen of dienst overnemen</button>
        <button class="bg-white text-black font-bold px-4 py-2 rounded">Vrij vragen</button>
    </div>
</header>

{{-- Main Section --}}
<main class="max-w-7xl mx-auto px-4 py-8">
    <livewire:shift-agenda />
</main>

@livewireScripts
</body>
</html>
