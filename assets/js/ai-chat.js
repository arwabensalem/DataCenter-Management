/**
 * GreenDC AI Advisor — chatbot frontend
 */
(function () {
    'use strict';

    const cfg = window.GDC_AI;
    if (!cfg) return;

    const messagesEl = document.getElementById('ai-messages');
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-question');
    const statusEl = document.getElementById('ai-status');
    const dcSelect = document.getElementById('ai-dc-select');
    const clearBtn = document.getElementById('ai-clear-history');

    if (!form || !messagesEl || !input) return;

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = str == null ? '' : String(str);
        return d.innerHTML;
    }

    /** Markdown léger → HTML (échappé d'abord). */
    function formatAnswer(text) {
        let s = esc(text || '');
        // Blocs de code
        s = s.replace(/```[\s\S]*?```/g, function (block) {
            const inner = block.replace(/^```\w*\n?/, '').replace(/```$/, '');
            return '<pre class="ai-code"><code>' + inner.trim() + '</code></pre>';
        });
        // Titres
        s = s.replace(/^######\s+(.+)$/gm, '<h6 class="ai-h">$1</h6>');
        s = s.replace(/^#####\s+(.+)$/gm, '<h6 class="ai-h">$1</h6>');
        s = s.replace(/^####\s+(.+)$/gm, '<h6 class="ai-h">$1</h6>');
        s = s.replace(/^###\s+(.+)$/gm, '<h5 class="ai-h">$1</h5>');
        s = s.replace(/^##\s+(.+)$/gm, '<h5 class="ai-h">$1</h5>');
        s = s.replace(/^#\s+(.+)$/gm, '<h5 class="ai-h">$1</h5>');
        // Gras / italique
        s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        s = s.replace(/__(.+?)__/g, '<strong>$1</strong>');
        s = s.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
        // Listes
        s = s.replace(/^\s*[-•]\s+(.+)$/gm, '<li>$1</li>');
        s = s.replace(/^\s*\d+\.\s+(.+)$/gm, '<li>$1</li>');
        s = s.replace(/(?:<li>[\s\S]*?<\/li>\s*)+/g, function (block) {
            return '<ul class="ai-list mb-2">' + block.replace(/\n/g, '') + '</ul>';
        });
        // Citations
        s = s.replace(/^&gt;\s?(.+)$/gm, '<blockquote class="ai-quote">$1</blockquote>');
        // Paragraphes / sauts de ligne
        s = s.replace(/\n{2,}/g, '</p><p class="ai-p">');
        s = s.replace(/\n/g, '<br>');
        if (!/^<[hul]/.test(s.trim())) {
            s = '<p class="ai-p">' + s + '</p>';
        }
        return s;
    }

    function appendMessage(role, content, meta) {
        const wrap = document.createElement('div');
        wrap.className = 'ai-msg ' + (role === 'user' ? 'ai-msg-user' : 'ai-msg-bot');
        let sourcesHtml = '';
        if (meta && meta.sources && meta.sources.length) {
            sourcesHtml = '<div class="ai-sources-inline mt-2"><div class="small text-muted fw-semibold mb-1">Références</div><ul class="small mb-0">';
            meta.sources.forEach(function (s) {
                const title = s.title || s.file || 'Document';
                const page = s.page ? ' — p.' + s.page : '';
                sourcesHtml += '<li>' + esc(title) + page + '</li>';
            });
            sourcesHtml += '</ul></div>';
        }
        const body = role === 'user' ? esc(content) : formatAnswer(content);
        wrap.innerHTML = '<div class="ai-msg-bubble">' + body + sourcesHtml + '</div>';
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function setLoading(on) {
        const btn = document.getElementById('ai-send');
        if (btn) btn.disabled = on;
        input.disabled = on;
        statusEl.textContent = on ? 'Analyse en cours…' : '';
    }

    async function sendQuestion(question) {
        const q = (question || '').trim();
        if (q.length < 3) return;

        const dcId = dcSelect ? parseInt(dcSelect.value, 10) : 0;
        appendMessage('user', q);
        input.value = '';
        setLoading(true);

        try {
            const res = await fetch(cfg.chatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    _csrf: cfg.csrf,
                    question: q,
                    data_center_id: dcId,
                }),
            });
            const data = await res.json();
            if (!res.ok) {
                appendMessage('bot', data.error || 'Erreur serveur');
                return;
            }
            appendMessage('bot', data.answer || '', {
                sources: data.sources || [],
            });
        } catch (e) {
            appendMessage(
                'bot',
                'Le service d\'analyse est temporairement indisponible. Réessayez dans un instant.'
            );
        } finally {
            setLoading(false);
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        sendQuestion(input.value);
    });

    document.querySelectorAll('.ai-suggest').forEach(function (btn) {
        btn.addEventListener('click', function () {
            sendQuestion(btn.textContent);
        });
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', async function () {
            const dcId = dcSelect ? parseInt(dcSelect.value, 10) : 0;
            try {
                await fetch(cfg.clearUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ _csrf: cfg.csrf, data_center_id: dcId }),
                });
                messagesEl.innerHTML =
                    '<div class="ai-msg ai-msg-bot"><div class="ai-msg-bubble">' +
                    'Historique effacé. Posez une nouvelle question.' +
                    '</div></div>';
            } catch (e) {
                /* ignore */
            }
        });
    }
})();
