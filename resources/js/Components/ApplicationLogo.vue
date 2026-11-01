<template>
    <!-- <svg viewBox="0 0 316 316" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M305.8 81.125C305.77 80.995 305.69 80.885 305.65 80.755C305.56 80.525 305.49 80.285 305.37 80.075C305.29 79.935 305.17 79.815 305.07 79.685C304.94 79.515 304.83 79.325 304.68 79.175C304.55 79.045 304.39 78.955 304.25 78.845C304.09 78.715 303.95 78.575 303.77 78.475L251.32 48.275C249.97 47.495 248.31 47.495 246.96 48.275L194.51 78.475C194.33 78.575 194.19 78.725 194.03 78.845C193.89 78.955 193.73 79.045 193.6 79.175C193.45 79.325 193.34 79.515 193.21 79.685C193.11 79.815 192.99 79.935 192.91 80.075C192.79 80.285 192.71 80.525 192.63 80.755C192.58 80.875 192.51 80.995 192.48 81.125C192.38 81.495 192.33 81.875 192.33 82.265V139.625L148.62 164.795V52.575C148.62 52.185 148.57 51.805 148.47 51.435C148.44 51.305 148.36 51.195 148.32 51.065C148.23 50.835 148.16 50.595 148.04 50.385C147.96 50.245 147.84 50.125 147.74 49.995C147.61 49.825 147.5 49.635 147.35 49.485C147.22 49.355 147.06 49.265 146.92 49.155C146.76 49.025 146.62 48.885 146.44 48.785L93.99 18.585C92.64 17.805 90.98 17.805 89.63 18.585L37.18 48.785C37 48.885 36.86 49.035 36.7 49.155C36.56 49.265 36.4 49.355 36.27 49.485C36.12 49.635 36.01 49.825 35.88 49.995C35.78 50.125 35.66 50.245 35.58 50.385C35.46 50.595 35.38 50.835 35.3 51.065C35.25 51.185 35.18 51.305 35.15 51.435C35.05 51.805 35 52.185 35 52.575V232.235C35 233.795 35.84 235.245 37.19 236.025L142.1 296.425C142.33 296.555 142.58 296.635 142.82 296.725C142.93 296.765 143.04 296.835 143.16 296.865C143.53 296.965 143.9 297.015 144.28 297.015C144.66 297.015 145.03 296.965 145.4 296.865C145.5 296.835 145.59 296.775 145.69 296.745C145.95 296.655 146.21 296.565 146.45 296.435L251.36 236.035C252.72 235.255 253.55 233.815 253.55 232.245V174.885L303.81 145.945C305.17 145.165 306 143.725 306 142.155V82.265C305.95 81.875 305.89 81.495 305.8 81.125ZM144.2 227.205L100.57 202.515L146.39 176.135L196.66 147.195L240.33 172.335L208.29 190.625L144.2 227.205ZM244.75 114.995V164.795L226.39 154.225L201.03 139.625V89.825L219.39 100.395L244.75 114.995ZM249.12 57.105L292.81 82.265L249.12 107.425L205.43 82.265L249.12 57.105ZM114.49 184.425L96.13 194.995V85.305L121.49 70.705L139.85 60.135V169.815L114.49 184.425ZM91.76 27.425L135.45 52.585L91.76 77.745L48.07 52.585L91.76 27.425ZM43.67 60.135L62.03 70.705L87.39 85.305V202.545V202.555V202.565C87.39 202.735 87.44 202.895 87.46 203.055C87.49 203.265 87.49 203.485 87.55 203.695V203.705C87.6 203.875 87.69 204.035 87.76 204.195C87.84 204.375 87.89 204.575 87.99 204.745C87.99 204.745 87.99 204.755 88 204.755C88.09 204.905 88.22 205.035 88.33 205.175C88.45 205.335 88.55 205.495 88.69 205.635L88.7 205.645C88.82 205.765 88.98 205.855 89.12 205.965C89.28 206.085 89.42 206.225 89.59 206.325C89.6 206.325 89.6 206.325 89.61 206.335C89.62 206.335 89.62 206.345 89.63 206.345L139.87 234.775V285.065L43.67 229.705V60.135ZM244.75 229.705L148.58 285.075V234.775L219.8 194.115L244.75 179.875V229.705ZM297.2 139.625L253.49 164.795V114.995L278.85 100.395L297.21 89.825V139.625H297.2Z"
        />
    </svg> -->

