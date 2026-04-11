<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecOps Dashboard | Modern Geolocation & URL Scanner</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- UI Frameworks -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        dark: { 900: '#0B0F1A', 800: '#141B2D', 700: '#1F2937' },
                        primary: { 500: '#3B82F6', 600: '#2563EB' }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .glass { background: rgba(31, 41, 55, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        #map { height: 400px; width: 100%; border-radius: 1rem; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }
    </style>
</head>
<body class="bg-dark-900 text-gray-200 antialiased overflow-hidden"
      x-data="{ tab: '{{ isset($scan_result) || isset($error_scan) ? 'scan' : 'ip' }}' }">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="w-20 lg:w-64 bg-dark-800 border-r border-gray-800 flex flex-col transition-all duration-300">
            <div class="p-6 mb-8">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-primary-600 rounded-lg">
                        <i data-lucide="shield-check" class="text-white w-6 h-6"></i>
                    </div>
                    <span class="hidden lg:block text-xl font-bold tracking-tight text-white">SecOps<span class="text-primary-500">Hub</span></span>
                </div>
            </div>

            <nav class="flex-1 px-4 space-y-2 overflow-y-auto custom-scrollbar">
                <button @click="tab = 'ip'"
                        :class="tab === 'ip' ? 'bg-primary-600/10 text-primary-500 border-primary-600' : 'text-gray-400 border-transparent hover:bg-gray-700/50 hover:text-white'"
                        class="w-full flex items-center p-3 rounded-xl border-l-4 transition-all group">
                    <i data-lucide="map-pin" class="w-5 h-5 lg:mr-3"></i>
                    <span class="hidden lg:block font-medium">IP Tracker</span>
                </button>
                <button @click="tab = 'scan'"
                        :class="tab === 'scan' ? 'bg-primary-600/10 text-primary-500 border-primary-600' : 'text-gray-400 border-transparent hover:bg-gray-700/50 hover:text-white'"
                        class="w-full flex items-center p-3 rounded-xl border-l-4 transition-all group">
                    <i data-lucide="search" class="w-5 h-5 lg:mr-3"></i>
                    <span class="hidden lg:block font-medium">URL Scanner</span>
                </button>
            </nav>

            <div class="p-4 mt-auto border-t border-gray-800">
                <div class="flex items-center space-x-3 p-2 bg-gray-900/50 rounded-xl">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary-600 to-indigo-600 flex items-center justify-center text-xs font-bold text-white uppercase">AD</div>
                    <div class="hidden lg:block">
                        <p class="text-sm font-semibold text-white">Admin User</p>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wider">SecOps Pro</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 bg-dark-900 relative">

            <!-- Header -->
            <header class="h-16 flex items-center justify-between px-8 border-b border-gray-800 bg-dark-900/50 backdrop-blur-md sticky top-0 z-10">
                <h2 class="text-lg font-semibold text-white" x-text="tab === 'ip' ? 'IP Tracker & Geolocation' : 'URL Virus Scanner'"></h2>
                <div class="flex items-center space-x-4">
                    <div class="text-xs text-gray-500 hidden md:block">Server Status: <span class="text-green-500 font-medium">Healthy</span></div>
                    <div class="w-px h-4 bg-gray-800 mx-2"></div>
                    <button class="p-2 text-gray-400 hover:text-white transition"><i data-lucide="bell" class="w-5 h-5"></i></button>
                </div>
            </header>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">

                <!-- Tab: IP Tracker -->
                <div x-show="tab === 'ip'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-cloak>
                    <div class="max-w-4xl">
                        <h3 class="text-2xl font-bold text-white mb-2">Lacak Geolocation</h3>
                        <p class="text-gray-400 mb-8 text-sm">Identifikasi lokasi fisik, ISP, dan zona waktu dari alamat IP target.</p>

                        <!-- Input Card -->
                        <div class="glass p-6 rounded-2xl mb-8">
                            <form action="{{ route('ipgeopost') }}" method="POST" class="flex flex-col md:flex-row gap-4">
                                @csrf
                                <div class="relative flex-1 group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-primary-500 transition">
                                        <i data-lucide="globe" class="w-5 h-5"></i>
                                    </div>
                                    <input type="text" name="ip_address" value="{{ $ipgeo['ip'] ?? '' }}" placeholder="Masukkan Alamat IP (Contoh: 8.8.8.8)"
                                           class="w-full bg-dark-900/50 border border-gray-700 text-white rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition outline-none" required>
                                </div>
                                <button type="submit" class="bg-primary-600 hover:bg-primary-500 text-white px-8 py-3 rounded-xl font-bold transition flex items-center justify-center space-x-2 shadow-lg shadow-primary-600/20 active:scale-95">
                                    <i data-lucide="navigation" class="w-5 h-5"></i>
                                    <span>Lacak Sekarang</span>
                                </button>
                            </form>
                        </div>

                        <!-- Results -->
                        @if(isset($error))
                            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl mb-8 flex items-center">
                                <i data-lucide="alert-circle" class="w-5 h-5 mr-3"></i>
                                {{ $error }}
                            </div>
                        @endif

                        @isset($ipgeo)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Main Info Card -->
                            <div class="md:col-span-2 glass p-8 rounded-3xl relative overflow-hidden group">
                                <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-110 transition duration-700">
                                    <i data-lucide="info" class="w-32 h-32"></i>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex items-center space-x-4 mb-8">
                                        <img src="{{ $ipgeo['country_flag'] ?? '' }}" alt="Flag" class="w-12 h-8 rounded shadow border border-gray-700">
                                        <div>
                                            <h4 class="text-3xl font-bold text-white tracking-tight">{{ $ipgeo['ip'] }}</h4>
                                            <p class="text-gray-400 font-medium">{{ $ipgeo['country_name'] }} • {{ $ipgeo['city'] }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                                        <div class="space-y-1">
                                            <span class="text-[10px] uppercase font-bold text-gray-500 tracking-widest">ISP Provider</span>
                                            <p class="text-white font-medium truncate">{{ $ipgeo['isp'] ?? 'N/A' }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <span class="text-[10px] uppercase font-bold text-gray-500 tracking-widest">AS Number</span>
                                            <p class="text-white font-medium">{{ $ipgeo['asn'] ?? 'N/A' }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <span class="text-[10px] uppercase font-bold text-gray-500 tracking-widest">Time Zone</span>
                                            <p class="text-white font-medium">{{ $ipgeo['time_zone']['name'] ?? 'N/A' }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <span class="text-[10px] uppercase font-bold text-gray-500 tracking-widest">Coordinates</span>
                                            <p class="text-white font-medium">{{ $ipgeo['latitude'] }}, {{ $ipgeo['longitude'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Small Stats Card -->
                            <div class="space-y-6">
                                <div class="glass p-6 rounded-3xl">
                                    <span class="text-[10px] uppercase font-bold text-gray-500 tracking-widest block mb-2">Connection Status</span>
                                    <div class="flex items-center text-green-500 font-bold text-lg">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-ping"></span>
                                        Active
                                    </div>
                                </div>
                                <div class="glass p-6 rounded-3xl">
                                    <span class="text-[10px] uppercase font-bold text-gray-500 tracking-widest block mb-2">Region</span>
                                    <p class="text-white font-bold text-lg">{{ $ipgeo['state_prov'] ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Map Card -->
                            <div class="md:col-span-3 glass p-4 rounded-3xl">
                                <div id="map"></div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var lat = {{ $ipgeo['latitude'] }};
                                var lng = {{ $ipgeo['longitude'] }};
                                var map = L.map('map', { zoomControl: false }).setView([lat, lng], 13);

                                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                                    attribution: '© OpenStreetMap contributors © CARTO'
                                }).addTo(map);

                                L.control.zoom({ position: 'bottomright' }).addTo(map);

                                var customIcon = L.divIcon({
                                    className: 'custom-div-icon',
                                    html: '<div class="w-10 h-10 bg-primary-500/20 border-2 border-primary-500 rounded-full flex items-center justify-center animate-pulse"><div class="w-3 h-3 bg-primary-500 rounded-full"></div></div>',
                                    iconSize: [40, 40],
                                    iconAnchor: [20, 20]
                                });

                                L.marker([lat, lng], {icon: customIcon}).addTo(map)
                                    .bindPopup('<b class="text-dark-900">{{ $ipgeo["city"] }}</b>')
                                    .openPopup();
                            });
                        </script>
                        @endisset
                    </div>
                </div>

                <!-- Tab: URL Scanner -->
                <div x-show="tab === 'scan'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-cloak>
                    <div class="max-w-4xl">
                        <h3 class="text-2xl font-bold text-white mb-2">URL Virus Scanner</h3>
                        <p class="text-gray-400 mb-8 text-sm">Analisis URL secara real-time untuk mendeteksi ancaman Phishing, Malware, dan Spyware.</p>

                        <!-- Input Card -->
                        <div class="glass p-6 rounded-2xl mb-8">
                            <form action="{{ route('scanpost') }}" method="POST" class="flex flex-col md:flex-row gap-4">
                                @csrf
                                <div class="relative flex-1 group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-primary-500 transition">
                                        <i data-lucide="link" class="w-5 h-5"></i>
                                    </div>
                                    <input type="url" name="url" value="{{ $scanned_url ?? '' }}" placeholder="https://domain-target.com"
                                           class="w-full bg-dark-900/50 border border-gray-700 text-white rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition outline-none" required>
                                </div>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-xl font-bold transition flex items-center justify-center space-x-2 shadow-lg shadow-indigo-600/20 active:scale-95">
                                    <i data-lucide="shield" class="w-5 h-5"></i>
                                    <span>Mulai Analisis</span>
                                </button>
                            </form>
                        </div>

                        <!-- Scan Status Info -->
                        @if(isset($error_scan))
                            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl mb-8">
                                {{ $error_scan }}
                            </div>
                        @endif

                        @isset($scan_result)
                            @php
                                $status = $scan_result['attributes']['status'] ?? 'queued';
                                $malicious = $scan_result['attributes']['stats']['malicious'] ?? 0;
                                $suspicious = $scan_result['attributes']['stats']['suspicious'] ?? 0;
                            @endphp

                            <div class="space-y-6">
                                <!-- Summary Hero -->
                                <div class="glass p-8 rounded-3xl border-l-8 {{ $malicious > 0 ? 'border-red-500' : 'border-green-500' }}">
                                    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <span class="px-3 py-1 bg-gray-900 rounded-full text-[10px] font-bold text-gray-500 tracking-widest uppercase">Target URL</span>
                                                <span class="text-xs text-gray-500">{{ date('d M Y, H:i:s') }}</span>
                                            </div>
                                            <h4 class="text-xl font-bold text-white truncate break-all">{{ $scanned_url }}</h4>
                                        </div>
                                        <div class="text-center md:text-right">
                                            @if($status === 'completed')
                                                <p class="text-sm text-gray-500 font-bold mb-1 uppercase tracking-wider">Keamanan</p>
                                                <h5 class="text-4xl font-extrabold {{ $malicious > 0 ? 'text-red-500' : 'text-green-500' }}">
                                                    {{ $malicious > 0 ? 'BERBAHAYA' : 'AMAN' }}
                                                </h5>
                                            @else
                                                <div class="flex items-center text-primary-500 space-x-2">
                                                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin"></i>
                                                    <span class="text-xl font-bold uppercase tracking-tighter animate-pulse">Scanning...</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Stats Grid -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="glass p-6 rounded-2xl text-center">
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Malicious</p>
                                        <p class="text-3xl font-bold text-red-500">{{ $malicious }}</p>
                                    </div>
                                    <div class="glass p-6 rounded-2xl text-center">
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Suspicious</p>
                                        <p class="text-3xl font-bold text-yellow-500">{{ $suspicious }}</p>
                                    </div>
                                    <div class="glass p-6 rounded-2xl text-center">
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Harmless</p>
                                        <p class="text-3xl font-bold text-green-500">{{ $scan_result['attributes']['stats']['harmless'] }}</p>
                                    </div>
                                    <div class="glass p-6 rounded-2xl text-center">
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Undetected</p>
                                        <p class="text-3xl font-bold text-gray-400">{{ $scan_result['attributes']['stats']['undetected'] }}</p>
                                    </div>
                                </div>

                                <!-- Detail Table -->
                                <div class="glass rounded-3xl overflow-hidden">
                                    <div class="px-8 py-6 border-b border-gray-800 flex justify-between items-center">
                                        <h4 class="font-bold text-white">Laporan Vendor Keamanan</h4>
                                        <span class="text-xs text-gray-500">Menampilkan 10 Vendor Teratas</span>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead class="bg-gray-900/50 text-[10px] font-bold text-gray-500 uppercase tracking-widest">
                                                <tr>
                                                    <th class="px-8 py-4">Security Engine</th>
                                                    <th class="px-8 py-4">Category</th>
                                                    <th class="px-8 py-4 text-right">Result</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-800">
                                                @php $top_results = collect($scan_result['attributes']['results'])->take(10); @endphp
                                                @foreach($top_results as $vendor => $data)
                                                <tr class="hover:bg-gray-800/30 transition">
                                                    <td class="px-8 py-4 text-sm font-semibold text-gray-300">{{ $vendor }}</td>
                                                    <td class="px-8 py-4">
                                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded {{ $data['category'] === 'malicious' ? 'text-red-400 bg-red-400/10' : ($data['category'] === 'suspicious' ? 'text-yellow-400 bg-yellow-400/10' : 'text-green-400 bg-green-400/10') }}">
                                                            {{ $data['category'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-8 py-4 text-sm text-right font-mono text-gray-500">{{ $data['result'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endisset
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>
