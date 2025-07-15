public function generateDailyClosing(Request $request)
{
    $user = Session::get('SESS_USER_NAME')['user'];
    $token = $user['Token'];
    $headers = ["Authorization: " . $token];

    $branchId = $request->get('OurBranchID');
    $today = $request->get('TanggalHariIni');
    $closingDate = str_replace("-", "", $request->get('TanggalClosing'));

    $folderPath = "./images/Download/FileDailyClosing/$branchId/$today/";
    if (!is_dir($folderPath)) mkdir($folderPath, 0777, true);

    $files = ['LHTK', 'LPUH', 'LDCK', 'LRP', 'LPM'];
    $url = ConstantUrl::getURL(ConstantUrl::API_BACKEND_EXISTING);
    $basicAuth = base64_encode('test123');

    foreach ($files as $filename) {
        $pdfPath = "{$folderPath}{$filename}-{$branchId}-{$closingDate}.pdf";

        $response = $this->fetchPDF($url, $headers);
        if (!$response) {
            $response = $this->fetchPDF('APIGeneratePDF', ["Authorization: Basic $basicAuth"]);
        }

        if ($response) file_put_contents($pdfPath, $response);
    }

    $zipPath = "./images/zip/FileDailyClosing_{$branchId}_{$today}_DailyClosing.rar";
    $this->zipFolder($folderPath, $zipPath);

    if (env("APP_ENV") == "production") {
        $uploadResult = $this->uploadZipToAPI($zipPath, $headers, $url);

        // Cleanup
        unlink($zipPath);
        array_map('unlink', glob("$folderPath/*"));
        rmdir($folderPath);

        return response()->json($uploadResult);
    }

    return response()->json([
        "code" => 200,
        "message" => "Data berhasil disimpan dengan nomor ticket XXXXXXXXX"
    ]);
}

private function fetchPDF($url, $headers)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => 2
    ]);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

private function zipFolder($folderPath, $zipPath)
{
    $zip = new \ZipArchive;
    if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folderPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folderPath));
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }
}

private function uploadZipToAPI($zipPath, $headers, $url)
{
    $file = curl_file_create($zipPath);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['file' => $file],
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}
