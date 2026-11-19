<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Discover Your Mehndi';
$activePage = 'home';
$designs = $conn->query('SELECT d.id, d.design_name, d.image, d.style, d.complexity, d.price, c.category_name FROM designs d JOIN categories c ON c.id = d.category_id ORDER BY d.id DESC LIMIT 6');
$categories = $conn->query('SELECT id, category_name FROM categories ORDER BY category_name');
$popularImages = [
    'popular-design-1.png',
    'popular-design-2.png',
    'popular-design-3.png',
    'popular-design-4.png',
    'popular-design-5.png',
    'popular-design-6.png',
];
$popularImageIndex = 0;
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <h1>🌿✨ Mehndi Shop –<br><span>Where Tradition Meets Creativity ✨🌿</span></h1>
        <p class="hero-intro">Explore beautiful mehndi designs for every occasion — from simple and elegant patterns to stunning bridal designs. 💚</p>
        <div class="hero-actions"><a class="button button-gold" href="ai_recommendation.php">✦ Get AI Recommendations</a><a class="text-link light-link" href="designs.php">Explore the gallery <span>→</span></a></div>
        <div class="hero-note"><span class="note-flower">🪷</span> Choose Your Design • Explore • Get Inspired 🪷</div>
    </div>
    <div class="hero-art">
        <div class="hero-photo-card">
            <img src="images/hero-mehndi.png" alt="Detailed mehndi design covering both hands" fetchpriority="high">
            <span class="art-caption">Your story, in every detail</span>
        </div>
    </div>
</section>
<section class="section section-intro">
    <p class="eyebrow">Find your perfect pattern</p><h2>Beautiful designs.<br><span>Thoughtfully chosen.</span></h2>
    <p>Mehndi Shop brings classic artistry and easy discovery together. Tell our recommendation system what you love, then explore designs that suit your special moment.</p>
</section>
<section class="section section-soft">
    <div class="section-heading"><div><p class="eyebrow">A few favourites</p><h2>Popular designs</h2></div><a class="text-link" href="designs.php">View all designs <span>→</span></a></div>
    <div class="design-grid">
    <?php while ($design = $designs->fetch_assoc()): ?>
        <article class="design-card">
            <?php $popularImage = $popularImages[$popularImageIndex++] ?? $design['image']; ?>
            <a class="card-image" href="design_details.php?id=<?= (int) $design['id'] ?>"><img src="<?= h(image_url($popularImage)) ?>" alt="<?= h($design['design_name']) ?>" loading="lazy"><span class="image-badge"><?= h($design['category_name']) ?></span></a>
            <div class="card-content"><h3><?= h($design['design_name']) ?></h3><p class="design-price"><?= h(format_price($design['price'])) ?></p><p><?= h($design['style']) ?> · <?= h($design['complexity']) ?></p><a class="card-link" href="design_details.php?id=<?= (int) $design['id'] ?>">View design <span>↗</span></a></div>
        </article>
    <?php endwhile; ?>
    </div>
</section>
<section class="section">
    <div class="section-heading"><div><p class="eyebrow">Explore by mood</p><h2>Find your style</h2></div></div>
    <div class="category-grid">
        <?php while ($category = $categories->fetch_assoc()): ?>
            <a class="category-tile" href="designs.php?category=<?= (int) $category['id'] ?>"><span class="category-flower">✿</span><span><?= h($category['category_name']) ?></span><span class="tile-arrow">↗</span></a>
        <?php endwhile; ?>
    </div>
</section>
<section class="about-band"><div class="about-symbol">✾</div><div><p class="eyebrow">About Mehndi Shop</p><h2>Tradition, with a personal touch.</h2><p>Our AI-inspired recommendation system uses your preferences—not a black box—to rank designs from the gallery. It is a transparent, rule-based match and does not use a trained machine-learning model.</p></div><a class="button button-outline" href="ai_recommendation.php">Find your match</a></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
