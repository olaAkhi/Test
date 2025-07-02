<div class="jumbotron-custom text-center">
    <h1 class="display-4">🌌 Welcome to Your Personal Cosmos! 🔢</h1>
    <p class="lead">Unlock the secrets of the stars and the wisdom of numbers. Our expert astrology and numerology services offer profound insights into your life's path, relationships, and potential.</p>
    <hr class="my-4 bg-light">
    <p>Ready to explore the map of your destiny?</p>
    <a class="btn btn-primary btn-lg" href="index.php?action=services" role="button">Discover Our Services</a>
    <?php if (!isset($_SESSION['user_id'])): ?>
    <a class="btn btn-outline-light btn-lg ms-2" href="index.php?action=register" role="button">Join Now</a>
    <?php endif; ?>
</div>

<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Why Choose AstroNumero?</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">🌟 Expert Insights</h5>
                        <p class="card-text">Our readings are crafted by experienced astrologers and numerologists dedicated to providing you with accurate and personalized guidance.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">🔒 Secure & Confidential</h5>
                        <p class="card-text">Your privacy is paramount. All your personal data and readings are kept strictly confidential and secure.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm">
                     <div class="card-body">
                        <h5 class="card-title">💡 Clarity & Purpose</h5>
                        <p class="card-text">Gain a deeper understanding of yourself and your life's path, empowering you to make informed decisions and live with purpose.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Featured Services</h2>
        <div class="row">
            <!-- Placeholder for a few featured services - this could be dynamic later -->
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Birth Chart Interpretation</h5>
                        <p class="card-text">A full analysis of your personality, strengths, and life path based on your unique birth details.</p>
                        <a href="index.php?action=service_detail&id=1" class="btn btn-outline-dark">Learn More</a> <!-- Assuming service ID 1 is Natal Chart -->
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Life Path Number Reading</h5>
                        <p class="card-text">Discover your life’s main purpose, challenges, and opportunities through the power of numerology.</p>
                        <a href="index.php?action=service_detail&id=21" class="btn btn-outline-dark">Learn More</a> <!-- Assuming service ID 21 is Life Path -->
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-4 mb-3">
                 <div class="card text-center h-100">
                    <div class="card-body">
                        <h5 class="card-title">Love Compatibility</h5>
                        <p class="card-text">Explore the dynamics of your relationships with our detailed synastry and composite chart readings.</p>
                        <a href="index.php?action=service_detail&id=5" class="btn btn-outline-dark">Learn More</a> <!-- Assuming service ID 5 is Synastry -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
