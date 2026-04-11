<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecOps Intelligence | Advanced OSINT Studio</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            500: '#6366f1',
                            600: '#4f46e5',
                        },
                        studio: {
                            bg: '#09090b',
                            card: '#18181b',
                            border: '#27272a',
                            muted: '#a1a1aa'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { background-color: #09090b; }
        .studio-card {
            background-color: #18181b;
            border: 1px solid #27272a;
        }
        #map {
            height: 100%;
            width: 100%;
            border-radius: 1rem;
            filter: grayscale(0.8) invert(1) contrast(1.2) opacity(0.8);
        }
        .data-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #71717a;
            margin-bottom: 2px;
            display: block;
        }
        .data-value {
            font-size: 13px;
            font-weight: 600;
            color: #f4f4f5;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
    </style>
</head>
<body class="text-slate-200 font-sans antialiased selection:bg-brand-500/30 overflow-x-hidden"
      x-data="{ tab: '{{ isset($cve_result) || isset($error_cve) ? 'cve' : (isset($scan_result) || isset($error_scan) ? 'scan' : 'ip') }}' }">

    <!-- Navigation -->
    <nav class="sticky top-0 z-50 w-full border-b border-studio-border bg-studio-bg/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center">
                    <i data-lucide="shield-check" class="text-white w-5 h-5"></i>
                </div>
                <span class="text-sm font-bold tracking-widest text-white uppercase">SecOps<span class="text-brand-500">Intell</span></span>
            </div>

            <div class="flex items-center bg-studio-card p-1 rounded-full border border-studio-border">
                <button @click="tab = 'ip'"
                        :class="tab === 'ip' ? 'bg-zinc-800 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-300'"
                        class="px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider transition-all">
                    Network
                </button>
                <button @click="tab = 'scan'"
                        :class="tab === 'scan' ? 'bg-zinc-800 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-300'"
                        class="px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider transition-all">
                    Threats
                </button>
                <button @click="tab = 'cve'"
                        :class="tab === 'cve' ? 'bg-zinc-800 text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-300'"
                        class="px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider transition-all">
                    CVE Ops
                </button>
            </div>

            <div class="hidden md:flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-[10px] text-studio-muted font-bold uppercase tracking-widest">System Operational</span>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">

        <!-- IP Intelligence Section -->
        <div x-show="tab === 'ip'" x-transition:enter="transition duration-300 translate-y-2" x-cloak class="space-y-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-black text-white tracking-tighter mb-2">Endpoint Lookup</h1>
                    <p class="text-studio-muted text-sm max-w-md">Analisis mendalam infrastruktur jaringan, metadata lokasi, dan konfigurasi zona waktu global.</p>
                </div>
                <!-- Input Box -->
                <div class="studio-card p-1.5 rounded-2xl w-full md:w-96 shadow-2xl">
                    <form action="{{ route('ipgeopost') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="ip_address" value="{{ $ipgeo['ip'] ?? '' }}" placeholder="Target IP..." required
                               class="flex-1 bg-transparent border-none text-sm text-white px-4 focus:ring-0">
                        <button type="submit" class="bg-white hover:bg-zinc-200 text-black p-2.5 rounded-xl transition shadow-lg">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            @if(isset($error))
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-5 rounded-2xl text-sm flex items-center">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-3"></i>
                    {{ $error }}
                </div>
            @endif

            @isset($ipgeo)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Main Identity Card -->
                <div class="lg:col-span-8 space-y-8">
                    <div class="studio-card p-10 rounded-[2.5rem] relative overflow-hidden">
                        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
                            <div class="flex items-center space-x-6">
                                <img src="{{ data_get($ipgeo, 'location.country_flag', data_get($ipgeo, 'country_flag')) }}" class="w-20 h-14 rounded-xl shadow-2xl border border-studio-border">
                                <div>
                                    <h2 class="text-5xl font-black text-white tracking-tighter">{{ $ipgeo['ip'] }}</h2>
                                    <p class="text-brand-500 font-bold text-sm mt-1 uppercase tracking-widest">
                                        {{ data_get($ipgeo, 'location.country_name_official', data_get($ipgeo, 'country_name_official', data_get($ipgeo, 'country_name', 'Unknown'))) }}
                                        {{ data_get($ipgeo, 'location.country_emoji', data_get($ipgeo, 'country_emoji', '')) }}
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-x-12 gap-y-4">
                                <div>
                                    <span class="data-label">Continent</span>
                                    <span class="data-value">{{ data_get($ipgeo, 'location.continent_name', data_get($ipgeo, 'continent_name', 'N/A')) }}</span>
                                </div>
                                <div>
                                    <span class="data-label">Capital</span>
                                    <span class="data-value">{{ data_get($ipgeo, 'location.country_capital', data_get($ipgeo, 'country_capital', 'N/A')) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Data Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mt-12 pt-10 border-t border-studio-border/50">
                            <div>
                                <span class="data-label">State / Province</span>
                                <span class="data-value">{{ data_get($ipgeo, 'location.state_prov', data_get($ipgeo, 'state_prov', 'N/A')) }}</span>
                            </div>
                            <div>
                                <span class="data-label">District</span>
                                <span class="data-value font-mono">{{ data_get($ipgeo, 'location.district', data_get($ipgeo, 'district', 'N/A')) }}</span>
                            </div>
                            <div>
                                <span class="data-label">City</span>
                                <span class="data-value">{{ data_get($ipgeo, 'location.city', data_get($ipgeo, 'city', 'N/A')) }}</span>
                            </div>
                            <div>
                                <span class="data-label">Zip Code</span>
                                <span class="data-value font-mono">{{ data_get($ipgeo, 'location.zipcode', data_get($ipgeo, 'zipcode', 'N/A')) }}</span>
                            </div>
                            <div>
                                <span class="data-label">GeoName ID</span>
                                <span class="data-value font-mono">{{ data_get($ipgeo, 'location.geoname_id', data_get($ipgeo, 'geoname_id', 'N/A')) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Map Module -->
                    <div class="studio-card p-3 rounded-[2.5rem] h-[450px]">
                        <div id="map"></div>
                    </div>
                </div>

                <!-- Sidebar Metadata -->
                <div class="lg:col-span-4 space-y-8">
                    <!-- Network & ASN -->
                    <div class="studio-card p-8 rounded-[2rem]">
                        <h3 class="text-white font-bold text-sm uppercase tracking-widest mb-6 border-b border-studio-border pb-4">Network Infrastructure</h3>
                        <div class="space-y-6">
                            <div>
                                <span class="data-label">ASN Number</span>
                                <span class="data-value text-brand-500">{{ data_get($ipgeo, 'asn.as_number', 'N/A') }}</span>
                            </div>
                            <div>
                                <span class="data-label">Organization</span>
                                <span class="data-value">{{ data_get($ipgeo, 'asn.organization', 'N/A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Regional Context -->
                    <div class="studio-card p-8 rounded-[2rem]">
                        <h3 class="text-white font-bold text-sm uppercase tracking-widest mb-6 border-b border-studio-border pb-4">Regional Context</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <span class="data-label">Currency</span>
                                <span class="data-value">{{ data_get($ipgeo, 'currency.name', 'N/A') }}</span>
                            </div>
                            <div>
                                <span class="data-label">TLD</span>
                                <span class="data-value text-brand-500">{{ data_get($ipgeo, 'country_metadata.tld', data_get($ipgeo, 'tld', 'N/A')) }}</span>
                            </div>
                            <div>
                                <span class="data-label">Calling Code</span>
                                <span class="data-value">{{ data_get($ipgeo, 'country_metadata.calling_code', data_get($ipgeo, 'calling_code', 'N/A')) }}</span>
                            </div>
                        </div>
                        <div class="mt-6">
                            <span class="data-label">Official Languages</span>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @php
                                    $langs = data_get($ipgeo, 'country_metadata.languages', data_get($ipgeo, 'languages', []));
                                    if (is_string($langs)) $langs = explode(',', $langs);
                                @endphp
                                @foreach($langs as $lang)
                                    <span class="text-[10px] font-bold bg-studio-border px-2 py-1 rounded">{{ trim($lang) }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Time & Dynamics -->
                    <div class="studio-card p-8 rounded-[2rem]">
                        <h3 class="text-white font-bold text-sm uppercase tracking-widest mb-6 border-b border-studio-border pb-4">Time Intelligence</h3>
                        <div class="space-y-6">
                            <div>
                                <span class="data-label">Zone Name</span>
                                <span class="data-value">{{ data_get($ipgeo, 'time_zone.name', 'N/A') }}</span>
                            </div>
                            <div>
                                <span class="data-label">Current Time</span>
                                <span class="data-value font-mono text-brand-500">{{ data_get($ipgeo, 'time_zone.current_time', 'N/A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var lat = {{ data_get($ipgeo, 'location.latitude', data_get($ipgeo, 'latitude', 0)) }};
                    var lng = {{ data_get($ipgeo, 'location.longitude', data_get($ipgeo, 'longitude', 0)) }};
                    var map = L.map('map', { zoomControl: false }).setView([lat, lng], 13);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);
                    L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'minimal-pin',
                            html: '<div class="w-10 h-10 -ml-5 -mt-5 flex items-center justify-center"><div class="absolute w-8 h-8 bg-brand-500/20 rounded-full animate-ping"></div><div class="w-3 h-3 bg-brand-600 rounded-full border-2 border-white shadow-xl"></div></div>',
                            iconSize: [0, 0]
                        })
                    }).addTo(map);
                });
            </script>
            @endisset
        </div>

        <!-- Threat Analysis Section -->
        <div x-show="tab === 'scan'" x-transition:enter="transition duration-300 translate-y-2" x-cloak class="space-y-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-black text-white tracking-tighter mb-2">Threat Scanner</h1>
                    <p class="text-studio-muted text-sm max-w-md">Pemindaian keamanan URL berbasis AI menggunakan 70+ engine antivirus global secara simultan.</p>
                </div>
                <!-- Input Box -->
                <div class="studio-card p-1.5 rounded-2xl w-full md:w-96 shadow-2xl">
                    <form action="{{ route('scanpost') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="url" name="url" value="{{ $scanned_url ?? '' }}" placeholder="https://..." required
                               class="flex-1 bg-transparent border-none text-sm text-white px-4 focus:ring-0">
                        <button type="submit" class="bg-white hover:bg-zinc-200 text-black p-2.5 rounded-xl transition shadow-lg">
                            <i data-lucide="zap" class="w-4 h-4 fill-current"></i>
                        </button>
                    </form>
                </div>
            </div>

            @if(isset($error_scan))
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-5 rounded-2xl text-sm">
                    {{ $error_scan }}
                </div>
            @endif

            @isset($scan_result)
                @php
                    $status = data_get($scan_result, 'attributes.status', 'queued');
                    $stats = data_get($scan_result, 'attributes.stats', []);
                    $malicious = $stats['malicious'] ?? 0;
                    $is_danger = $malicious > 0;
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Analysis Header -->
                    <div class="lg:col-span-12 studio-card p-10 rounded-[2.5rem] flex flex-col md:flex-row items-center justify-between gap-8 border-b-8 {{ $is_danger ? 'border-red-500' : 'border-green-500' }}">
                        <div class="flex items-center space-x-8">
                            <div class="w-20 h-20 rounded-[2rem] flex items-center justify-center border-4 {{ $is_danger ? 'bg-red-500/10 border-red-500/20' : 'bg-green-500/10 border-green-500/20' }}">
                                <i data-lucide="{{ $is_danger ? 'shield-alert' : 'shield-check' }}" class="w-10 h-10 {{ $is_danger ? 'text-red-500' : 'text-green-500' }}"></i>
                            </div>
                            <div>
                                <p class="data-label">Target Analysis ({{ strtoupper($status) }})</p>
                                <h2 class="text-2xl font-black text-white tracking-tight break-all max-w-2xl">{{ $scanned_url }}</h2>
                                <p class="text-studio-muted text-xs font-mono mt-1">ID: {{ $scan_result['id'] }}</p>
                            </div>
                        </div>
                        <div class="text-center md:text-right">
                            <p class="data-label">Security Verdict</p>
                            <span class="text-2xl font-black {{ $is_danger ? 'text-red-500' : 'text-green-500' }} uppercase tracking-tighter">
                                {{ $is_danger ? 'Malicious Detected' : 'Unrated / Clean' }}
                            </span>
                        </div>
                    </div>

                    <!-- Stats & Details -->
                    <div class="lg:col-span-4 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="studio-card p-6 rounded-3xl text-center">
                                <span class="data-label text-red-500">Malicious</span>
                                <span class="text-4xl font-black text-red-500">{{ $stats['malicious'] ?? 0 }}</span>
                            </div>
                            <div class="studio-card p-6 rounded-3xl text-center">
                                <span class="data-label text-amber-500">Suspicious</span>
                                <span class="text-4xl font-black text-amber-500">{{ $stats['suspicious'] ?? 0 }}</span>
                            </div>
                            <div class="studio-card p-6 rounded-3xl text-center">
                                <span class="data-label text-green-500">Harmless</span>
                                <span class="text-4xl font-black text-green-500">{{ $stats['harmless'] ?? 0 }}</span>
                            </div>
                            <div class="studio-card p-6 rounded-3xl text-center">
                                <span class="data-label">Undetected</span>
                                <span class="text-4xl font-black text-white">{{ $stats['undetected'] ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="studio-card p-8 rounded-[2rem]">
                            <h3 class="text-white font-bold text-sm uppercase tracking-widest mb-6 border-b border-studio-border pb-4">Scan Metadata</h3>
                            <div class="space-y-6">
                                <div>
                                    <span class="data-label">File SHA-256</span>
                                    <span class="data-value font-mono text-[10px] break-all text-brand-500">{{ data_get($scan_result, 'meta.file_info.sha256', 'N/A') }}</span>
                                </div>
                                <div>
                                    <span class="data-label">Scan Date (Epoch)</span>
                                    <span class="data-value">{{ data_get($scan_result, 'attributes.date', 'N/A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Vendor Grid -->
                    <div class="lg:col-span-8 studio-card rounded-[2.5rem] overflow-hidden">
                        <div class="px-8 py-6 border-b border-studio-border bg-zinc-900/30 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-white uppercase tracking-widest">Security Engine Telemetry</h3>
                            <span class="text-[10px] text-studio-muted font-bold">Total Engines: {{ count(data_get($scan_result, 'attributes.results', [])) }}</span>
                        </div>
                        <div class="max-h-[600px] overflow-y-auto custom-scrollbar">
                            <div class="grid grid-cols-1 md:grid-cols-2 divide-x divide-y divide-studio-border">
                                @foreach(data_get($scan_result, 'attributes.results', []) as $vendor => $data)
                                <div class="flex items-center justify-between px-8 py-5 hover:bg-white/[0.02] transition">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-zinc-300">{{ $vendor }}</span>
                                        <span class="text-[10px] text-studio-muted uppercase tracking-tighter">{{ $data['method'] }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="text-[10px] font-black uppercase tracking-widest {{ $data['category'] === 'malicious' ? 'text-red-500' : ($data['category'] === 'suspicious' ? 'text-amber-500' : 'text-studio-muted') }}">
                                            {{ $data['result'] }}
                                        </span>
                                        <div class="w-2 h-2 rounded-full {{ $data['category'] === 'malicious' ? 'bg-red-500 shadow-[0_0_10px_#ef4444]' : ($data['category'] === 'suspicious' ? 'bg-amber-500' : 'bg-green-500/50') }}"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endisset
        </div>

        <!-- CVE Intelligence Section -->
        <div x-show="tab === 'cve'" x-transition:enter="transition duration-300 translate-y-2" x-cloak class="space-y-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-black text-white tracking-tighter mb-2">Vulnerability Database</h1>
                    <p class="text-studio-muted text-sm max-w-md">Akses real-time ke National Vulnerability Database (NVD) untuk identifikasi exploit dan mitigasi keamanan.</p>
                </div>
                <!-- Input Box -->
                <div class="studio-card p-1.5 rounded-2xl w-full md:w-96 shadow-2xl">
                    <form action="{{ route('cvepost') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="cve_id" value="{{ $searched_cve ?? '' }}" placeholder="CVE-2019-1010218..."
                               class="flex-1 bg-transparent border-none text-sm text-white px-4 focus:ring-0">
                        <button type="submit" class="bg-white hover:bg-zinc-200 text-black p-2.5 rounded-xl transition shadow-lg">
                            <i data-lucide="database" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            @if(isset($error_cve))
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-5 rounded-2xl text-sm">
                    {{ $error_cve }}
                </div>
            @endif

            @isset($cve_result)
                <div class="space-y-8">
                    @forelse($cve_result['vulnerabilities'] ?? [] as $item)
                        @php
                            $cve = $item['cve'];
                            $metrics = $cve['metrics'];
                            $cvss31 = data_get($metrics, 'cvssMetricV31.0.cvssData') ?? data_get($metrics, 'cvssMetricV30.0.cvssData');
                            $cvss2 = data_get($metrics, 'cvssMetricV2.0.cvssData');

                            $score = data_get($cvss31, 'baseScore', data_get($cvss2, 'baseScore', 'N/A'));
                            $severity = data_get($cvss31, 'baseSeverity', data_get($metrics, 'cvssMetricV2.0.baseSeverity', 'UNKNOWN'));

                            $color = match(strtoupper($severity)) {
                                'CRITICAL' => 'text-red-500 border-red-500/20 bg-red-500/10',
                                'HIGH' => 'text-orange-500 border-orange-500/20 bg-orange-500/10',
                                'MEDIUM' => 'text-yellow-500 border-yellow-500/20 bg-yellow-500/10',
                                'LOW' => 'text-green-500 border-green-500/20 bg-green-500/10',
                                default => 'text-zinc-500 border-zinc-500/20 bg-zinc-500/10'
                            };
                        @endphp

                        <div class="studio-card p-10 rounded-[2.5rem] relative overflow-hidden">
                            <div class="flex flex-col lg:flex-row justify-between gap-8">
                                <div class="flex-1 space-y-6">
                                    <div class="flex items-center space-x-4">
                                        <span class="px-3 py-1 bg-brand-600 rounded-lg text-[10px] font-black uppercase tracking-widest text-white">CVE Identity</span>
                                        <span class="text-studio-muted text-xs font-mono">Published: {{ \Carbon\Carbon::parse($cve['published'])->format('d M Y') }}</span>
                                    </div>
                                    <h2 class="text-4xl font-black text-white tracking-tighter">{{ $cve['id'] }}</h2>

                                    <div class="prose prose-invert max-w-none">
                                        <p class="text-zinc-400 text-sm leading-relaxed">
                                            {{ collect($cve['descriptions'])->where('lang', 'en')->first()['value'] ?? 'No description available.' }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-3 pt-4">
                                        @foreach($cve['weaknesses'] ?? [] as $weakness)
                                            @foreach($weakness['description'] as $desc)
                                                <span class="px-3 py-1 border border-studio-border rounded-full text-[10px] font-bold text-brand-500 uppercase">{{ $desc['value'] }}</span>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>

                                <div class="lg:w-72 space-y-6">
                                    <div class="studio-card p-8 rounded-3xl text-center {{ $color }}">
                                        <p class="text-[10px] font-black uppercase tracking-[0.2em] mb-2">CVSS Score</p>
                                        <p class="text-6xl font-black">{{ $score }}</p>
                                        <p class="text-xs font-bold mt-2 uppercase tracking-widest">{{ $severity }}</p>
                                    </div>

                                    <div class="studio-card p-6 rounded-2xl space-y-4">
                                        <h4 class="text-[10px] font-black text-white uppercase tracking-widest border-b border-studio-border pb-2">Vector Details</h4>
                                        <div class="grid grid-cols-1 gap-3">
                                            @if($cvss31)
                                                <div class="flex justify-between">
                                                    <span class="text-[10px] text-studio-muted uppercase">Attack Vector</span>
                                                    <span class="text-[10px] text-white font-bold">{{ data_get($cvss31, 'attackVector') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-[10px] text-studio-muted uppercase">Complexity</span>
                                                    <span class="text-[10px] text-white font-bold">{{ data_get($cvss31, 'attackComplexity') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-[10px] text-studio-muted uppercase">Privileges</span>
                                                    <span class="text-[10px] text-white font-bold">{{ data_get($cvss31, 'privilegesRequired') }}</span>
                                                </div>
                                            @elseif($cvss2)
                                                <div class="flex justify-between">
                                                    <span class="text-[10px] text-studio-muted uppercase">Access Vector</span>
                                                    <span class="text-[10px] text-white font-bold">{{ data_get($cvss2, 'accessVector') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10 pt-8 border-t border-studio-border/50">
                                <h4 class="text-[10px] font-black text-white uppercase tracking-[0.2em] mb-4">Security References</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach(collect($cve['references'] ?? [])->take(6) as $ref)
                                        <a href="{{ $ref['url'] }}" target="_blank" class="flex items-center space-x-3 p-3 studio-card rounded-xl hover:bg-zinc-800/50 transition truncate">
                                            <i data-lucide="external-link" class="w-3 h-3 text-brand-500"></i>
                                            <span class="text-[10px] font-medium text-zinc-400 truncate">{{ $ref['url'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="studio-card p-20 rounded-[3rem] text-center">
                            <i data-lucide="search-x" class="w-12 h-12 text-studio-muted mx-auto mb-4"></i>
                            <p class="text-studio-muted text-sm uppercase font-bold tracking-widest">No CVE record found for identity: {{ $searched_cve }}</p>
                        </div>
                    @endforelse
                </div>
            @endisset
        </div>

    </main>

    <!-- Global Background Elements -->
    <div class="fixed top-0 left-0 w-full h-[600px] bg-gradient-to-b from-brand-500/[0.03] to-transparent -z-10 pointer-events-none"></div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
