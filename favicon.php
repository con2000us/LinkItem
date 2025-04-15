<?php
// 設置內容類型為SVG
header('Content-Type: image/svg+xml');
header('Cache-Control: max-age=86400');

// SVG圖標內容
echo <<<EOT
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#5a55aa;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#3498db;stop-opacity:1" />
    </linearGradient>
  </defs>
  <circle cx="50" cy="50" r="45" fill="url(#grad)"/>
  <path d="M65,35 A15,15 0 1,1 50,20 L50,30 A5,5 0 1,0 55,35 L45,35 L55,45 L65,35" fill="white"/>
  <path d="M35,65 A15,15 0 1,1 50,80 L50,70 A5,5 0 1,0 45,65 L55,65 L45,55 L35,65" fill="white"/>
</svg>
EOT;
?> 