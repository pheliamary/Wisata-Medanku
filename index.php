<?php
/**
 * index.php v3.0 — Landing Page SIG Wisata Medan (Tailwind Revamp)
 */
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/models/Wisata.php';

$wm = new Wisata();

$filter = [
    'search'     => sanitize($_GET['search']     ?? ''),
    'kategori'   => sanitize($_GET['kategori']   ?? ''),
    'kecamatan'  => sanitize($_GET['kecamatan']  ?? ''),
    'kelurahan'  => sanitize($_GET['kelurahan']  ?? ''),
    'rating_min' => sanitize($_GET['rating_min'] ?? ''),
    'tiket'      => sanitize($_GET['tiket']      ?? ''),
    'sort'       => sanitize($_GET['sort']       ?? ''),
];

$wisatas       = $wm->getAll($filter);
$stats         = $wm->getStats();
$kategoriList  = $wm->getDistinctKategori();
$kecamatanList = $wm->getDistinctKecamatan();
$kelurahanList = $wm->getDistinctKelurahan($filter['kecamatan']);
$count         = count($wisatas);

function dashImg(string $name): string {
    $base = __DIR__ . '/assets/images/dashboard-page/';
    foreach (['jpg','jpeg','png','webp'] as $ext) {
        if (file_exists($base . $name . '.' . $ext)) return 'assets/images/dashboard-page/' . $name . '.' . $ext;
    }
    return '';
}
function logoPath(): string {
    $base = __DIR__ . '/assets/images/logo-sig/';
    foreach (glob($base . '*') ?: [] as $f) {
        if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['png','jpg','svg','webp']))
            return 'assets/images/logo-sig/' . basename($f);
    }
    return '';
}
$logo = logoPath();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SIG Wisata Kota Medan — Jelajahi Destinasi Terbaik</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
</script>
<script>
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        fontFamily: {
          sans: ['Inter', 'sans-serif'],
          display: ['Outfit', 'sans-serif'],
        },
        colors: {
          brand: {
            50: '#f0fdf4',
            100: '#dcfce7',
            200: '#bbf7d0',
            300: '#86efac',
            400: '#4ade80',
            500: '#22c55e',
            600: '#16a34a',
            700: '#15803d',
            800: '#166534',
            900: '#14532d',
            950: '#052e16',
          },
          dark: '#0c1a10',
          accent: '#c8901a',
        }
      }
    }
  }
