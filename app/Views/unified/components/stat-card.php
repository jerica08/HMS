<?php

$iconColor = $iconColor ?? 'blue';
$metrics = $metrics ?? [];
$actionUrl = $actionUrl ?? null;
$actionText = $actionText ?? 'View';
?>
<div class="overview-card">
    <div class="card-header-modern">
        <div class="card-icon-modern <?= $iconColor ?>">
            <i class="<?= $icon ?>"></i>
        </div>
        <div class="card-info">
            <h3 class="card-title-modern"><?= $title ?></h3>
            <?php if (!empty($subtitle)): ?>
                <p class="card-subtitle"><?= $subtitle ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-metrics">
        <?php foreach ($metrics as $metric): ?>
            <div class="metric">
                <div class="metric-value <?= $metric['color'] ?? 'blue' ?>">
                    <?= $metric['value'] ?? 0 ?>
                </div>
                <div class="metric-label"><?= $metric['label'] ?? '' ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($actionUrl): ?>
        <div class="card-actions">
            <a href="<?= base_url($actionUrl) ?>" class="action-btn"><?= $actionText ?></a>
        </div>
    <?php endif; ?>
</div>

