@if(\App\Models\Setting::get('ai_enabled') === '1' && auth()->check() && auth()->user()->hasPermission('ai-assistant.use'))
<div id="ai-assistant-widget" data-ask-url="{{ route('api.ai.ask') }}">
    <button type="button" id="ai-widget-toggle" class="ai-widget-toggle" aria-label="AI Assistant">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
        <span class="ai-widget-status-dot"></span>
    </button>

    <div id="ai-widget-panel" class="ai-widget-panel" hidden>
        <div class="ai-widget-header">
            <div>
                <strong>AI Assistant</strong>
                <small>SiMPEL — bantu cari data surat</small>
            </div>
            <button type="button" id="ai-widget-close" class="btn btn-sm btn-ghost" aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <div id="ai-widget-messages" class="ai-widget-messages">
            <div class="ai-msg ai-msg-bot">
                Assalamu&rsquo;alaikum! Saya Suri, asisten eSurat Anda. Mau cari surat masuk, SK, atau notulensi rapim? Langsung tanya saja, ya!
            </div>
        </div>

        <div id="ai-widget-links" class="ai-widget-links" hidden></div>

        <form id="ai-widget-form" class="ai-widget-form">
            <input type="text" id="ai-widget-input" class="form-control" placeholder="Tanyakan soal data surat..." autocomplete="off" maxlength="500">
            <button type="submit" class="btn btn-primary" id="ai-widget-send" aria-label="Kirim">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            </button>
        </form>
    </div>
</div>

