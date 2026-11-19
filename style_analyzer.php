<?php
require_once __DIR__ . '/includes\functions.php';
$pageTitle = 'AI Style Analyzer';
require __DIR__ . '/includes\header.php';
?>
<section class="page-banner"><p class="eyebrow">A playful visual guide</p><h1>AI Style <span>Analyzer.</span></h1><p>Preview a design on your device, then select the visual details you notice to get a simple rule-based style suggestion.</p></section>
<section class="section analyzer-section">
    <div class="analyzer-notice"><strong>Honest note:</strong> This demo does not upload or automatically understand images, and it does not use a trained AI model. Your image preview stays in your browser; the suggestion is based only on the clues you select.</div>
    <div class="analyzer-grid"><div class="analyzer-upload"><label class="upload-drop" for="analyzer-image"><span class="upload-icon">↑</span><strong>Choose a design image</strong><span>JPG, PNG or WebP · preview only</span><input id="analyzer-image" type="file" accept="image/jpeg,image/png,image/webp"></label><img id="analyzer-preview" class="analyzer-preview" alt="Your selected design preview" hidden></div>
    <div class="analyzer-form"><h2>What do you notice?</h2><p>Select all the visual clues that fit your image.</p>
        <label class="check-option"><input type="checkbox" name="clue" value="dense"> Dense, detailed coverage over much of the hand</label>
        <label class="check-option"><input type="checkbox" name="clue" value="flowing"> Flowing vines, leaves or sweeping lines</label>
        <label class="check-option"><input type="checkbox" name="clue" value="symmetry"> A balanced, symmetrical central motif</label>
        <label class="check-option"><input type="checkbox" name="clue" value="simple"> Lots of open skin with a few simple motifs</label>
        <label class="check-option"><input type="checkbox" name="clue" value="flower"> Flowers or petal-shaped motifs</label>
        <label class="check-option"><input type="checkbox" name="clue" value="geometry"> Repeating geometric shapes or straight lines</label>
        <button class="button button-green" type="button" id="analyze-style">Suggest a style</button>
        <div id="analyzer-result" class="analyzer-result" role="status" aria-live="polite" hidden></div>
    </div></div>
</section>
<?php require __DIR__ . '/includes\footer.php'; ?>
