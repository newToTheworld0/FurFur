<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>About - StraySafe</title>
        <link rel="stylesheet" href="about.css">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@500;600;700&display=swap" rel="stylesheet" />
    </head>
    <body>
        <?php include 'navbar.php'; ?>

        <img src="paws.png" alt="dog paw" class="bg-paw paw-top-right" />
        <img src="paws.png" alt="dog paw" class="bg-paw paw-mid-left" />
        <img src="paws.png" alt="dog paw" class="bg-paw paw-mid-right" />
        <img src="paws.png" alt="dog paw" class="bg-paw paw-bottom-left" />
        <img src="paws.png" alt="dog paw" class="bg-paw paw-bottom-right" />

        <main class="container">
            <header class="hero-banner">
              <h1>Want to know about this project?</h1>
              <p>See our details below</p>
            </header>

            <section class="content-grid">
              <div class="left-column">
                <article class="info-card mission-card">
                  <div class="card-pill">OUR MISSION</div>
                  <div class="card-body mission-text">
                    At StraySafe, we want to make the world a safer and kinder place for animals, especially those who are lost, abandoned, or living on the streets. Our goal is to bring together people who care about animals and are willing to help by giving them a chance to find a loving home. Through our platform, users can share sightings of stray animals, report those who need help, connect with potential adopters, and learn more about responsible pet care. We believe that even small acts of kindness can make a big difference in an animal's life.
                  </div>
                </article>

                <article class="info-card contact-card">
                  <div class="card-pill">Contact us</div>
                  <div class="card-body contact-text">
                    Support Email: straysafe@gmail.com
                  </div>
                </article>
              </div>

              <article class="info-card faq-card">
                <div class="card-pill">FAQs</div>
                <div class="card-body">
                  <div class="faq-item">
                    <p class="faq-question">1. What is StraySafe?</p>
                    <p>StraySafe is a website where people can come together to help stray animals. It allows users to post pictures and locations of animals they see around their community, especially those that need help.</p>
                  </div>
                  <div class="faq-item">
                    <p class="faq-question">2. What is the main purpose of StraySafe?</p>
                    <p>The main purpose is to help stray animals get the care and attention they deserve by sharing information and reporting sightings.</p>
                  </div>
                  <div class="faq-item">
                    <p class="faq-question">3. Who can use StraySafe?</p>
                    <p>Anyone who wants to help or show support for stray animals can use StraySafe.</p>
                  </div>
                </div>
              </article>
            </section>
        </main>

        <footer>
            <div class="footer-box f1">
                <img src="paw.png" alt="Logo">
                <p>StraySafe</p>
            </div>
            <div class="footer-box f2">
                <p>Information and Welfare Tracking System for Community Animals</p>
            </div>
            <div class="footer-box f3">
                <p>Student project</p>
            </div>
        </footer>
        <script src="script.js"></script>
    </body>
</html>