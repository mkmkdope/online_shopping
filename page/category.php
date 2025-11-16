<?php
$_title = 'Book Categories';
include '../sb_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_title; ?></title>
    <link rel="stylesheet" href="sb_style.css">
</head>
<body>
    <main class="container">
        <section class="hero">
            <h1>Book Categories</h1>
            <p>Explore our extensive collection organized by genre and subject</p>
        </section>

        <!-- All Categories Grid -->
        <section class="categories" style="margin: 3rem 0;">
            <h2 style="text-align: center; margin-bottom: 2rem; color: #2c3e50;">Browse All Categories</h2>
            <div class="category-list" style="justify-content: center;">
                <a href="product.php?category=1" class="category">Fiction</a>
                <a href="product.php?category=2" class="category">Non-Fiction</a>
                <a href="product.php?category=3" class="category">Science Fiction</a>
                <a href="product.php?category=4" class="category">Fantasy</a>
                <a href="product.php?category=5" class="category">Mystery</a>
                <a href="product.php?category=6" class="category">Romance</a>
                <a href="product.php?category=7" class="category">Biography</a>
                <a href="product.php?category=8" class="category">History</a>
                <a href="product.php?category=9" class="category">Classic Literature</a>
                <a href="product.php?category=10" class="category">Contemporary Fiction</a>
                <a href="product.php?category=11" class="category">Thriller</a>
                <a href="product.php?category=12" class="category">Young Adult</a>
                <a href="product.php?category=13" class="category">Children</a>
                <a href="product.php?category=14" class="category">Self-Help</a>
                <a href="product.php?category=15" class="category">Business</a>
            </div>
        </section>

        <!-- Popular Categories with Descriptions -->
        <section style="margin: 4rem 0;">
            <h2 style="text-align: center; margin-bottom: 3rem; color: #2c3e50;">Popular Categories</h2>
            
            <div style="display: grid; gap: 2rem;">
                <!-- Fiction -->
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color: #e74c3c; margin-bottom: 1rem;">Fiction</h3>
                    <p style="margin-bottom: 1rem; line-height: 1.6;">
                        Immerse yourself in imaginative worlds and compelling narratives. Our fiction collection includes 
                        everything from literary masterpieces to contemporary novels that explore the human experience.
                    </p>
                    <a href="product.php?category=1" style="color: #e74c3c; font-weight: bold;">Browse Fiction Books →</a>
                </div>

                <!-- Non-Fiction -->
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color: #e74c3c; margin-bottom: 1rem;">Non-Fiction</h3>
                    <p style="margin-bottom: 1rem; line-height: 1.6;">
                        Explore real stories, facts, and insights across various subjects including science, history, 
                        biography, and personal development. Learn from experts and thought leaders.
                    </p>
                    <a href="product.php?category=2" style="color: #e74c3c; font-weight: bold;">Browse Non-Fiction Books →</a>
                </div>

                <!-- Science Fiction -->
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color: #e74c3c; margin-bottom: 1rem;">Science Fiction</h3>
                    <p style="margin-bottom: 1rem; line-height: 1.6;">
                        Journey to distant galaxies, explore futuristic technologies, and encounter alien civilizations. 
                        From space operas to cyberpunk, discover stories that push the boundaries of imagination.
                    </p>
                    <a href="product.php?category=3" style="color: #e74c3c; font-weight: bold;">Browse Sci-Fi Books →</a>
                </div>

                <!-- Fantasy -->
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color: #e74c3c; margin-bottom: 1rem;">Fantasy</h3>
                    <p style="margin-bottom: 1rem; line-height: 1.6;">
                        Enter realms of magic, mythical creatures, and epic quests. Our fantasy collection spans from 
                        high fantasy epics to urban fantasy adventures that will transport you to other worlds.
                    </p>
                    <a href="product.php?category=4" style="color: #e74c3c; font-weight: bold;">Browse Fantasy Books →</a>
                </div>

                <!-- Mystery & Thriller -->
                <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color: #e74c3c; margin-bottom: 1rem;">Mystery & Thriller</h3>
                    <p style="margin-bottom: 1rem; line-height: 1.6;">
                        Unravel puzzles, solve crimes, and experience heart-pounding suspense. From cozy mysteries to 
                        psychological thrillers, these page-turners will keep you guessing until the very end.
                    </p>
                    <a href="product.php?category=5" style="color: #e74c3c; font-weight: bold;">Browse Mystery Books →</a>
                </div>
            </div>
        </section>

        <!-- Category Statistics -->
        <section style="background: #2c3e50; color: white; padding: 3rem; border-radius: 8px; text-align: center; margin: 3rem 0;">
            <h2 style="margin-bottom: 2rem;">Our Collection by Numbers</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 2rem;">
                <div>
                    <div style="font-size: 2.5rem; font-weight: bold; color: #e74c3c;">15+</div>
                    <div>Categories</div>
                </div>
                <div>
                    <div style="font-size: 2.5rem; font-weight: bold; color: #e74c3c;">10,000+</div>
                    <div>Books</div>
                </div>
                <div>
                    <div style="font-size: 2.5rem; font-weight: bold; color: #e74c3c;">500+</div>
                    <div>Authors</div>
                </div>
                <div>
                    <div style="font-size: 2.5rem; font-weight: bold; color: #e74c3c;">50+</div>
                    <div>Publishers</div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="hero">
            <h2>Can't Find What You're Looking For?</h2>
            <p>Browse our complete book collection or use our advanced search to find specific titles.</p>
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                <a href="product.php" style="display: inline-block; background: #e74c3c; color: white; padding: 12px 30px; border-radius: 4px;">View All Books</a>
                <a href="index.php" style="display: inline-block; background: #2c3e50; color: white; padding: 12px 30px; border-radius: 4px;">Back to Home</a>
            </div>
        </section>
    </main>
</body>
</html>
<?php include '../sb_foot.php'; ?>