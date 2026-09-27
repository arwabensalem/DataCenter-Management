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

    function appendMessage(role, htmlContent, meta) {
        const wrap = document.createElement('div');
        wrap.className = 'ai-msg ' + (role === 'user' ? 'ai-msg-user' : 'ai-msg-bot');
        let sourcesHtml = '';
        if (meta && meta.sources && meta.sources.length) {
            sourcesHtml = '<div class="ai-sources-inline mt-2"><strong class="small">Sources</strong><ul class="small mb-0">';
            meta.sources.forEach(function (s) {
                const title = s.title || s.file || 'Document';
                const page = s.page ? ' — p.' + s.page : '';
                sourcesHtml += '<li>📄 ' + esc(title) + page + '</li>';
            });
            sourcesHtml += '</ul></div>';
        }
        let badge = '';
        if (meta && meta.fallback) {
            badge = '<span class="badge bg-warning text-dark ms-1">fallback</span>';
        } else if (meta && meta.llm) {
            badge = '<span class="badge bg-success ms-1">LLM</span>';
        }
        wrap.innerHTML =
            '<div class="ai-msg-bubble">' +
            htmlContent +
            badge +
            sourcesHtml +
            '</div>';
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
        appendMessage('user', esc(q));
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
                appendMessage('bot', esc(data.error || 'Erreur serveur'));
                return;
            }
            const answerHtml = esc(data.answer || '').replace(/\n/g, '<br>');
            appendMessage('bot', answerHtml, {
                sources: data.sources || [],
                fallback: !!data.fallback,
                llm: !!(data.metrics && data.metrics.llm_used),
            });
        } catch (e) {
            appendMessage(
                'bot',
                'Impossible de joindre le service. Affichage possible en mode fallback au prochain essai.'
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
                    '<div class="ai-msg ai-msg-bot"><div class="ai-msg-bubble">Historique effacé. Posez une nouvelle question.</div></div>';
            } catch (e) {
                /* ignore */
            }
        });
    }
})();
