<?php

declare(strict_types=1);

use App\Helpers\Security;
use App\Helpers\Url;

/** @var list<array<string, mixed>> $dataCenters */
/** @var int $selectedId */
/** @var bool $ragOnline */
/** @var string $csrf */

$baseUrl = Url::base();
?>
<div class="ai-chat-page">
    <div class="welcome-banner p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="h4 mb-1"><i class="fa-solid fa-robot me-2"></i>GreenDC AI Advisor</h2>
                <p class="mb-0 opacity-90">
                    Assistant contextuel — données métier + documents RAG (DOE, ANME, Green Grid…).
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if ($ragOnline && !empty($llmOnline)): ?>
                    <span class="badge bg-success">
                        <i class="fa-solid fa-circle me-1"></i>
                        LLM <?= Security::e((string) ($llmProvider ?: 'ok')) ?>
                        <?php if (!empty($llmModel)): ?>
                            · <?= Security::e((string) $llmModel) ?>
                        <?php endif; ?>
                    </span>
                <?php elseif ($ragOnline): ?>
                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-database me-1"></i> RAG seul (sans LLM)</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> Mode fallback</span>
                <?php endif; ?>
                <?php if ($selectedId > 0): ?>
                    <a href="<?= Security::e(Url::to('ai/analyze/' . $selectedId)) ?>" class="btn btn-light btn-sm">
                        <i class="fa-solid fa-magnifying-glass-chart me-1"></i> Analyse complète
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($dataCenters === []): ?>
        <div class="alert alert-info">Aucun Data Center accessible. Créez-en un pour activer l'assistant.</div>
    <?php else: ?>
    <div class="row g-3">
        <div class="col-lg-3">
            <div class="form-card h-100">
                <label class="form-label small text-muted text-uppercase">Data Center</label>
                <select id="ai-dc-select" class="form-select mb-3">
                    <?php foreach ($dataCenters as $dc): ?>
                        <option value="<?= (int) $dc['id'] ?>" <?= (int) $dc['id'] === $selectedId ? 'selected' : '' ?>>
                            <?= Security::e((string) $dc['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="small text-muted mb-2">Exemples de questions :</p>
                <div class="d-flex flex-column gap-1 ai-suggestions">
                    <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-suggest">Pourquoi mon PUE est élevé ?</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-suggest">Comment réduire le cooling ?</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-suggest">Mon installation PV est-elle suffisante ?</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-suggest">Comment réduire mes émissions CO₂ ?</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary text-start ai-suggest">Quels équipements consomment le plus ?</button>
                </div>
                <button type="button" id="ai-clear-history" class="btn btn-link btn-sm text-muted mt-3 px-0">
                    Effacer l'historique
                </button>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="form-card ai-chat-panel d-flex flex-column">
                <div id="ai-messages" class="ai-messages flex-grow-1 mb-3">
                    <div class="ai-msg ai-msg-bot">
                        <div class="ai-msg-bubble">
                            Bonjour — je suis <strong>GreenDC AI Advisor</strong>.
                            Posez une question sur le Data Center sélectionné.
                            <?php if (!empty($llmOnline)): ?>
                                Les réponses sont générées par le LLM
                                (<em><?= Security::e((string) ($llmModel ?: 'local')) ?></em>)
                                à partir des calculateurs et des documents RAG.
                            <?php else: ?>
                                Le LLM n'est pas encore joignable : activez Ollama ou une clé dans <code>.env</code>.
                                En attendant, une analyse déterministe reste disponible.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <form id="ai-chat-form" class="ai-chat-form">
                    <div class="input-group">
                        <input type="text" id="ai-question" class="form-control" placeholder="Votre question…" maxlength="2000" autocomplete="off" required>
                        <button type="submit" class="btn btn-brand" id="ai-send">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                <div id="ai-status" class="small text-muted mt-2"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
window.GDC_AI = {
    csrf: <?= json_encode($csrf, JSON_UNESCAPED_UNICODE) ?>,
    chatUrl: <?= json_encode(Url::to('ai/chat'), JSON_UNESCAPED_UNICODE) ?>,
    clearUrl: <?= json_encode(Url::to('ai/clear-history'), JSON_UNESCAPED_UNICODE) ?>,
    baseUrl: <?= json_encode($baseUrl, JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="<?= Security::e($baseUrl) ?>/assets/js/ai-chat.js"></script>
