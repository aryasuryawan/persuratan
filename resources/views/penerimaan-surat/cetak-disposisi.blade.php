@php
    $appName = \App\Models\Setting::get('app_name', 'Aplikasi Surat');
    $lembaga = \App\Models\Setting::get('lembaga_nama', '');
    $alamat = \App\Models\Setting::get('lembaga_alamat', '');
    $telepon = \App\Models\Setting::get('lembaga_telepon', '');
    $email = \App\Models\Setting::get('lembaga_email', '');
    $website = \App\Models\Setting::get('lembaga_website', '');
    $logo = \App\Models\Setting::get('app_logo');
    $disposisiCollection = $surat->disposisi;
    $disposisi = $disposisiCollection->first();

    $instruksiDb = $disposisi->instruksi ?? [];
    $instruksiText = implode(', ', $instruksiDb);

    // Pelaksana (dari relasi ke_pelaksana)
    $pelaksanaText = '';
    if ($disposisi->kepadaUser) {
        $pelaksanaText = $disposisi->kepadaUser->name;
        if ($disposisi->kepadaUser->jabatan) {
            $pelaksanaText .= ' (' . $disposisi->kepadaUser->jabatan . ')';
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Disposisi - {{ $surat->nomor_agenda }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .form-box { border-color: black !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 p-4 md:p-8 text-black font-sans">

    <div class="max-w-4xl mx-auto bg-white p-6 border-2 border-black shadow-md form-box">

        <div class="relative mb-6 text-center">
            <h1 class="text-xl md:text-2xl font-bold uppercase underline tracking-wide">
                LEMBAR DISPOSISI KETUA PENGURUS YDDR
            </h1>

            <div class="absolute right-0 top-0 hidden sm:flex items-center space-x-1">
                <div class="text-right">
                    <span class="block text-[10px] font-black text-green-700 leading-none">DOMPET</span>
                    <span class="block text-[10px] font-black text-green-700 leading-none">DHUAFA</span>
                </div>
                <div class="w-6 h-6 bg-green-600 rounded-bl-full flex items-center justify-center">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                </div>
            </div>
        </div>

        <div class="space-y-2 mb-6 text-sm md:text-base">

            <div class="flex flex-col md:flex-row md:items-center">
                <label class="w-28 font-medium">Nomor</label>
                <span class="hidden md:inline w-4">:</span>
                <div class="flex-grow flex flex-col md:flex-row md:items-center gap-2">
                    <input type="text" value="{{ $surat->nomor_agenda }}" readonly class="flex-grow border border-black p-1.5 bg-gray-50">
                    <span class="font-medium whitespace-nowrap md:pl-4">Diterima tanggal :</span>
                    <input type="text" value="{{ $surat->tanggal_diterima->format('d/m/Y') }}" readonly class="w-full md:w-48 border border-black p-1.5 bg-gray-50">
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center">
                <label class="w-28 font-medium">Surat dari</label>
                <span class="hidden md:inline w-4">:</span>
                <div class="flex-grow">
                    <input type="text" value="{{ $surat->pengirim }}" readonly class="w-full border border-black p-1.5 bg-gray-50">
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center">
                <label class="w-28 font-medium">Isi surat</label>
                <span class="hidden md:inline w-4">:</span>
                <div class="flex-grow">
                    <input type="text" value="{{ $surat->perihal }}" readonly class="w-full border border-black p-1.5 bg-gray-50">
                </div>
            </div>

        </div>

        <div class="space-y-3 mb-6 text-sm md:text-base">

            <div class="flex flex-col md:flex-row md:items-center">
                <label class="w-28 font-medium">Instruksi</label>
                <span class="hidden md:inline w-4">:</span>
                <div class="flex-grow font-medium">{{ $instruksiText ?: '-' }}</div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center">
                <label class="w-28 font-medium">Pelaksana</label>
                <span class="hidden md:inline w-4">:</span>
                <div class="flex-grow font-medium">{{ $pelaksanaText ?: '-' }}</div>
            </div>

        </div>

        <div class="border border-black p-4 mb-4">
            <div class="text-center font-bold underline mb-2">MEMO</div>
            <div class="mb-3">
                <strong>Batas waktu:</strong> {{ $disposisi->tanggal_deadline ? $disposisi->tanggal_deadline->format('d/m/Y') : '-' }}
            </div>
            <textarea rows="5" class="w-full border-none focus:outline-none resize-none bg-transparent" readonly>{{ $disposisi->catatan ?? '' }}</textarea>
        </div>

        <div class="border border-black p-3 text-center italic text-xs md:text-sm">
            *Note : pihak yang hadir mohon dapat mengirimkan resume hasil pertemuannya ke email 
            <a href="mailto:corporatesecretary@dompetdhuafa.org" class="underline text-blue-700">corporatesecretary@dompetdhuafa.org</a>, 
            <a href="mailto:secretary@dompetdhuafa.org" class="underline text-blue-700">secretary@dompetdhuafa.org</a> 
            (paling lambat H+3 setelah kegiatan)
        </div>

    </div>

</body>
</html>
