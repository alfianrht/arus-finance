<?php
$file = 'app/Controllers/Arus.php';
$content = file_get_contents($file);

// Find public function rekap() and remove it
$content = preg_replace('/[ \t]*public function rekap\(\): string\s*\{.*?\}\s*(?=public function |private function |protected function |$)/s', '', $content);

// Find public function rekening() and remove it
$content = preg_replace('/[ \t]*public function rekening\(string \$slug\): string\s*\{.*?\}\s*(?=public function |private function |protected function |$)/s', '', $content);

// Find public function unit() and remove it
$content = preg_replace('/[ \t]*public function unit\(string \$slug\): string\s*\{.*?\}\s*(?=public function |private function |protected function |$)/s', '', $content);

// Find public function kegiatan() and remove it
$content = preg_replace('/[ \t]*public function kegiatan\(string \$slug\): string\s*\{.*?\}\s*(?=public function |private function |protected function |$)/s', '', $content);

file_put_contents($file, $content);
echo "Cleaned Arus.php successfully.\n";