<style>
#ai-assistant-widget {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 1050;
    font-family: var(--tblr-font-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif);
}
.ai-widget-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: none;
    background: var(--tblr-primary, #206bc4);
    color: #fff;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}
.ai-widget-toggle svg { width: 24px; height: 24px; }
.ai-widget-status-dot {
    position: absolute;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #2fb344;
    border: 2px solid #fff;
    bottom: 2px;
    right: 2px;
}
.ai-widget-panel {
    position: fixed;
    right: 20px;
    bottom: 88px;
    width: 360px;
    max-width: calc(100vw - 40px);
    background: var(--tblr-card-bg, #fff);
    border: 1px solid var(--tblr-border-color, #e6e7e9);
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    color: var(--tblr-body-color, #1d273b);
}
.ai-widget-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid var(--tblr-border-color, #e6e7e9);
    background: var(--tblr-primary, #206bc4);
    color: #fff;
}
.ai-widget-header small { display: block; opacity: 0.85; }
.ai-widget-header .btn-ghost { color: #fff; }
.ai-widget-messages {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    max-height: 320px;
    min-height: 120px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.ai-msg {
    max-width: 85%;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.45;
    white-space: pre-wrap;
    word-break: break-word;
}
.ai-msg-bot {
    align-self: flex-start;
    background: var(--tblr-bg-surface-secondary, #f1f4f8);
    color: inherit;
    border-bottom-left-radius: 2px;
}
.ai-msg-user {
    align-self: flex-end;
    background: var(--tblr-primary, #206bc4);
    color: #fff;
    border-bottom-right-radius: 2px;
}
.ai-msg-error {
    align-self: flex-start;
    background: var(--tblr-danger-lt, #fbe4e6);
    color: var(--tblr-danger, #d63939);
}
.ai-msg-typing::after {
    content: "";
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    animation: ai-blink 1s infinite;
}
@keyframes ai-blink {
    0%, 60%, 100% { opacity: 0.2; }
    30% { opacity: 1; }
}
.ai-widget-links {
    padding: 4px 12px 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    border-top: 1px dashed var(--tblr-border-color, #e6e7e9);
}
.ai-link-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border: 1px solid var(--tblr-border-color, #e6e7e9);
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    background: var(--tblr-bg-surface-secondary, #f8fafc);
}
.ai-link-card:hover { border-color: var(--tblr-primary, #206bc4); }
.ai-link-card .ai-link-modul {
    flex-shrink: 0;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    background: var(--tblr-primary-lt, #e4effb);
    color: var(--tblr-primary, #206bc4);
}
.ai-link-card .ai-link-text { overflow: hidden; }
.ai-link-card .ai-link-title {
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ai-link-card .ai-link-sub {
    font-size: 11px;
    color: var(--tblr-secondary, #667382);
}
.ai-widget-form {
    display: flex;
    gap: 8px;
    padding: 10px 12px;
    border-top: 1px solid var(--tblr-border-color, #e6e7e9);
}
.ai-widget-form .form-control { border-radius: 8px; }
</style>

<script>
(function () {
    const root = document.getElementById('ai-assistant-widget');
    if (!root) return;

    const toggle = document.getElementById('ai-widget-toggle');
    const panel = document.getElementById('ai-widget-panel');
    const closeBtn = document.getElementById('ai-widget-close');
    const messagesEl = document.getElementById('ai-widget-messages');
    const linksEl = document.getElementById('ai-widget-links');
    const form = document.getElementById('ai-widget-form');
    const input = document.getElementById('ai-widget-input');
    const sendBtn = document.getElementById('ai-widget-send');
    const askUrl = root.dataset.askUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]');
    const history = [];

    const MODUL_LABELS = {
        surat_masuk: 'Surat Masuk',
        surat_keluar: 'Surat Keluar',
        surat_keputusan: 'SK',
        komite_persetujuan: 'Komite',
        notulen_rapim: 'Notulensi',
    };

    function addMsg(text, cls) {
        const div = document.createElement('div');
        div.className = 'ai-msg ' + (cls || 'ai-msg-bot');
        div.textContent = text;
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        return div;
    }

    function renderLinks(links) {
        linksEl.innerHTML = '';
        if (!links || !links.length) {
            linksEl.hidden = true;
            return;
        }
        linksEl.hidden = false;
        links.forEach(function (link) {
            const a = document.createElement('a');
            a.className = 'ai-link-card';
            a.href = link.url;
            a.target = '_self';
            const modul = document.createElement('span');
            modul.className = 'ai-link-modul';
            modul.textContent = MODUL_LABELS[link.modul] || link.modul;
            const text = document.createElement('span');
            text.className = 'ai-link-text';
            const title = document.createElement('div');
            title.className = 'ai-link-title';
            title.textContent = link.label;
            const sub = document.createElement('div');
            sub.className = 'ai-link-sub';
            sub.textContent = (link.tanggal ? link.tanggal + ' — ' : '') + (link.judul || '');
            text.appendChild(title);
            text.appendChild(sub);
            a.appendChild(modul);
            a.appendChild(text);
            linksEl.appendChild(a);
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const question = input.value.trim();
        if (!question) return;

        input.value = '';
        addMsg(question, 'ai-msg-user');
        const typing = addMsg('Mengetik', 'ai-msg-typing');
        renderLinks([]);
        sendBtn.disabled = true;

        const body = { question: question };
        if (history.length) body.history = history.slice(-6);

        fetch(askUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
            },
            body: JSON.stringify(body),
        })
            .then(function (r) {
                return r.json().catch(function () {
                    return { message: 'Terjadi kesalahan (kode ' + r.status + ').' };
                });
            })
            .then(function (data) {
                typing.remove();
                if (data.answer) {
                    addMsg(data.answer);
                    renderLinks(data.links || []);
                    history.push({ role: 'user', content: question });
                    history.push({ role: 'assistant', content: data.answer });
                } else {
                    addMsg(data.message || 'Terjadi kesalahan. Silakan coba lagi.', 'ai-msg-error');
                }
            })
            .catch(function () {
                typing.remove();
                addMsg('Terjadi kesalahan jaringan. Silakan coba lagi.', 'ai-msg-error');
            })
            .finally(function () {
                sendBtn.disabled = false;
                input.focus();
            });
    });

    toggle.addEventListener('click', function () {
        const hidden = panel.hidden;
        panel.hidden = !hidden;
        if (hidden) input.focus();
    });
    closeBtn.addEventListener('click', function () {
        panel.hidden = true;
    });
})();
</script>
@endif
