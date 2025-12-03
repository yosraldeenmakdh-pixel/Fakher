<div class="space-y-3">
    <!-- خريطة مبسطة باستخدام OpenStreetMap -->
    <div class="border rounded-lg overflow-hidden">
        <iframe
            width="100%"
            height="300"
            frameborder="0"
            scrolling="no"
            marginheight="0"
            marginwidth="0"
            src="https://www.openstreetmap.org/export/embed.html?bbox={{ $lng - 0.01 }}%2C{{ $lat - 0.01 }}%2C{{ $lng + 0.01 }}%2C{{ $lat + 0.01 }}&amp;layer=mapnik&amp;marker={{ $lat }}%2C{{ $lng }}"
            style="border: none;">
        </iframe>
    </div>

    <!-- معلومات الموقع -->
    <div class="flex flex-wrap gap-4 items-center justify-between">
        {{-- <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="text-gray-600 text-sm">الإحداثيات:</span>
                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                    {{ number_format($lat, 6) }}, {{ number_format($lng, 6) }}
                </span>
            </div>
            <div class="text-xs text-gray-500">
                انقر على الخريطة للتكبير
            </div>
        </div> --}}

        <!-- أزرار التنقل -->
        <div class="flex gap-2">
            <a
                href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}"
                target="_blank"
                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-100 text-blue-700 text-sm rounded hover:bg-blue-200"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Google Maps
            </a>

            {{-- <a
                href="https://maps.apple.com/?q={{ $lat }},{{ $lng }}"
                target="_blank"
                class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Apple Maps
            </a> --}}
        </div>
    </div>
</div>
