<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- [UBAH KHUSUS DESKTOP & MOBILE] Rebranding Xaviera Title -->
    <title inertia>xaviera</title>

    <!-- Favicon Premium -->
    <link rel="icon" type="image/png" href="/images/logo-brand.png">

    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- [UBAH KHUSUS DESKTOP & MOBILE] Injeksi Font Sultan (Cinzel & Plus Jakarta Sans) -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"></noscript>

    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
</body>

</html>