<svg xmlns="http://www.w3.org/2000/svg" 
viewBox="0 0 480 480" 
width="80" 
height="80">
  <defs>
    <linearGradient id="vNeon" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="#6FD0FF"/>
      <stop offset="100%" stop-color="#2F6BFF"/>
    </linearGradient>
    <linearGradient id="vGreen" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="#7DFF9E"/>
      <stop offset="100%" stop-color="#00E676"/>
    </linearGradient>

    <!-- Dark glass fills -->
    <linearGradient id="glassTop" x1="0" y1="1" x2="0" y2="0">
      <stop offset="0%" stop-color="#030303"/>
      <stop offset="60%" stop-color="#0A0A0A"/>
      <stop offset="100%" stop-color="#181818"/>
    </linearGradient>
    <linearGradient id="glassLeft" x1="0" y1="1" x2="0" y2="0">
      <stop offset="0%" stop-color="#020202"/>
      <stop offset="65%" stop-color="#070707"/>
      <stop offset="100%" stop-color="#121212"/>
    </linearGradient>
    <linearGradient id="glassRight" x1="0" y1="1" x2="0" y2="0">
      <stop offset="0%" stop-color="#040404"/>
      <stop offset="65%" stop-color="#0B0B0B"/>
      <stop offset="100%" stop-color="#171717"/>
    </linearGradient>

    <!-- Green specular streak -->
    <linearGradient id="specular" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#12963E" stop-opacity="0"/>
      <stop offset="42%" stop-color="#12963E" stop-opacity="0.04"/>
      <stop offset="50%" stop-color="#12963E" stop-opacity="0.18"/>
      <stop offset="58%" stop-color="#12963E" stop-opacity="0.04"/>
      <stop offset="100%" stop-color="#12963E" stop-opacity="0"/>
    </linearGradient>

    <filter id="emboss" x="-30%" y="-30%" width="160%" height="160%">
      <feGaussianBlur stdDeviation="0.6" result="soft"/>
      <feMerge>
        <feMergeNode in="soft"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>
  <g transform="translate(240,370) scale(1.7)">
    <!-- ===== PARENT CONTAINER — BLACK BACKGROUND ===== -->
    <polygon points="0,-193.9 48,-166.2 48,-110.8 96,-83.1 96,-27.7 48,0 0,27.7 -48,0 -96,-27.7 -96,-83.1 -48,-110.8 -48,-166.2"
             fill="#000000"/>

    <!-- ===== BOTTOM CUBE — DARK GLASS + BOLDER DOTTED GREEN OUTLINE ===== -->
    <polygon points="0,-27.7 48,-55.4 0,-83.1 -48,-55.4" fill="url(#glassTop)" stroke="#39FF6A" stroke-width="2" stroke-dasharray="4 3" stroke-linejoin="round"/>
    <polygon points="-48,-55.4 0,-27.7 0,27.7 -48,0" fill="url(#glassLeft)" stroke="#39FF6A" stroke-width="2" stroke-dasharray="4 3" stroke-linejoin="round"/>
    <polygon points="48,-55.4 0,-27.7 0,27.7 48,0" fill="url(#glassRight)" stroke="#39FF6A" stroke-width="2" stroke-dasharray="4 3" stroke-linejoin="round"/>

    <!-- ===== MIDDLE CUBE — DARK GLASS + BOLDER DOTTED GREEN OUTLINE ===== -->
    <polygon points="0,-83.1 48,-110.8 0,-138.5 -48,-110.8" fill="url(#glassTop)" stroke="#39FF6A" stroke-width="2" stroke-dasharray="4 3" stroke-linejoin="round"/>
    <polygon points="-48,-110.8 0,-83.1 0,-27.7 -48,-55.4" fill="url(#glassLeft)" stroke="#39FF6A" stroke-width="2" stroke-dasharray="4 3" stroke-linejoin="round"/>
    <polygon points="48,-110.8 0,-83.1 0,-27.7 48,-55.4" fill="url(#glassRight)" stroke="#39FF6A" stroke-width="2" stroke-dasharray="4 3" stroke-linejoin="round"/>

    <!-- ===== GREEN SPECULAR SHEEN OVERLAYS ===== -->
    <polygon points="0,-27.7 48,-55.4 0,-83.1 -48,-55.4" fill="url(#specular)"/>
    <polygon points="-48,-55.4 0,-27.7 0,27.7 -48,0" fill="url(#specular)"/>
    <polygon points="48,-55.4 0,-27.7 0,27.7 48,0" fill="url(#specular)"/>
    <polygon points="0,-83.1 48,-110.8 0,-138.5 -48,-110.8" fill="url(#specular)"/>
    <polygon points="-48,-110.8 0,-83.1 0,-27.7 -48,-55.4" fill="url(#specular)"/>
    <polygon points="48,-110.8 0,-83.1 0,-27.7 48,-55.4" fill="url(#specular)"/>

    <!-- ===== NEON BLUE RING ON TOP FACE OF MIDDLE CUBE ===== -->
    <g>
      <path d="M -48,-110.8 L 0,-138.5 L 48,-110.8 L 0,-83.1 Z
               M -46,-111.2 L 0,-136.1 L 46,-111.2 L 0,-85.5 Z"
            fill="#2F6BFF" fill-rule="evenodd" opacity="0.95"/>
      <path d="M -48,-112.8 L 0,-140.5 L 48,-112.8 L 0,-85.1 Z
               M -46,-113.2 L 0,-138.1 L 46,-113.2 L 0,-87.5 Z"
            fill="url(#vNeon)" fill-rule="evenodd"/>
      <polygon points="-48,-112.8 0,-140.5 48,-112.8 0,-85.1"
               fill="none" stroke="#B8F2FF" stroke-width="0.8" stroke-linejoin="round" opacity="0.55"/>
    </g>

    <!-- ===== TOP CUBE — NEON GREEN ===== -->
    <g>
      <polygon points="0,-138.5 48,-166.2 0,-193.9 -48,-166.2" fill="#00E676"/>
      <polygon points="-48,-166.2 0,-138.5 0,-83.1 -48,-110.8" fill="#007A3D"/>
      <polygon points="48,-166.2 0,-138.5 0,-83.1 48,-110.8" fill="#00B85C"/>
    </g>

    <!-- ===== "S" AND "T" — LIGHT BRIGHT GREEN ===== -->
    <text x="-24" y="-118" font-family="Montserrat, Arial, sans-serif" font-size="26"
          font-weight="800" fill="#CCFFDD" fill-opacity="1" text-anchor="middle"
          filter="url(#emboss)"
          transform="translate(-24,-124.6) skewY(30) translate(24,124.6)">S</text>
    <text x="24" y="-118" font-family="Montserrat, Arial, sans-serif" font-size="26"
          font-weight="800" fill="#CCFFDD" fill-opacity="1" text-anchor="middle"
          filter="url(#emboss)"
          transform="translate(24,-124.6) skewY(-30) translate(-24,124.6)">T</text>

    <!-- ===== TOP RING — NEON GREEN ===== -->
    <g>
      <path d="M -48,-166.2 L 0,-193.9 L 48,-166.2 L 0,-138.5 Z
               M -46,-166.6 L 0,-191.5 L 46,-166.6 L 0,-140.9 Z"
            fill="#39FF6A" fill-rule="evenodd" opacity="0.95"/>
      <path d="M -48,-168.2 L 0,-195.9 L 48,-168.2 L 0,-140.5 Z
               M -46,-168.6 L 0,-193.5 L 46,-168.6 L 0,-142.9 Z"
            fill="url(#vGreen)" fill-rule="evenodd"/>
      <polygon points="-48,-168.2 0,-195.9 48,-168.2 0,-140.5"
               fill="none" stroke="#B8FFCC" stroke-width="0.8" stroke-linejoin="round" opacity="0.5"/>
    </g>

    <!-- ===== TOP DIVIDER LINE — NEON GREEN ===== -->
    <g>
      <polygon points="-48,-110.8 0,-83.1 48,-110.8 48,-108.8 0,-81.1 -48,-108.8" fill="#39FF6A" opacity="0.95"/>
      <polygon points="-48,-112.8 0,-85.1 48,-112.8 48,-110.8 0,-83.1 -48,-110.8" fill="url(#vGreen)"/>
      <polyline points="-48,-112.8 0,-85.1 48,-112.8"
                fill="none" stroke="#B8FFCC" stroke-width="0.8" stroke-linejoin="round" opacity="0.5"/>
    </g>

    <!-- ===== OUTER OUTLINE — NEON GREEN ===== -->
    <g>
      <polygon points="0,-193.9 48,-166.2 48,-110.8 96,-83.1 96,-27.7 48,0 0,27.7 -48,0 -96,-27.7 -96,-83.1 -48,-110.8 -48,-166.2"
               fill="none" stroke="#39FF6A" stroke-width="5.5" stroke-linejoin="round" stroke-linecap="round"/>
      <polygon points="0,-193.9 48,-166.2 48,-110.8 96,-83.1 96,-27.7 48,0 0,27.7 -48,0 -96,-27.7 -96,-83.1 -48,-110.8 -48,-166.2"
               fill="none" stroke="#B8FFCC" stroke-width="1.3" stroke-linejoin="round" stroke-linecap="round" opacity="0.5"/>
    </g>
  </g>
</svg>
</template>