</script>
<link rel="stylesheet" href="assets/css/style.css">
<style>
  [x-cloak] { display: none !important; }
  .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
  .glass-dark { background: rgba(12, 26, 16, 0.8); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
  
  /* Custom scrollbar */
  ::-webkit-scrollbar { width: 8px; }
  ::-webkit-scrollbar-track { background: #f1f1f1; }
  ::-webkit-scrollbar-thumb { background: #16a34a; border-radius: 10px; }
  ::-webkit-scrollbar-thumb:hover { background: #15803d; }

  @keyframes pan-image {
    0% { transform: scale(1.05) translate(0, 0); }
    100% { transform: scale(1.15) translate(-2%, -2%); }
  }
  .animate-pan { animation: pan-image 20s linear infinite alternate; }
</style>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100 font-sans antialiased overflow-x-hidden selection:bg-brand-500 selection:text-white" x-data="{ scrolled: false, mobileMenuOpen: false }" @scroll.window="scrolled = (window.pageYOffset > 50)">

<!-- CURSOR (tetap dipertahankan dari style lama) -->
<div class="cursor-dot hidden md:block" id="cursorDot"></div>
<div class="cursor-ring hidden md:block" id="cursorRing"></div>

<!-- LOADER -->
<div class="page-loader" id="pageLoader">
    <div class="loader-text font-display font-bold text-3xl text-white"><span class="text-accent">Wisata</span> MedanKu</div>
    <div class="loader-progress"></div>
</div>

<!-- NAVBAR -->
<nav :class="{'glass shadow-sm py-3': scrolled, 'bg-transparent py-5': !scrolled}" class="fixed top-0 w-full z-50 transition-all duration-300">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 flex justify-between items-center">
    <a href="index.php" class="flex items-center gap-3 group">
      <?php if ($logo): ?>
      <img src="<?= $logo ?>" alt="Logo SIG" class="h-10 w-10 object-contain transition-transform group-hover:scale-110 group-hover:-rotate-3 drop-shadow-md brightness-0 invert">
      <?php else: ?>
      <div class="w-10 h-10 rounded-xl bg-brand-500 text-white flex items-center justify-center text-xl shadow-lg transition-transform group-hover:scale-110 group-hover:-rotate-3">🗺️</div>
      <?php endif; ?>
      <div>
        <div class="font-display font-bold text-xl leading-tight" :class="{'text-gray-900': scrolled, 'text-white': !scrolled}">SIG Wisata</div>
        <div class="text-[0.65rem] tracking-widest uppercase font-semibold text-amber-400">Kota Medan</div>
      </div>
    </a>

    <!-- Desktop Menu -->
    <ul class="hidden md:flex items-center gap-1 font-medium text-sm">
      <li><a href="#beranda" class="px-4 py-2 rounded-full transition-colors relative group" :class="{'text-gray-600 hover:text-brand-600': scrolled, 'text-gray-200 hover:text-white': !scrolled}">Beranda<span class="absolute bottom-1 left-1/2 w-0 h-[2px] bg-brand-500 transition-all group-hover:w-1/2 group-hover:-translate-x-1/2"></span></a></li>
      <li><a href="#galeri" class="px-4 py-2 rounded-full transition-colors relative group" :class="{'text-gray-600 hover:text-brand-600': scrolled, 'text-gray-200 hover:text-white': !scrolled}">Galeri<span class="absolute bottom-1 left-1/2 w-0 h-[2px] bg-brand-500 transition-all group-hover:w-1/2 group-hover:-translate-x-1/2"></span></a></li>
      <li><a href="#destinasi" class="px-4 py-2 rounded-full transition-colors relative group" :class="{'text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400': scrolled, 'text-gray-200 hover:text-white': !scrolled}">Destinasi<span class="absolute bottom-1 left-1/2 w-0 h-[2px] bg-brand-500 transition-all group-hover:w-1/2 group-hover:-translate-x-1/2"></span></a></li>
      <li><a href="#peta" class="px-4 py-2 rounded-full transition-colors relative group" :class="{'text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400': scrolled, 'text-gray-200 hover:text-white': !scrolled}">Peta<span class="absolute bottom-1 left-1/2 w-0 h-[2px] bg-brand-500 transition-all group-hover:w-1/2 group-hover:-translate-x-1/2"></span></a></li>
      <li class="ml-2">
        <button @click="document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light'" class="p-2 rounded-full transition-colors flex items-center justify-center" :class="{'bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-white': scrolled, 'bg-white/10 hover:bg-white/20 text-white': !scrolled}">
          <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
        </button>
      </li>
      <li class="ml-2">
        <a href="views/admin/login.php" class="px-5 py-2 rounded-full bg-brand-600 text-white font-semibold hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-500/30 transition-all flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg> Admin
        </a>
      </li>
    </ul>

    <!-- Mobile Menu Button -->
    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg transition-colors" :class="{'text-gray-800': scrolled, 'text-white': !scrolled}">
      <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
      <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
  </div>

  <!-- Mobile Menu -->
  <div x-show="mobileMenuOpen" x-collapse x-cloak class="md:hidden glass border-t border-gray-200 absolute w-full left-0 mt-3 shadow-xl">
    <ul class="px-6 py-4 space-y-2 text-gray-800 font-medium">
      <li><a href="#beranda" @click="mobileMenuOpen = false" class="block py-2 hover:text-brand-600">Beranda</a></li>
      <li><a href="#galeri" @click="mobileMenuOpen = false" class="block py-2 hover:text-brand-600">Galeri</a></li>
      <li><a href="#destinasi" @click="mobileMenuOpen = false" class="block py-2 hover:text-brand-600">Destinasi</a></li>
      <li><a href="#peta" @click="mobileMenuOpen = false" class="block py-2 hover:text-brand-600">Peta</a></li>
      <li class="pt-2">
        <a href="views/admin/login.php" class="flex items-center gap-2 py-2 text-brand-600 font-bold">
           ⚙️ Panel Admin
        </a>
      </li>
    </ul>
  </div>
</nav>

<!-- HERO -->
<section id="beranda" class="relative min-h-[100svh] flex items-center pt-20 pb-16 overflow-hidden bg-brand-950">
  <!-- Background Video with Overlay -->
  <div class="absolute inset-0 z-0">
    <video autoplay loop muted playsinline class="w-full h-full object-cover opacity-80">
      <source src="assets/video/medan_video.MP4" type="video/mp4">
    </video>
    <div class="absolute inset-0 bg-gradient-to-t from-brand-950/90 via-brand-900/40 to-transparent"></div>
  </div>
  
  <!-- Decorative Blobs -->
  <div class="absolute top-1/4 -left-20 w-72 h-72 bg-brand-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
  <div class="absolute top-1/3 -right-20 w-96 h-96 bg-emerald-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>

  <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 w-full mt-10">
    <div class="max-w-3xl">
      <div data-aos="fade-up" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand-400/30 bg-brand-500/10 text-brand-300 text-xs font-bold uppercase tracking-widest mb-6">
        <span class="w-2 h-2 rounded-full bg-brand-400 animate-pulse"></span>
        Sistem Informasi Geografis
      </div>
      
      <h1 data-aos="fade-up" data-aos-delay="100" class="font-display font-extrabold text-5xl md:text-6xl lg:text-7xl text-white leading-[1.1] mb-6">
        Jelajahi Pesona <br>
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 to-emerald-500">Kota Medan</span>
      </h1>
      
      <p data-aos="fade-up" data-aos-delay="200" class="text-lg md:text-xl text-gray-300 mb-10 max-w-2xl leading-relaxed">
        Temukan <span class="font-bold text-white"><?= $stats['total'] ?></span> destinasi wisata terbaik dari kawasan bersejarah, religi, kuliner, hingga pesona alam yang menakjubkan.
      </p>

      <!-- Search Form -->
      <div data-aos="fade-up" data-aos-delay="300" class="glass-dark p-2 rounded-2xl md:rounded-full border border-white/10 shadow-2xl backdrop-blur-xl max-w-3xl">
        <form onsubmit="event.preventDefault(); const p = new URLSearchParams(new FormData(this)); window.location.href = 'index.php?' + p.toString() + '#destinasi';" class="flex flex-col md:flex-row gap-2">
          <div class="flex-1 flex items-center px-4 py-3 md:py-0">
            <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" name="search" placeholder="Cari destinasi, kecamatan..." value="<?= htmlspecialchars($filter['search']) ?>" class="w-full bg-transparent border-none text-white focus:outline-none focus:ring-0 placeholder-gray-400 text-sm md:text-base">
          </div>
          
          <div class="hidden md:block w-px h-8 bg-white/20 self-center"></div>
          
          <div class="px-2 border-t border-white/10 md:border-t-0">
            <select name="kategori" class="w-full md:w-auto bg-transparent border-none text-gray-300 focus:outline-none focus:ring-0 text-sm py-3 cursor-pointer appearance-none px-4">
              <option value="" class="text-gray-900">Semua Kategori</option>
              <?php foreach ($kategoriList as $kat): ?>
              <option value="<?= $kat ?>" class="text-gray-900" <?= $filter['kategori']===$kat?'selected':'' ?>><?= ucfirst($kat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-semibold py-3 px-8 rounded-xl md:rounded-full transition-all hover:shadow-lg hover:shadow-brand-500/40 w-full md:w-auto flex items-center justify-center gap-2">
            Cari 
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
          </button>
        </form>
      </div>

      <!-- Quick Links -->
      <div data-aos="fade-up" data-aos-delay="400" class="mt-8 flex flex-wrap gap-3 items-center">
        <span class="text-sm text-gray-400 font-medium">Populer:</span>
        <?php foreach (['sejarah'=>'🏛️','religi'=>'🕌','kuliner'=>'🍽️','alam'=>'🌿'] as $q => $emj): ?>
        <a href="?kategori=<?= $q ?>#destinasi" class="flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-white/10 bg-white/5 text-gray-300 text-sm hover:bg-brand-500 hover:text-white hover:border-brand-500 transition-all">
          <span><?= $emj ?></span> <?= ucfirst($q) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  
  <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-bounce">
    <span class="text-xs uppercase tracking-widest text-white/50 font-bold">Scroll</span>
    <div class="w-[1px] h-12 bg-gradient-to-b from-white/50 to-transparent"></div>
  </div>
</section>

<!-- STATS RIBBON -->
<div class="bg-white dark:bg-gray-800 border-y border-gray-200 dark:border-gray-700 transition-colors">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 divide-x divide-gray-100 dark:divide-gray-700 py-8 gap-y-6">
      <div class="text-center px-4" data-aos="zoom-in" data-aos-delay="0">
        <div class="font-display font-bold text-4xl text-brand-600 dark:text-brand-400 mb-1 stat-n"><?= $stats['total'] ?></div>
        <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Total Wisata</div>
      </div>
      <?php 
      $delay = 100;
      foreach ($stats['per_kategori'] as $idx => $c): 
        if (!$c['jumlah']) continue; 
      ?>
      <div class="text-center px-4" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
        <div class="font-display font-bold text-4xl text-brand-600 dark:text-brand-400 mb-1 stat-n"><?= $c['jumlah'] ?></div>
        <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold"><?= ucfirst($c['kategori']) ?></div>
      </div>
      <?php $delay+=100; endforeach; ?>
      <div class="text-center px-4 border-l lg:border-l-0 dark:border-gray-700" data-aos="zoom-in" data-aos-delay="<?= $delay ?>">
        <div class="font-display font-bold text-4xl text-brand-600 dark:text-brand-400 mb-1 stat-n"><?= count($kecamatanList) ?></div>
        <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Kecamatan</div>
      </div>
    </div>
  </div>
</div>

<!-- GALERI KOTA -->
<section id="galeri" class="py-24 bg-gray-50 dark:bg-gray-900 transition-colors relative">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto mb-16">
      <h2 data-aos="fade-up" class="font-display font-bold text-4xl md:text-5xl text-gray-900 dark:text-white mb-4">Keindahan <span class="text-brand-600 dark:text-brand-400">Medan</span></h2>
      <p data-aos="fade-up" data-aos-delay="100" class="text-gray-600 dark:text-gray-400 text-lg">Sekilas pandang pesona destinasi ikonik yang menanti untuk Anda jelajahi.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 md:grid-rows-2 gap-4 h-[600px]">
      <?php
      $slots = [
        ['file'=>'gallery-1-fix','label'=>'Kawasan Kesawan','span'=>'md:col-span-2 md:row-span-2'],
        ['file'=>'gallery-2','label'=>'Istana Maimun','span'=>'md:col-span-1 md:row-span-1'],
        ['file'=>'gallery-3','label'=>'Kuil Shri Mariamman','span'=>'md:col-span-1 md:row-span-1'],
        ['file'=>'gallery-4','label'=>'Museum Sumatera Utara','span'=>'md:col-span-2 md:row-span-1'],
      ];
      foreach ($slots as $i => $s):
        $img = dashImg($s['file']);
      ?>
      <div data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>" class="<?= $s['span'] ?> relative rounded-2xl overflow-hidden group cursor-pointer shadow-sm bg-gray-200 h-[280px] md:h-auto">
        <?php if ($img): ?>
        <img src="<?= $img ?>" alt="<?= $s['label'] ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
        <?php else: ?>
        <div class="w-full h-full flex items-center justify-center text-4xl bg-gradient-to-br from-brand-100 to-brand-200 text-brand-700 font-bold">🏛️</div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-300"></div>
        <div class="absolute bottom-0 left-0 p-6 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
          <h3 class="text-white font-display font-bold text-xl"><?= $s['label'] ?></h3>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- DESTINASI WISATA -->
<section id="destinasi" class="py-24 bg-white relative">
  <div class="max-w-7xl mx-auto px-6 lg:px-8">
    
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
      <div class="max-w-2xl">
        <div data-aos="fade-right" class="text-brand-600 font-bold tracking-widest uppercase text-sm mb-2 flex items-center gap-2"><span class="w-8 h-0.5 bg-brand-600"></span>Eksplorasi</div>
        <h2 data-aos="fade-up" class="font-display font-bold text-4xl md:text-5xl text-gray-900">
          <?php
            if ($filter['kategori']) echo 'Wisata <span class="text-brand-600">'.ucfirst($filter['kategori']).'</span>';
            elseif ($filter['kecamatan']) echo 'Wisata di <span class="text-brand-600">'.$filter['kecamatan'].'</span>';
            elseif ($filter['search']) echo 'Hasil: "<span class="text-brand-600">'.htmlspecialchars($filter['search']).'</span>"';
            else echo 'Semua <span class="text-brand-600">Destinasi</span>';
          ?>
        </h2>
      </div>
    </div>

    <!-- FILTER BAR -->
    <div data-aos="fade-up" id="filter-bar" class="bg-gray-50 border border-gray-100 p-4 rounded-2xl mb-10 flex flex-wrap items-center gap-4 shadow-sm" x-data>
      
      <!-- Category Pills -->
      <div class="flex flex-wrap gap-2">
        <button class="px-4 py-2 rounded-full text-sm font-semibold transition-all border <?= !$filter['kategori'] ? 'bg-brand-600 text-white border-brand-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-500 hover:text-brand-600' ?>" onclick="applyFilter('kategori','')">Semua</button>
        <?php foreach ($kategoriList as $kat): ?>
        <button class="px-4 py-2 rounded-full text-sm font-semibold transition-all border <?= $filter['kategori']===$kat ? 'bg-brand-600 text-white border-brand-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-500 hover:text-brand-600' ?>" onclick="applyFilter('kategori','<?= $kat ?>')"><?= ucfirst($kat) ?></button>
        <?php endforeach; ?>
      </div>

      <div class="hidden md:block w-px h-8 bg-gray-300"></div>

      <!-- Select Filters -->
      <div class="flex flex-wrap gap-3">
        <select class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-brand-500 focus:border-brand-500 block px-3 py-2 font-medium" onchange="applyFilter('kecamatan',this.value)">
          <option value="">Semua Kecamatan</option>
          <?php foreach ($kecamatanList as $kec): ?>
          <option value="<?= $kec ?>" <?= $filter['kecamatan']===$kec?'selected':'' ?>><?= $kec ?></option>
          <?php endforeach; ?>
        </select>
        
        <select class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-brand-500 focus:border-brand-500 block px-3 py-2 font-medium" onchange="applyFilter('kelurahan',this.value)">
          <option value="">Semua Kelurahan</option>
          <?php foreach ($kelurahanList as $kel): ?>
          <option value="<?= $kel['nama'] ?>" <?= $filter['kelurahan']===$kel['nama']?'selected':'' ?>><?= $kel['nama'] ?></option>
          <?php endforeach; ?>
        </select>
        
        <select class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-brand-500 focus:border-brand-500 block px-3 py-2 font-medium" onchange="applyFilter('rating_min',this.value)">
          <option value="">Semua Rating</option>
          <option value="4.5" <?= $filter['rating_min']==='4.5'?'selected':'' ?>>⭐ 4.5+</option>
          <option value="4.0" <?= $filter['rating_min']==='4.0'?'selected':'' ?>>⭐ 4.0+</option>
          <option value="3.5" <?= $filter['rating_min']==='3.5'?'selected':'' ?>>⭐ 3.5+</option>
        </select>
        
        <select class="bg-white border border-gray-200 text-gray-700 text-sm rounded-lg focus:ring-brand-500 focus:border-brand-500 block px-3 py-2 font-medium" onchange="applyFilter('sort',this.value)">
          <option value="">Urutkan: Nama</option>
          <option value="rating" <?= $filter['sort']==='rating'?'selected':'' ?>>Rating Tertinggi</option>
          <option value="tiket"  <?= $filter['sort']==='tiket'?'selected':'' ?>>Harga Terendah</option>
        </select>
      </div>

      <div class="ml-auto flex items-center gap-4">
        <?php if (array_filter($filter)): ?>
        <a href="index.php" class="text-sm text-red-500 font-semibold hover:underline">✕ Reset Filter</a>
        <?php endif; ?>
        <div class="text-sm font-semibold bg-gray-200 text-gray-700 py-1 px-3 rounded-full"><?= $count ?> Hasil</div>
      </div>
    </div>

    <!-- WISATA GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php if (empty($wisatas)): ?>
      <div class="col-span-full py-20 text-center">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="text-2xl font-display font-bold text-gray-800 mb-2">Tidak Ada Hasil</h3>
        <p class="text-gray-500 mb-6">Coba ubah filter atau kata kunci pencarian Anda.</p>
        <a href="index.php" class="inline-block bg-brand-500 text-white font-semibold px-6 py-3 rounded-full hover:bg-brand-600 transition-colors">Reset Pencarian</a>
      </div>
      <?php else: ?>
      <?php
      $catEmoji = ['sejarah'=>'🏛️','religi'=>'🕌','edukasi'=>'🎓','alam'=>'🌿','kuliner'=>'🍽️','olahraga'=>'⚽'];
      $catColor = [
          'sejarah' => 'bg-blue-500',
          'religi'  => 'bg-purple-500',
          'edukasi' => 'bg-red-500',
          'alam'    => 'bg-emerald-500',
          'kuliner' => 'bg-amber-500',
          'olahraga'=> 'bg-pink-500'
      ];
      
      foreach ($wisatas as $w):
        $hasImg = !empty($w['gambar']) && file_exists(UPLOAD_DIR . $w['gambar']);
        $emoji  = $catEmoji[$w['kategori']] ?? '📍';
        $bgColor = $catColor[$w['kategori']] ?? 'bg-gray-500';
      ?>
      <div data-aos="fade-up" class="group bg-white dark:bg-black rounded-3xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 cursor-pointer flex flex-col" onclick="if(typeof window.zoomToWisata === 'function') window.zoomToWisata(<?= $w['id'] ?>,<?= $w['latitude'] ?>,<?= $w['longitude'] ?>)">
        
        <div class="relative h-56 overflow-hidden bg-gray-100 dark:bg-gray-700">
          <?php if ($hasImg): ?>
          <img src="uploads/wisata/<?= htmlspecialchars($w['gambar']) ?>" alt="<?= htmlspecialchars($w['nama_wisata']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy">
          <?php else: ?>
          <div class="w-full h-full flex items-center justify-center text-6xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600"><?= $emoji ?></div>
          <?php endif; ?>
          
          <div class="absolute top-4 left-4 <?= $bgColor ?> text-white text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-full shadow-md backdrop-blur-sm bg-opacity-90">
            <?= ucfirst($w['kategori']??'Umum') ?>
          </div>
          
          <!-- Hover Overlay -->
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
            <span class="bg-white/90 text-gray-900 backdrop-blur text-sm font-bold px-4 py-2 rounded-full transform translate-y-4 group-hover:translate-y-0 transition-transform">Lihat di Peta</span>
          </div>
        </div>
        
        <div class="p-6 flex-1 flex flex-col">
          <div class="flex items-center gap-1.5 text-xs font-semibold text-brand-600 dark:text-brand-400 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <?= htmlspecialchars(trim(($w['kelurahan']??'').', '.($w['kecamatan']??''), ', ')) ?>
          </div>
          
          <h3 class="font-display font-bold text-xl text-gray-900 dark:text-white mb-2 line-clamp-1 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors"><?= htmlspecialchars($w['nama_wisata']) ?></h3>
          
          <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2 mb-4 flex-1 leading-relaxed">
            <?= htmlspecialchars($w['deskripsi']??'') ?>
          </p>
          
          <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700 mt-auto">
            <div class="font-bold text-sm <?= ($w['tiket_masuk']??0)>0 ? 'text-gray-800 dark:text-gray-200' : 'text-emerald-500 dark:text-emerald-400' ?>">
              <?= ($w['tiket_masuk']??0)>0 ? 'Rp '.number_format($w['tiket_masuk'],0,',','.') : '✓ Gratis' ?>
            </div>
            <div class="flex items-center gap-1 bg-amber-50 text-amber-600 px-2 py-1 rounded font-bold text-sm">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
              <?= number_format($w['rating']??0,1) ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- PETA INTERAKTIF -->
<section id="peta" class="py-24 bg-brand-950 relative overflow-hidden">
  <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'#16a34a\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
  <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
    <div class="text-center max-w-2xl mx-auto mb-12">
      <h2 data-aos="fade-up" data-aos-delay="100" class="font-display font-bold text-4xl md:text-5xl text-white mb-4">Peta <span class="text-brand-400">Wisata</span></h2>
      <p data-aos="fade-up" data-aos-delay="200" class="text-gray-400 text-lg">Klik marker untuk melihat info detail, atau klik kartu wisata di atas untuk zoom otomatis ke lokasinya.</p>
    </div>

    <div data-aos="zoom-in" data-aos-delay="300" class="bg-white p-2 rounded-3xl shadow-2xl">
      <div id="map" class="h-[600px] w-full rounded-2xl z-0"></div>
    </div>

    <!-- Map Legend -->
    <div data-aos="fade-up" data-aos-delay="400" class="mt-8 flex flex-wrap justify-center gap-4 md:gap-8 bg-brand-900/50 p-4 rounded-2xl backdrop-blur border border-brand-800">
      <?php foreach (['sejarah'=>'bg-blue-500','religi'=>'bg-purple-500','edukasi'=>'bg-red-500','alam'=>'bg-emerald-500','kuliner'=>'bg-amber-500','olahraga'=>'bg-pink-500'] as $k=>$c): ?>
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full <?= $c ?> shadow-[0_0_10px_currentColor]"></span>
        <span class="text-gray-300 text-sm font-medium uppercase tracking-wider"><?= ucfirst($k) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="bg-gray-900 text-white pt-16 pb-8 border-t border-gray-800">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col items-center justify-center gap-4">
    <div class="flex items-center gap-4 mb-2">
      <a href="https://www.instagram.com/pheliamary?igsh=MW9qMjBoMTV5N2U3ag==" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
      </a>
      <a href="https://www.linkedin.com/in/phelia-nathania" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd" /></svg>
      </a>
    </div>
    <div class="text-center">
      <p class="text-gray-400 text-sm">© <?= date('Y') ?> Sistem Informasi Geografis Wisata Kota Medan.</p>
      <p class="text-gray-500 text-xs mt-1">Phelia Nathania | phelianathania05@gmail.com</p>
    </div>
  </div>
</footer>

<!-- MODAL DESAIN BARU MENGGUNAKAN ALPINE.JS -->
<div x-data="{ open: false, data: {} }" 
     @open-modal.window="open = true; data = $event.detail;" 
     @keydown.escape.window="open = false;"
     class="relative z-[100]">
     
  <!-- Backdrop -->
  <div x-show="open" x-cloak 
       x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
       x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
       class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" 
       @click="open = false;"></div>

  <!-- Modal Panel -->
  <div class="fixed inset-0 z-10 overflow-y-auto" x-show="open" x-cloak>
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
      <div x-show="open" 
           x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-black text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100 dark:border-gray-800" @click.stop>
        
        <!-- Close Button -->
        <button @click="open = false;" class="absolute top-4 right-4 w-10 h-10 bg-black/40 hover:bg-black/60 backdrop-blur text-white rounded-full flex items-center justify-center transition-colors z-10">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <!-- Image Header -->
        <div class="relative h-64 bg-gray-200 dark:bg-gray-700 w-full">
          <template x-if="data.gambar">
            <img :src="'uploads/wisata/' + data.gambar" class="w-full h-full object-cover">
          </template>
          <template x-if="!data.gambar">
            <div class="w-full h-full flex items-center justify-center text-6xl bg-gradient-to-br from-brand-100 to-brand-200 dark:from-brand-900 dark:to-brand-800 text-brand-600 dark:text-brand-300" x-text="data.emoji || '📍'"></div>
          </template>
          <!-- Category Tag -->
          <div class="absolute bottom-4 left-4 bg-white/90 dark:bg-gray-900/90 backdrop-blur px-4 py-1.5 rounded-full text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-widest shadow-lg" x-text="data.kategori"></div>
        </div>

        <!-- Content -->
        <div class="p-8">
          <div class="flex items-center gap-2 text-sm text-brand-600 dark:text-brand-400 font-semibold mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
            <span x-text="data.lokasi"></span>
          </div>
          
          <h3 class="text-3xl font-display font-bold text-gray-900 dark:text-white mb-4 leading-tight" x-text="data.nama"></h3>
          <p class="text-gray-600 dark:text-gray-300 leading-relaxed mb-6" x-text="data.deskripsi"></p>

          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-600">
              <div class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Tiket Masuk</div>
              <div class="font-bold text-gray-900 dark:text-gray-100" x-html="data.tiket"></div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-600">
              <div class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Rating</div>
              <div class="font-bold text-amber-500 flex items-center gap-1" x-html="data.rating_html"></div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-600">
              <div class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Jam Operasional</div>
              <div class="font-bold text-gray-900 dark:text-gray-100 text-sm" x-text="data.jam"></div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-600">
              <div class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Koordinat</div>
              <div class="font-mono text-xs text-gray-600 dark:text-gray-400 mt-1" x-text="data.koordinat"></div>
            </div>
          </div>

          <template x-if="data.fasilitas && data.fasilitas.length > 0">
            <div class="mb-6">
              <div class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-3">Fasilitas Tersedia</div>
              <div class="flex flex-wrap gap-2">
                <template x-for="fas in data.fasilitas">
                  <span class="bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 border border-brand-100 dark:border-brand-800 px-3 py-1 rounded-lg text-sm font-medium" x-text="fas"></span>
                </template>
              </div>
            </div>
          </template>

          <div class="flex flex-col sm:flex-row items-center gap-3 mt-4">
            <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(data.nama) + '+' + data.lat + ',' + data.lng" target="_blank" class="w-full sm:w-1/2 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-brand-600 dark:text-brand-400 font-bold py-4 rounded-xl border border-brand-200 dark:border-gray-600 transition-colors flex items-center justify-center gap-2 shadow-sm">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
              Buka di Google Maps
            </a>
            <button @click="open = false;" class="w-full sm:w-1/2 bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-brand-500/20">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- FLOATING MUSIC PLAYER -->
<div x-data="{ 
  playing: sessionStorage.getItem('bgMusic') === 'true',
  init() { 
    this.$refs.audio.volume = 0.4; 
    const time = sessionStorage.getItem('bgMusicTime') || 0;
    this.$refs.audio.currentTime = time;
    if (this.playing) {
      this.$refs.audio.play().catch(() => this.playing = false);
    }
    setInterval(() => {
      if (this.playing) sessionStorage.setItem('bgMusicTime', this.$refs.audio.currentTime);
    }, 1000);
  },
  toggle() {
    this.playing = !this.playing;
    sessionStorage.setItem('bgMusic', this.playing);
    if(this.playing) this.$refs.audio.play();
    else this.$refs.audio.pause();
  }
}" class="fixed bottom-6 right-6 z-50">
  <audio x-ref="audio" loop>
    <source src="assets/music/music.mp3" type="audio/mpeg">
  </audio>
  <button @click="toggle()" 
          class="w-14 h-14 bg-brand-600 text-white rounded-full flex items-center justify-center shadow-[0_10px_25px_rgba(22,163,74,0.5)] hover:bg-brand-700 transition-all hover:scale-110"
          :class="{'animate-[pulse_2s_ease-in-out_infinite]': playing}"
          title="Play/Pause Music">
    <svg x-show="!playing" class="w-6 h-6 ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
    <svg x-show="playing" x-cloak class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
  </button>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="assets/js/map.js?v=<?= time() ?>"></script>

<script>
// ── PAGE LOADER ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const hideLoader = () => {
    const loader = document.getElementById('pageLoader');
    if(loader && !loader.classList.contains('hidden')) {
      loader.classList.add('hidden');
      if(typeof AOS !== 'undefined') AOS.refresh();
    }
  };
  
  // Sembunyikan setelah 800ms
  setTimeout(hideLoader, 800);
});

// Fallback maksimal 3 detik jika terjadi kendala pada resource
setTimeout(() => {
  const loader = document.getElementById('pageLoader');
  if(loader) loader.classList.add('hidden');
}, 3000);

// ── AOS INIT ─────────────────────────────────────────────
AOS.init({
  duration: 800,
  easing: 'ease-out-cubic',
  once: true,
  offset: 50,
});

// ── CUSTOM CURSOR (Dipertahankan dari aslinya) ───────────
const dot  = document.getElementById('cursorDot');
const ring = document.getElementById('cursorRing');
let mouseX=0, mouseY=0, ringX=0, ringY=0;

if(window.innerWidth > 768) {
  document.addEventListener('mousemove', e => {
    mouseX = e.clientX; mouseY = e.clientY;
    if(dot) { dot.style.left = mouseX + 'px'; dot.style.top  = mouseY + 'px'; }
  });
  (function animRing() {
    ringX += (mouseX - ringX) * .15;
    ringY += (mouseY - ringY) * .15;
    if(ring) { ring.style.left = ringX + 'px'; ring.style.top  = ringY + 'px'; }
    requestAnimationFrame(animRing);
  })();

  document.querySelectorAll('a, button, .cursor-pointer').forEach(el => {
    el.addEventListener('mouseenter', () => {
      if(dot) { dot.style.transform = 'translate(-50%, -50%) scale(2)'; dot.style.background = '#10b981'; }
      if(ring) { ring.style.transform = 'translate(-50%, -50%) scale(1.5)'; ring.style.borderColor = '#10b981'; }
    });
    el.addEventListener('mouseleave', () => {
      if(dot) { dot.style.transform = 'translate(-50%, -50%) scale(1)'; dot.style.background = '#f5b942'; }
      if(ring) { ring.style.transform = 'translate(-50%, -50%) scale(1)'; ring.style.borderColor = 'rgba(200,144,26,.5)'; }
    });
  });
}

// ── FILTER APPLY ──────────────────────────────────────────
function applyFilter(key, val) {
  const p = new URLSearchParams(window.location.search);
  val ? p.set(key, val) : p.delete(key);
  if (key === 'kecamatan') p.delete('kelurahan');
  window.location.href = 'index.php?' + p.toString() + '#destinasi';
}

// ── CARD CLICK & MODAL (SweetAlert & Alpine) ───────────────
function handleCardClick(id, lat, lng) {
  // Show loading state using SweetAlert2
  Swal.fire({
    title: 'Memuat data...',
    text: 'Mohon tunggu sebentar',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  fetch('controllers/api_wisata.php?action=detail&id=' + id)
    .then(r => r.json())
    .then(w => {
      Swal.close();
      if (w.error) { 
        Swal.fire({icon: 'error', title: 'Oops...', text: w.error, confirmButtonColor: '#16a34a'}); 
        return; 
      }

      const emojis = {sejarah:'🏛️',religi:'🕌',edukasi:'🎓',alam:'🌿',kuliner:'🍽️',olahraga:'⚽'};
      const tiket  = w.tiket_masuk > 0 ? 'Rp '+parseInt(w.tiket_masuk).toLocaleString('id-ID') : '<span class="text-emerald-500">Gratis</span>';
      const fasArr = (w.fasilitas||'').split(',').map(s=>s.trim()).filter(Boolean);
      const loc    = [w.alamat, w.kelurahan, w.kecamatan].filter(Boolean).join(', ');
      
      const ratingVal = parseFloat(w.rating||0);
      const stars  = '★'.repeat(Math.round(ratingVal)) + '☆'.repeat(5-Math.round(ratingVal));
      const ratingHtml = `<span>${stars}</span> <span class="text-gray-600 ml-1">${ratingVal.toFixed(1)}</span>`;

      // Dispatch event to Alpine JS modal
      window.dispatchEvent(new CustomEvent('open-modal', { 
        detail: {
          id: w.id,
          lat: w.latitude,
          lng: w.longitude,
          nama: w.nama_wisata,
          kategori: w.kategori || '-',
          emoji: emojis[w.kategori],
          gambar: w.gambar,
          lokasi: loc,
          deskripsi: w.deskripsi || '-',
          tiket: tiket,
          rating_html: ratingHtml,
          jam: w.jam_operasional || '-',
          koordinat: `${parseFloat(w.latitude).toFixed(5)}, ${parseFloat(w.longitude).toFixed(5)}`,
          fasilitas: fasArr
        }
      }));
      
      // Zoom map immediately and scroll to map section
      if(typeof window.zoomToWisata === 'function') {
        window.zoomToWisata(w.id, w.latitude, w.longitude);
      }
    })
    .catch(() => { 
      Swal.close();
      Swal.fire({icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat data dari server.', confirmButtonColor: '#16a34a'});
    });
}
</script>

</body>
</html>