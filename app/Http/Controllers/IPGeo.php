<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class IPGeo extends Controller
{
    public function index()
    {
        return view('index.ipgeo');
    }

    public function ipgeo(Request $request)
    {
        $ip_address = $request->input('ip_address', '8.8.8.8');
        $api_key = env('GEOIP_API_KEY', '');

        $response = Http::timeout(30)
            ->retry(3, 500)
            ->withOptions([
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])
            ->get('https://api.ipgeolocation.io/ipgeo', [
                'apiKey' => $api_key,
                'ip' => $ip_address,
            ]);

        if ($response->successful()) {
            return view('index.ipgeo', [
                'ipgeo' => $response->json()
            ]);
        }

        return view('index.ipgeo', [
            'error' => 'Gagal mengambil data dari API: ' . ($response->json()['message'] ?? 'Unknown Error')
        ]);
    }

    public function scan(Request $request)
    {
        $url_to_scan = $request->input('url');
        $api_key = env('VIRUSTOTAL_API_KEY', '');

        $submit_response = Http::timeout(30)
            ->retry(3, 500)
            ->withOptions([
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])
            ->asForm()
            ->withHeaders([
                'x-apikey' => $api_key,
                'accept' => 'application/json',
            ])->post('https://www.virustotal.com/api/v3/urls', [
                    'url' => $url_to_scan
                ]);

        if (!$submit_response->successful()) {
            return view('index.ipgeo', [
                'error_scan' => 'Gagal mengirim URL ke VirusTotal: ' . ($submit_response->json()['error']['message'] ?? 'Unknown Error')
            ]);
        }

        $analysis_id = $submit_response->json('data.id');

        sleep(3);

        $analysis_response = Http::timeout(30)
            ->retry(3, 500)
            ->withOptions([
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])
            ->withHeaders([
                'x-apikey' => $api_key,
                'accept' => 'application/json',
            ])->get('https://www.virustotal.com/api/v3/analyses/' . $analysis_id);

        if ($analysis_response->successful()) {
            $data = $analysis_response->json('data');

            if ($data['attributes']['status'] !== 'completed') {
                sleep(2);
                $analysis_response = Http::timeout(30)->withHeaders(['x-apikey' => $api_key])
                    ->get('https://www.virustotal.com/api/v3/analyses/' . $analysis_id);
                $data = $analysis_response->json('data');
            }

            return view('index.ipgeo', [
                'scan_result' => $data,
                'scanned_url' => $url_to_scan
            ]);
        }

        return view('index.ipgeo', [
            'error_scan' => 'Gagal mengambil hasil analisis: ' . ($analysis_response->json()['error']['message'] ?? 'Unknown Error')
        ]);
    }

    public function cve(Request $request)
    {
        $cve_id = $request->input('cve_id');
        $url = 'https://services.nvd.nist.gov/rest/json/cves/2.0';
        $params = [];

        if ($cve_id) {
            $upper_cve_id = strtoupper($cve_id);
            if (in_array($upper_cve_id, ['HIGH', 'MEDIUM', 'LOW', 'CRITICAL'])) {
                $params['cvssV3Severity'] = $upper_cve_id;
            } else {
                $params['cveId'] = $cve_id;
            }
        } else {
            $endDate = Carbon::now()->setTimezone('UTC');
            $startDate = Carbon::now()->setTimezone('UTC')->subDays(7);

            $params['pubStartDate'] = $startDate->format('Y-m-d\TH:i:s.000');
            $params['pubEndDate'] = $endDate->format('Y-m-d\TH:i:s.000');

            $params['resultsPerPage'] = 50;
        }

        $response = Http::timeout(30)
            ->retry(3, 500)
            ->withOptions([
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])
            ->get($url, $params);

        if ($response->successful()) {
            $data = $response->json();

            // Limit results to 30 items for default search or severity search
            $is_severity = $cve_id && in_array(strtoupper($cve_id), ['HIGH', 'MEDIUM', 'LOW', 'CRITICAL']);
            if ((!$cve_id || $is_severity) && isset($data['vulnerabilities'])) {
                $vulnerabilities = array_reverse($data['vulnerabilities']);
                $data['vulnerabilities'] = array_slice($vulnerabilities, 0, 30);
            }

            return view('index.ipgeo', [
                'cve_result' => $data,
                'searched_cve' => $cve_id
            ]);
        }

        return view('index.ipgeo', [
            'error_cve' => 'Gagal mengambil data CVE: ' . ($response->json()['message'] ?? 'API Error')
        ]);
    }

    public function dumptest()
    {
        $url_to_scan = 'http://evil.com';
        $ip_address = '8.8.8.8';

        $scan_api = env('VIRUSTOTAL_API_KEY', '');
        $geoip_key = env('GEOIP_API_KEY', '');

        $ip_response = Http::timeout(30)->retry(3, 500)
            ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
            ->get('https://api.ipgeolocation.io/ipgeo', [
                'apiKey' => $geoip_key,
                'ip' => $ip_address,
            ]);

        $endDate = Carbon::now()->setTimezone('UTC');
        $startDate = Carbon::now()->setTimezone('UTC')->subDays(7);
        $cve_response = Http::timeout(30)
            ->retry(3, 500)
            ->withOptions([
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])
            ->get('https://services.nvd.nist.gov/rest/json/cves/2.0', [
                'pubStartDate' => $startDate->format('Y-m-d\TH:i:s.000'),
                'pubEndDate' => $endDate->format('Y-m-d\TH:i:s.000'),
                'resultsPerPage' => 50
            ]);

        $submit_response = Http::timeout(30)->retry(3, 500)
            ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
            ->asForm()->withHeaders([
                    'x-apikey' => $scan_api,
                    'accept' => 'application/json',
                ])->post('https://www.virustotal.com/api/v3/urls', [
                    'url' => $url_to_scan
                ]);

        $scan_data = null;
        $scan_error = null;

        if ($submit_response->successful()) {
            $analysis_id = $submit_response->json('data.id');
            sleep(3);

            $analysis_response = Http::timeout(30)->retry(3, 500)
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->withHeaders([
                    'x-apikey' => $scan_api,
                    'accept' => 'application/json',
                ])->get('https://www.virustotal.com/api/v3/analyses/' . $analysis_id);

            if ($analysis_response->successful()) {
                $scan_data = $analysis_response->json();
                if ($scan_data['data']['attributes']['status'] !== 'completed') {
                    sleep(2);
                    $analysis_response = Http::get('https://www.virustotal.com/api/v3/analyses/' . $analysis_id, [
                        'headers' => ['x-apikey' => $scan_api]
                    ]);
                    $scan_data = $analysis_response->json();
                    if ($cve_response->successful()) {
                        $data = $cve_response->json();

                        if (isset($data['vulnerabilities'])) {
                            $vulnerabilities = array_reverse($data['vulnerabilities']);
                            $data['vulnerabilities'] = array_slice($vulnerabilities, 0, 30);
                        }

                    }
                }
            } else {
                $scan_error = 'VirusTotal Detail Error: ' . $analysis_response->body();
            }
        } else {
            $scan_error = 'VirusTotal Submit Error: ' . $submit_response->body();
        }

        return view('index.dump', [
            'ipgeo_result' => $ip_response->successful() ? $ip_response->json() : null,
            'ipgeo_error' => !$ip_response->successful() ? $ip_response->body() : null,
            'scan_result' => $scan_data,
            'scan_error' => $scan_error,
            'scanned_url' => $url_to_scan,
            'scanned_ip' => $ip_address,
            'cve_result' => array_slice(array_reverse($cve_response->json()['vulnerabilities']), 0, 30),
            'cve_error' => !$cve_response->successful() ? $cve_response->body() : null
        ]);
    }
}