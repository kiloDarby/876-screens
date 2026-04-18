<?php if (!empty($errors)): ?>
    <div class="global-alert global-alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="global-alert global-alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>