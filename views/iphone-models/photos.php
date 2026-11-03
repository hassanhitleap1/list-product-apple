<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $groups key => ['brand', 'name', 'imageUrl', 'sizes', 'active'] */

$this->title = Yii::t('app', 'Model photos');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Phone trade-in prices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$byBrand = [];
foreach ($groups as $key => $g) {
    $byBrand[$g['brand']][$key] = $g;
}
$missing = count(array_filter($groups, function ($g) { return $g['active'] && !$g['imageUrl']; }));
?>
<div class="mp">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="mp-lede">
        One photo per model. It is used for every storage size and shows in the calculator when a customer picks the model and on their offer.
        Upload a photo, or paste a link to an image online (right-click the image › Copy image address).
        Use your own photos, or photos you have permission to use. Uploads: JPG, PNG or WebP, up to 3&nbsp;MB. A square photo on a white or transparent background looks best.
    </p>
    <?php if ($missing): ?>
        <p class="mp-missing"><?= $missing ?> model<?= $missing === 1 ? '' : 's' ?> on the site still have no photo. They show a drawing instead.</p>
    <?php endif; ?>

    <?= Html::beginForm(['photos'], 'post', ['enctype' => 'multipart/form-data', 'id' => 'mpForm']) ?>

    <?php foreach ($byBrand as $brand => $items): ?>
        <h2 class="mp-brand"><?= Html::encode($brand) ?></h2>
        <ul class="mp-grid">
            <?php foreach ($items as $key => $g): ?>
                <li class="mp-card<?= $g['active'] ? '' : ' is-hidden' ?>">
                    <div class="mp-photo" id="mp-photo-<?= $key ?>">
                        <?php if ($g['imageUrl']): ?>
                            <img src="<?= Html::encode($g['imageUrl']) ?>" alt="<?= Html::encode($g['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="mp-empty">No photo</span>
                        <?php endif; ?>
                    </div>
                    <div class="mp-body">
                        <p class="mp-name"><?= Html::encode($g['name']) ?></p>
                        <p class="mp-sizes">
                            <?= $g['sizes'] ? Html::encode(implode(', ', array_unique($g['sizes']))) : 'All sizes' ?>
                            <?= $g['active'] ? '' : ' · hidden on site' ?>
                        </p>
                        <label class="mp-upload">
                            <input type="file" name="photo[<?= $key ?>]" accept="image/jpeg,image/png,image/webp" data-preview="mp-photo-<?= $key ?>">
                            <span><?= $g['imageUrl'] ? 'Replace photo' : 'Upload photo' ?></span>
                        </label>
                        <?php $isLink = $g['image'] && preg_match('#^https?://#i', $g['image']); ?>
                        <input type="url" class="mp-link" name="link[<?= $key ?>]" placeholder="or paste image link (https://…)"
                               value="<?= $isLink ? Html::encode($g['image']) : '' ?>" aria-label="Image link for <?= Html::encode($g['name']) ?>"
                               data-preview="mp-photo-<?= $key ?>">
                        <?php if ($g['imageUrl']): ?>
                            <label class="mp-remove">
                                <input type="checkbox" name="remove[]" value="<?= $key ?>"> Remove photo
                            </label>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>

    <div class="mp-save">
        <span id="mpCount">No new photos chosen</span>
        <button type="submit" class="btn btn-success">Save photos</button>
    </div>
    <?= Html::endForm() ?>
</div>

<?php
$this->registerCss(<<<CSS
.mp-lede { color: #56627a; max-width: 46rem; }
.mp-missing { display: inline-block; padding: .375rem .875rem; border-radius: 999px; background: #fff1e8; color: #9a3412; font-size: .9375rem; }
.mp-brand { font-size: 1.25rem; font-weight: 600; margin: 1.75rem 0 .75rem; }
.mp-grid { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr)); gap: .75rem; }
.mp-card { display: flex; gap: .875rem; align-items: center; background: #fff; border: 1px solid #dde5ee; border-radius: 1rem; padding: .75rem; }
.mp-card.is-hidden { opacity: .6; }
.mp-photo { flex-shrink: 0; width: 5.5rem; height: 5.5rem; border-radius: .75rem; background: #f4f7fb; display: grid; place-items: center; overflow: hidden; }
.mp-photo img { width: 100%; height: 100%; object-fit: contain; }
.mp-empty { font-size: .8125rem; color: #56627a; }
.mp-body { min-width: 0; }
.mp-name { font-weight: 500; margin: 0; }
.mp-sizes { font-size: .8125rem; color: #56627a; margin: 0 0 .5rem; }
.mp-upload { position: relative; display: inline-block; cursor: pointer; margin: 0; }
.mp-upload input { position: absolute; inset: 0; opacity: 0; width: 100%; cursor: pointer; }
.mp-upload span { display: inline-block; padding: .3rem .875rem; border-radius: 999px; border: 1.5px solid #dde5ee; font-size: .875rem; }
.mp-upload:hover span { border-color: #16203a; }
.mp-upload input:focus-visible + span { outline: 3px solid #00a4ff; outline-offset: 2px; }
.mp-upload.is-chosen span { background: #e3f4ff; border-color: #00a4ff; color: #0072b8; }
.mp-link { display: block; width: 100%; margin-top: .375rem; padding: .25rem .625rem; border: 1.5px solid #dde5ee; border-radius: 999px; font-size: .8125rem; }
.mp-link:focus { outline: 3px solid #00a4ff; outline-offset: 1px; border-color: #16203a; }
.mp-link.is-bad { border-color: #b3261e; }
.mp-remove { display: block; font-size: .8125rem; color: #b3261e; margin: .375rem 0 0; }
.mp-save { position: sticky; bottom: 0; display: flex; justify-content: flex-end; align-items: center; gap: 1rem; margin-top: 1.5rem; padding: .875rem 0; background: linear-gradient(to top, #f4f7fb 70%, rgba(244,247,251,0)); }
#mpCount { color: #56627a; font-size: .9375rem; }
CSS);

$this->registerJs(<<<JS
(function () {
    var count = 0, label = document.getElementById('mpCount');
    document.querySelectorAll('#mpForm input[type=file]').forEach(function (input) {
        input.addEventListener('change', function () {
            var f = input.files && input.files[0];
            var box = document.getElementById(input.dataset.preview);
            var wrap = input.closest('.mp-upload');
            if (f) {
                var img = document.createElement('img');
                img.alt = '';
                img.src = URL.createObjectURL(f);
                box.textContent = '';
                box.appendChild(img);
                if (!wrap.classList.contains('is-chosen')) count++;
                wrap.classList.add('is-chosen');
                wrap.querySelector('span').textContent = f.name;
            }
            label.textContent = count ? count + ' new photo' + (count === 1 ? '' : 's') + ' ready to save' : 'No new photos chosen';
        });
    });
    document.querySelectorAll('#mpForm .mp-link').forEach(function (input) {
        input.addEventListener('change', function () {
            var url = input.value.trim();
            if (!/^https?:\/\//i.test(url)) { input.classList.toggle('is-bad', url !== ''); return; }
            var box = document.getElementById(input.dataset.preview);
            var img = document.createElement('img');
            img.alt = '';
            img.onload = function () { input.classList.remove('is-bad'); box.textContent = ''; box.appendChild(img); };
            img.onerror = function () { input.classList.add('is-bad'); input.title = 'This link does not open as an image.'; };
            img.src = url;
            label.textContent = 'Links changed. Press Save photos.';
        });
    });
})();
JS);
?>
