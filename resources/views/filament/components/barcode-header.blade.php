<div class="mini-barcode-wrapper flex items-center justify-end gap-2 mb-4">
    {{-- <span class="text-xs text-gray-600">الدفع الإلكتروني:</span> --}}
    <div class="barcode-icon-container relative group">
        @if(file_exists(public_path('images/my_barcode.jpg')))
            <img src="{{ asset('images/my_barcode.jpg') }}"
                 alt="باركود الدفع"
                 class="h-6 w-6 object-cover rounded border border-gray-300 cursor-pointer">
        @else
            <div class="h-6 w-6 bg-gray-100 border border-gray-300 rounded flex items-center justify-center">
                <div class="flex gap-0.5">
                    @for($i = 0; $i < 3; $i++)
                        <div class="w-0.5 bg-gray-600" style="height: {{ rand(8, 12) }}px"></div>
                    @endfor
                </div>
            </div>
        @endif

</div>
