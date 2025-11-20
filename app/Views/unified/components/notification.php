<div id="<?= esc($id ?? 'moduleNotification') ?>"
     role="alert"
     aria-live="polite"
     style="
        display:none;
        margin: 0.75rem auto 0 auto;
        padding: 0.6rem 0.9rem;
        max-width: 1180px;
        border-radius: 6px;
        border: 1px solid #bbf7d0;
        background: #ecfdf5;
        color: #166534;
        align-items:center;
        gap:0.5rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
">
    <i id="<?= esc(($id ?? 'moduleNotification') . 'Icon') ?>"
       class="fas fa-check-circle"
       aria-hidden="true"></i>
    <span id="<?= esc(($id ?? 'moduleNotification') . 'Text') ?>"></span>
    <button type="button"
            onclick="<?= esc($dismissFn ?? 'dismissModuleNotification()') ?>"
            aria-label="Dismiss notification"
            style="margin-left:auto; background:transparent; border:none; cursor:pointer; color:inherit;">
        <i class="fas fa-times"></i>
    </button>
</div>