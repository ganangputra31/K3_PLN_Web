@extends('layouts.public')
@section('title', 'Denah Lokasi')

@section('content')
@include('public._pagehead', [
    'eyebrow' => 'Layout & Jalur Evakuasi',
    'title' => 'Denah Lokasi',
    'subtitle' => 'Tata letak fasilitas, titik APAR, kotak P3K, jalur evakuasi, dan titik kumpul.',
])

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card-k3 p-3">
                    {{-- Ganti src dengan gambar denah dari asset lokal atau Supabase Storage --}}
                    @php $denah = asset('assets/denah.png'); @endphp
                    <div class="ratio ratio-16x9 bg-light rounded d-flex align-items-center justify-content-center">
                        <img src="{{ $denah }}" alt="Denah Lokasi PLN"
                             class="w-100 h-100 rounded" style="object-fit: contain;"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="flex-column align-items-center justify-content-center text-muted" style="display:none;">
                            <i class="bi bi-map fs-1"></i>
                            <p class="small mt-2 mb-0">Unggah gambar denah ke <code>public/assets/denah.png</code><br>atau ke Supabase Storage.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card-k3 p-4 h-100">
                    <h6 class="fw-bold mb-3">Legenda</h6>
                    @foreach ($legend as $l)
                        <div class="d-flex align-items-center mb-2">
                            <span class="card-icon me-3" style="width:38px;height:38px;font-size:1rem;"><i class="bi {{ $l['icon'] }}"></i></span>
                            <span class="small">{{ $l['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
