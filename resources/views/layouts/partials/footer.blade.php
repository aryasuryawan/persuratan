<footer class="footer footer-transparent d-print-none">
    <div class="container-xl">
        <div class="row text-center align-items-center flex-row-reverse">
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                @php
                    $footerLinks = \App\Models\Setting::getArray('footer_links', []);
                @endphp
                @if(!empty($footerLinks))
                <ul class="list-inline list-inline-dots mb-0">
                    @foreach($footerLinks as $link)
                    <li class="list-inline-item">
                        <a href="{{ $link['url'] ?? '#' }}" class="link-secondary">{{ $link['label'] ?? '' }}</a>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <p class="text-secondary mb-0">
                    {{ \App\Models\Setting::get('footer_text', 'SiMPEL v1.0.1 — Yayasan Dompet Dhuafa Republika') }}
                </p>
                @if(\App\Models\Setting::get('footer_copyright'))
                <p class="text-secondary mb-0 small">
                    {{ \App\Models\Setting::get('footer_copyright') }}
                </p>
                @endif
            </div>
        </div>
    </div>
</footer>
