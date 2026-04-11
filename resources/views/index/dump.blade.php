<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Data Dump Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #09090b; color: #e4e4e7; font-family: 'Courier New', Courier, monospace; }
        .sf-dump { background-color: #18181b !important; border: 1px solid #27272a !important; border-radius: 0.5rem !important; }
    </style>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto">
        <header class="mb-10 border-b border-zinc-800 pb-6">
            <h1 class="text-2xl font-bold text-white">Advanced SecOps Data Dump</h1>
            <p class="text-zinc-500 text-sm mt-1">Debugging output for IP Intelligence and Threat Scanner APIs</p>
        </header>

        <div class="space-y-12">
            <!-- IP Geolocation Result -->
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-indigo-400">1. IP Geolocation Result ({{ $scanned_ip }})</h2>
                    @if($ipgeo_result)
                        <span class="text-[10px] bg-green-900/30 text-green-400 px-2 py-1 rounded border border-green-800 uppercase">Success</span>
                    @else
                        <span class="text-[10px] bg-red-900/30 text-red-400 px-2 py-1 rounded border border-red-800 uppercase">Failed</span>
                    @endif
                </div>
                
                @if($ipgeo_result)
                    @dump($ipgeo_result)
                @else
                    <div class="bg-red-900/10 border border-red-900/30 p-4 rounded-lg text-red-400 text-xs">
                        <strong>Error Body:</strong><br>
                        <pre class="mt-2 overflow-x-auto">{{ $ipgeo_error }}</pre>
                    </div>
                @endif
            </section>

            <!-- VirusTotal Scan Result -->
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-indigo-400">2. VirusTotal Scan Result ({{ $scanned_url }})</h2>
                    @if($scan_result)
                        <span class="text-[10px] bg-green-900/30 text-green-400 px-2 py-1 rounded border border-green-800 uppercase">Success</span>
                    @else
                        <span class="text-[10px] bg-red-900/30 text-red-400 px-2 py-1 rounded border border-red-800 uppercase">Failed</span>
                    @endif
                </div>

                @if($scan_result)
                    @dump($scan_result)
                @else
                    <div class="bg-red-900/10 border border-red-900/30 p-4 rounded-lg text-red-400 text-xs">
                        <strong>Error Message:</strong><br>
                        <pre class="mt-2 overflow-x-auto">{{ $scan_error }}</pre>
                    </div>
                @endif
            </section>
        </div>

        <footer class="mt-20 pt-10 border-t border-zinc-800 text-center">
            <a href="{{ route('home') }}" class="text-zinc-500 hover:text-white text-xs transition">← Back to Terminal</a>
        </footer>
    </div>
</body>
</html>
